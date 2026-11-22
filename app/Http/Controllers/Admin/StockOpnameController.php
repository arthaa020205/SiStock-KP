<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BatchStock;
use App\Models\ProductBatch;
use App\Models\RackLocation;
use App\Models\StockAdjustmentRequest;
use App\Models\StockMovement;
use App\Models\StockOpname;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockOpnameController extends Controller
{
    public function index(Request $request)
    {
        $query = StockOpname::with([
            'creator',
            'details.adjustmentRequests',
        ]);

        if ($request->filled('search')) {
            $query->where(
                'opname_number',
                'like',
                '%' . $request->search . '%'
            );
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $stockOpnames = $query
            ->whereIn('status', [
                'submitted',
                'approved',
                'rejected',
                'completed',
            ])
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.stock-opnames.index', compact(
            'stockOpnames'
        ));
    }

    public function show(StockOpname $stockOpname)
    {
        $stockOpname->load([
            'creator',
            'approver',
            'details.product.baseUnit',
            'details.batchStock.productBatch.product.baseUnit',
            'details.batchStock.rackLocation',
            'details.adjustmentRequests.requester',
            'details.adjustmentRequests.reviewer',
        ]);

        return view('admin.stock-opnames.show', compact(
            'stockOpname'
        ));
    }

    /**
     * Menyetujui permintaan penyesuaian stok.
     */
    public function approveAdjustment(StockAdjustmentRequest $adjustmentRequest)
    {
        $result = DB::transaction(function () use ($adjustmentRequest) {

            $adjustment = StockAdjustmentRequest::whereKey($adjustmentRequest->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($adjustment->status !== 'pending') {
                throw ValidationException::withMessages([
                    'adjustment' => 'Permintaan ini sudah diproses sebelumnya.',
                ]);
            }

            $detail = $adjustment->detail()
                ->lockForUpdate()
                ->firstOrFail();

            $opname = StockOpname::whereKey($detail->stock_opname_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($opname->status !== 'submitted') {
                throw ValidationException::withMessages([
                    'adjustment' => 'Stock Opname tidak berada dalam status diajukan.',
                ]);
            }

            $difference = round(
                (float) $detail->physical_quantity -
                (float) $detail->system_quantity,
                3
            );

            if (abs($difference) < 0.0005) {
                throw ValidationException::withMessages([
                    'adjustment' => 'Tidak ada selisih stok yang perlu disesuaikan.',
                ]);
            }

            /*
             * Ambil produk dari detail baru.
             * Jika detail merupakan data Stock Opname lama,
             * ambil produk melalui batch.
             */
            $productId = $detail->product_id;

            $legacyBatchStock = null;

            if (!$productId && $detail->batch_stock_id) {
                $legacyBatchStock = BatchStock::with('productBatch')
                    ->find($detail->batch_stock_id);

                $productId = $legacyBatchStock?->productBatch?->product_id;
            }

            if (!$productId) {
                throw ValidationException::withMessages([
                    'adjustment' => 'Produk pada detail Stock Opname tidak ditemukan.',
                ]);
            }

            /*
             * Kunci semua stok batch milik produk.
             * Ini mencegah perubahan stok bersamaan saat penyesuaian.
             */
            $batchStocks = BatchStock::query()
                ->whereHas('productBatch', function ($query) use ($productId) {
                    $query->where('product_id', $productId);
                })
                ->with('productBatch')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            $currentStock = round(
                (float) $batchStocks->sum('quantity'),
                3
            );

            $allocations = [];

            /*
             * SELISIH POSITIF
             * Buat batch penyesuaian baru.
             */
            if ($difference > 0) {

                $rackLocation = $this->getAdjustmentRackLocation();

                $batchCode = $this->generateAdjustmentBatchCode($productId);

                $productBatch = ProductBatch::create([
                    'product_id' => $productId,
                    'batch_code' => $batchCode,
                    'received_date' => now()->toDateString(),
                    'expiry_date' => null,
                ]);

                $batchStock = BatchStock::create([
                    'product_batch_id' => $productBatch->id,
                    'rack_location_id' => $rackLocation->id,
                    'quantity' => $difference,
                ]);

                StockMovement::create([
                    'batch_stock_id' => $batchStock->id,
                    'movement_type' => 'adjustment_in',
                    'quantity' => $difference,
                    'balance_before' => 0,
                    'balance_after' => $difference,
                    'reference_type' => StockAdjustmentRequest::class,
                    'reference_id' => $adjustment->id,
                    'notes' => 'Penyesuaian stok positif Stock Opname '
                        . $opname->opname_number
                        . ' - Batch ' . $batchCode,
                    'created_by' => auth()->id(),
                ]);

                $allocations[] = [
                    'batch_code' => $batchCode,
                    'rack_code' => $rackLocation->code,
                    'movement_type' => 'adjustment_in',
                    'quantity' => $difference,
                    'balance_before' => 0,
                    'balance_after' => $difference,
                ];
            }

            /*
             * SELISIH NEGATIF
             * Kurangi beberapa batch berdasarkan FEFO.
             */
            if ($difference < 0) {

                $quantityToReduce = abs($difference);

                if ($currentStock + 0.0005 < $quantityToReduce) {
                    throw ValidationException::withMessages([
                        'adjustment' =>
                            'Stok saat ini tidak mencukupi untuk penyesuaian negatif. '
                            . 'Periksa transaksi setelah penghitungan Stock Opname '
                            . 'sebelum melanjutkan persetujuan.',
                    ]);
                }

                $availableBatches = $batchStocks
                    ->filter(fn ($batch) => (float) $batch->quantity > 0)
                    ->sort(function ($a, $b) {

                        $expiryA = $a->productBatch?->expiry_date;
                        $expiryB = $b->productBatch?->expiry_date;

                        // Batch dengan ED lebih awal diprioritaskan.
                        // Batch tanpa ED diletakkan paling akhir.
                        if (!$expiryA && $expiryB) {
                            return 1;
                        }

                        if ($expiryA && !$expiryB) {
                            return -1;
                        }

                        if ($expiryA && $expiryB) {
                            $comparison = strcmp(
                                (string) $expiryA,
                                (string) $expiryB
                            );

                            if ($comparison !== 0) {
                                return $comparison;
                            }
                        }

                        return $a->id <=> $b->id;
                    });

                foreach ($availableBatches as $batchStock) {

                    if ($quantityToReduce <= 0.0005) {
                        break;
                    }

                    $balanceBefore = (float) $batchStock->quantity;

                    $quantity = min(
                        $balanceBefore,
                        $quantityToReduce
                    );

                    $quantity = round($quantity, 3);

                    $balanceAfter = round(
                        $balanceBefore - $quantity,
                        3
                    );

                    $batchStock->update([
                        'quantity' => $balanceAfter,
                    ]);

                    StockMovement::create([
                        'batch_stock_id' => $batchStock->id,
                        'movement_type' => 'adjustment_out',
                        'quantity' => $quantity,
                        'balance_before' => $balanceBefore,
                        'balance_after' => $balanceAfter,
                        'reference_type' => StockAdjustmentRequest::class,
                        'reference_id' => $adjustment->id,
                        'notes' => 'Penyesuaian stok negatif Stock Opname '
                            . $opname->opname_number
                            . ' - Batch '
                            . $batchStock->productBatch->batch_code,
                        'created_by' => auth()->id(),
                    ]);

                    $allocations[] = [
                        'batch_code' => $batchStock->productBatch->batch_code,
                        'rack_code' => $batchStock->rack_location_id,
                        'movement_type' => 'adjustment_out',
                        'quantity' => $quantity,
                        'balance_before' => $balanceBefore,
                        'balance_after' => $balanceAfter,
                    ];

                    $quantityToReduce = round(
                        $quantityToReduce - $quantity,
                        3
                    );
                }

                if ($quantityToReduce > 0.0005) {
                    throw ValidationException::withMessages([
                        'adjustment' => 'Alokasi penyesuaian stok tidak berhasil dipenuhi.',
                    ]);
                }
            }

            /*
             * Tandai permintaan telah disetujui.
             */
            $adjustment->update([
                'status' => 'approved',
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
                'approval_notes' => 'Permintaan penyesuaian stok disetujui.',
            ]);

            /*
             * Periksa apakah masih ada permintaan pending.
             */
            $pendingRequests = StockAdjustmentRequest::whereHas(
                'detail',
                function ($query) use ($opname) {
                    $query->where('stock_opname_id', $opname->id);
                }
            )
                ->where('status', 'pending')
                ->exists();

            if (!$pendingRequests) {

                $hasRejected = StockAdjustmentRequest::whereHas(
                    'detail',
                    function ($query) use ($opname) {
                        $query->where('stock_opname_id', $opname->id);
                    }
                )
                    ->where('status', 'rejected')
                    ->exists();

                $opname->update([
                    'status' => $hasRejected ? 'rejected' : 'approved',
                    'approved_by' => $hasRejected ? null : auth()->id(),
                    'approved_at' => $hasRejected ? null : now(),
                ]);
            }

            $product = $detail->product()
                ->with('baseUnit')
                ->first();

            if (!$product) {
                $product = $legacyBatchStock?->productBatch?->product;
            }

            return [
                'opname_id' => $opname->id,
                'opname_number' => $opname->opname_number,
                'adjustment_id' => $adjustment->id,
                'product_name' => $product?->name ?? '-',
                'product_code' => $product?->code ?? '-',
                'system_quantity' => (float) $detail->system_quantity,
                'physical_quantity' => (float) $detail->physical_quantity,
                'difference' => $difference,
                'current_stock_before' => $currentStock,
                'current_stock_after' => $difference > 0
                    ? $currentStock + $difference
                    : $currentStock - abs($difference),
                'allocations' => $allocations,
            ];
        });

        ActivityLogger::log(
            'stock_adjustment_approved',
            'Admin menyetujui penyesuaian stok pada Stock Opname '
                . $result['opname_number'],
            StockOpname::find($result['opname_id']),
            [
                'opname_number' => $result['opname_number'],
                'adjustment_request_id' => $result['adjustment_id'],
                'product_name' => $result['product_name'],
                'product_code' => $result['product_code'],
                'system_quantity' => $result['system_quantity'],
                'physical_quantity' => $result['physical_quantity'],
                'difference' => $result['difference'],
                'current_stock_before' => $result['current_stock_before'],
                'current_stock_after' => $result['current_stock_after'],
                'allocations' => $result['allocations'],
            ]
        );

        return redirect()
            ->route('admin.stock-opnames.show', $result['opname_id'])
            ->with('success', 'Permintaan penyesuaian stok berhasil disetujui.');
    }

    /**
     * Menolak permintaan penyesuaian stok.
     */
    public function rejectAdjustment(
        Request $request,
        StockAdjustmentRequest $adjustmentRequest
    ) {
        $validated = $request->validate([
            'approval_notes' => ['required', 'string', 'max:1000'],
        ]);

        $result = DB::transaction(function () use (
            $adjustmentRequest,
            $validated
        ) {

            $adjustment = StockAdjustmentRequest::whereKey($adjustmentRequest->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($adjustment->status !== 'pending') {
                throw ValidationException::withMessages([
                    'adjustment' => 'Permintaan ini sudah diproses sebelumnya.',
                ]);
            }

            $detail = $adjustment->detail()
                ->lockForUpdate()
                ->firstOrFail();

            $opname = StockOpname::whereKey($detail->stock_opname_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($opname->status !== 'submitted') {
                throw ValidationException::withMessages([
                    'adjustment' => 'Stock Opname tidak berada dalam status diajukan.',
                ]);
            }

            $product = $detail->product()
                ->with('baseUnit')
                ->first();

            if (!$product && $detail->batch_stock_id) {
                $batchStock = BatchStock::with('productBatch.product')
                    ->find($detail->batch_stock_id);

                $product = $batchStock?->productBatch?->product;
            }

            $adjustment->update([
                'status' => 'rejected',
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
                'approval_notes' => $validated['approval_notes'],
            ]);

            $pendingRequests = StockAdjustmentRequest::whereHas(
                'detail',
                function ($query) use ($opname) {
                    $query->where('stock_opname_id', $opname->id);
                }
            )
                ->where('status', 'pending')
                ->exists();

            if (!$pendingRequests) {
                $opname->update([
                    'status' => 'rejected',
                ]);
            }

            return [
                'opname_id' => $opname->id,
                'opname_number' => $opname->opname_number,
                'adjustment_id' => $adjustment->id,
                'product_name' => $product?->name ?? '-',
                'product_code' => $product?->code ?? '-',
                'system_quantity' => (float) $detail->system_quantity,
                'physical_quantity' => (float) $detail->physical_quantity,
                'difference' => (float) $detail->difference_quantity,
                'approval_notes' => $adjustment->approval_notes,
            ];
        });

        ActivityLogger::log(
            'stock_adjustment_rejected',
            'Admin menolak penyesuaian stok pada Stock Opname '
                . $result['opname_number'],
            StockOpname::find($result['opname_id']),
            [
                'opname_number' => $result['opname_number'],
                'adjustment_request_id' => $result['adjustment_id'],
                'product_name' => $result['product_name'],
                'product_code' => $result['product_code'],
                'approval_notes' => $result['approval_notes'],
                'system_quantity' => $result['system_quantity'],
                'physical_quantity' => $result['physical_quantity'],
                'difference' => $result['difference'],
            ]
        );

        return redirect()
            ->route('admin.stock-opnames.show', $result['opname_id'])
            ->with('success', 'Permintaan penyesuaian stok berhasil ditolak.');
    }

    /**
     * Mengambil atau membuat lokasi khusus penyesuaian.
     */
    private function getAdjustmentRackLocation(): RackLocation
    {
        $rack = RackLocation::where('code', 'ADJ')->first();

        if ($rack) {
            return $rack;
        }

        return RackLocation::create([
            'code' => 'ADJ',
            'name' => 'Lokasi Penyesuaian',
            'description' => 'Lokasi khusus untuk batch hasil penyesuaian Stock Opname.',
            'is_active' => true,
        ]);
    }

    /**
     * Membuat kode batch penyesuaian yang unik untuk produk.
     */
    private function generateAdjustmentBatchCode(int $productId): string
    {
        do {
            $code = 'ADJ-' . now()->format('YmdHis')
                . '-' . $productId
                . '-' . random_int(100, 999);

            $exists = ProductBatch::where('product_id', $productId)
                ->where('batch_code', $code)
                ->exists();

        } while ($exists);

        return $code;
    }
}