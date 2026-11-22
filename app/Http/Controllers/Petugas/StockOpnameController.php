<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\BatchStock;
use App\Models\StockMovement;
use App\Models\StockOpname;
use App\Models\StockOpnameDetail;
use App\Models\StockAdjustmentRequest;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use App\Notifications\StockOpnameSubmitted;
use Illuminate\Support\Facades\Notification;

class StockOpnameController extends Controller
{
    /**
     * Menampilkan daftar Stock Opname.
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'periode' => [
                'nullable',
                'in:semua,hari_ini,minggu_ini,bulan_ini,tahun_ini,rentang',
            ],
            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_akhir' => [
                'nullable',
                'date',
                'after_or_equal:tanggal_mulai',
            ],
            'status' => [
                'nullable',
                'in:draft,submitted,approved,rejected,completed',
            ],
        ]);

        $periode = $validated['periode'] ?? 'semua';
        $status = $validated['status'] ?? '';
        $tanggalMulai = $validated['tanggal_mulai'] ?? '';
        $tanggalAkhir = $validated['tanggal_akhir'] ?? '';

        $query = StockOpname::with('creator');

        // Filter periode berdasarkan tanggal pelaksanaan Stock Opname.
        switch ($periode) {
            case 'hari_ini':
                $query->whereDate('opname_date', today());
                break;

            case 'minggu_ini':
                $query->whereBetween('opname_date', [
                    today()->startOfWeek()->toDateString(),
                    today()->endOfWeek()->toDateString(),
                ]);
                break;

            case 'bulan_ini':
                $query->whereBetween('opname_date', [
                    today()->startOfMonth()->toDateString(),
                    today()->endOfMonth()->toDateString(),
                ]);
                break;

            case 'tahun_ini':
                $query->whereBetween('opname_date', [
                    today()->startOfYear()->toDateString(),
                    today()->endOfYear()->toDateString(),
                ]);
                break;

            case 'rentang':
                if ($tanggalMulai && $tanggalAkhir) {
                    $query->whereBetween('opname_date', [
                        $tanggalMulai,
                        $tanggalAkhir,
                    ]);
                } elseif ($tanggalMulai) {
                    $query->whereDate('opname_date', '>=', $tanggalMulai);
                } elseif ($tanggalAkhir) {
                    $query->whereDate('opname_date', '<=', $tanggalAkhir);
                }
                break;
        }

        // Filter status.
        if ($status !== '') {
            $query->where('status', $status);
        }

        $opnames = $query
            ->orderByDesc('opname_date')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('petugas.stock-opnames.index', compact(
            'opnames',
            'periode',
            'status',
            'tanggalMulai',
            'tanggalAkhir'
        ));
    }

    /**
     * Menampilkan form pembuatan Stock Opname.
     */
    public function create()
    {
        return view('petugas.stock-opnames.create');
    }

    /**
     * Membuat Stock Opname berdasarkan total stok setiap produk.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'opname_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $opname = DB::transaction(function () use ($validated) {

            /*
             * Kunci produk dan seluruh batch stock agar proses
             * pencatatan snapshot tidak bertabrakan dengan transaksi stok.
             */
            Product::orderBy('id')
                ->lockForUpdate()
                ->get();

            $batchStocks = BatchStock::orderBy('id')
                ->lockForUpdate()
                ->get();

            $products = Product::where('is_active', true)
                ->orderBy('name')
                ->get();

            if ($products->isEmpty()) {
                throw ValidationException::withMessages([
                    'stock_opname' => 'Tidak ada produk aktif untuk dilakukan Stock Opname.',
                ]);
            }

            // Batas pergerakan stok saat Stock Opname dimulai.
            $movementCutoffId = StockMovement::max('id') ?? 0;

            $opname = StockOpname::create([
                'opname_number' => 'SO-' . now()->format('Ymd-His')
                    . '-' . random_int(100, 999),

                'opname_date' => $validated['opname_date'],
                'status' => 'draft',
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id(),
                'movement_cutoff_id' => $movementCutoffId,
            ]);

            // Kelompokkan stok seluruh batch berdasarkan produk.
            $stockByProduct = $batchStocks
                ->groupBy(function ($batchStock) {
                    return $batchStock->productBatch->product_id
                        ?? null;
                });

            foreach ($products as $product) {
                $totalStock = $stockByProduct
                    ->get($product->id, collect())
                    ->sum('quantity');

                StockOpnameDetail::create([
                    'stock_opname_id' => $opname->id,
                    'product_id' => $product->id,
                    'batch_stock_id' => null,
                    'system_quantity' => $totalStock,
                    'physical_quantity' => 0,
                    'difference_quantity' => 0,
                    'is_counted' => false,
                ]);
            }

            return $opname;
        });

        ActivityLogger::log(
            'stock_opname_created',
            'Petugas membuat Stock Opname baru dengan nomor ' . $opname->opname_number,
            $opname,
            [
                'opname_number' => $opname->opname_number,
                'opname_date' => $opname->opname_date,
                'total_items' => $opname->details()->count(),
                'status' => $opname->status,
            ]
        );

        return redirect()
            ->route('petugas.stock-opnames.index')
            ->with('success', 'Stock Opname berhasil dibuat sebagai Draft.');
    }

    /**
     * Menampilkan detail Stock Opname.
     */
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

        return view(
            'petugas.stock-opnames.show',
            compact('stockOpname')
        );
    }

    /**
     * Menyimpan hasil perhitungan fisik Stock Opname.
     */
    public function updateDetails(Request $request, StockOpname $stockOpname)
    {
        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'integer'],
            'items.*.physical_quantity' => ['required', 'numeric', 'min:0'],
            'items.*.notes' => ['nullable', 'string'],
        ]);

        $updatedItems = DB::transaction(function () use (
            $validated,
            $stockOpname
        ) {
            $opname = StockOpname::whereKey($stockOpname->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($opname->status !== 'draft') {
                throw ValidationException::withMessages([
                    'stock_opname' => 'Hasil Stock Opname hanya dapat diubah selama status Draft.',
                ]);
            }

            $logs = [];

            foreach ($validated['items'] as $item) {
                $detail = StockOpnameDetail::where('stock_opname_id', $opname->id)
                    ->whereKey($item['id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                if (!$detail->product_id) {
                    throw ValidationException::withMessages([
                        'stock_opname' => 'Detail Stock Opname lama tidak dapat diubah menggunakan format baru. Silakan buat Stock Opname baru.',
                    ]);
                }

                /*
                 * Kunci seluruh batch produk ini.
                 * Transaksi stok yang menggunakan batch tersebut
                 * harus menunggu sampai proses pengambilan batas selesai.
                 */
                BatchStock::whereHas('productBatch', function ($query) use ($detail) {
                    $query->where('product_id', $detail->product_id);
                })
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get();

                /*
                 * Ambil pergerakan stok setelah snapshot awal
                 * sampai batas penghitungan produk ini.
                 */
                $countedMovementId = StockMovement::max('id') ?? 0;

                $movements = StockMovement::query()
                    ->join(
                        'batch_stocks',
                        'stock_movements.batch_stock_id',
                        '=',
                        'batch_stocks.id'
                    )
                    ->join(
                        'product_batches',
                        'batch_stocks.product_batch_id',
                        '=',
                        'product_batches.id'
                    )
                    ->where('product_batches.product_id', $detail->product_id)
                    ->where(
                        'stock_movements.id',
                        '>',
                        $detail->counted_movement_id ?? $opname->movement_cutoff_id
                    )
                    ->where('stock_movements.id', '<=', $countedMovementId)
                    ->lockForUpdate()
                    ->get([
                        'stock_movements.movement_type',
                        'stock_movements.quantity',
                    ]);

                $movementDifference = 0;

                foreach ($movements as $movement) {
                    if (in_array($movement->movement_type, [
                        'in',
                        'transfer_in',
                        'adjustment_in',
                    ])) {
                        $movementDifference += (float) $movement->quantity;
                    } elseif (in_array($movement->movement_type, [
                        'out',
                        'transfer_out',
                        'adjustment_out',
                    ])) {
                        $movementDifference -= (float) $movement->quantity;
                    }
                }

                // Stok sistem yang seharusnya tersedia saat produk dihitung.
                $systemQuantity = (float) $detail->system_quantity
                    + $movementDifference;

                $physicalQuantity = (float) $item['physical_quantity'];

                $difference = $physicalQuantity - $systemQuantity;

                /*
                 * system_quantity diperbarui menjadi stok sistem
                 * pada saat produk dihitung.
                 *
                 * Dengan demikian, hasil hitung tetap menjadi snapshot
                 * meskipun ada transaksi berikutnya.
                 */
                $detail->update([
                    'system_quantity' => $systemQuantity,
                    'physical_quantity' => $physicalQuantity,
                    'difference_quantity' => $difference,
                    'is_counted' => true,
                    'counted_at' => now(),
                    'counted_movement_id' => $countedMovementId,
                    'notes' => $item['notes'] ?? null,
                ]);

                $product = Product::find($detail->product_id);

                $logs[] = [
                    'product' => $product?->name,
                    'product_code' => $product?->code,
                    'system_quantity' => $systemQuantity,
                    'physical_quantity' => $physicalQuantity,
                    'difference_quantity' => $difference,
                    'notes' => $detail->notes,
                ];
            }

            return $logs;
        });

        ActivityLogger::log(
            'stock_opname_count_updated',
            'Petugas memperbarui hasil perhitungan fisik Stock Opname ' . $stockOpname->opname_number,
            $stockOpname,
            [
                'opname_number' => $stockOpname->opname_number,
                'updated_items' => count($updatedItems),
                'items' => $updatedItems,
                'status' => 'draft',
            ]
        );

        return redirect()
            ->route('petugas.stock-opnames.show', $stockOpname->id)
            ->with('success', 'Hasil perhitungan fisik berhasil disimpan.');
    }

    /**
     * Mengajukan Stock Opname kepada Admin.
     */
    public function submit(StockOpname $stockOpname)
    {
        $result = DB::transaction(function () use ($stockOpname) {

            $opname = StockOpname::whereKey($stockOpname->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($opname->status !== 'draft') {
                throw ValidationException::withMessages([
                    'stock_opname' => 'Stock Opname ini sudah tidak dapat disubmit.',
                ]);
            }

            $details = $opname->details()
                ->lockForUpdate()
                ->get();

            if ($details->isEmpty()) {
                throw ValidationException::withMessages([
                    'stock_opname' => 'Detail Stock Opname masih kosong.',
                ]);
            }

            if ($details->contains(fn ($detail) => !$detail->is_counted)) {
                throw ValidationException::withMessages([
                    'stock_opname' => 'Semua produk harus dihitung terlebih dahulu.',
                ]);
            }

            if ($details->contains(fn ($detail) => !$detail->product_id)) {
                throw ValidationException::withMessages([
                    'stock_opname' => 'Stock Opname ini masih menggunakan format lama. Buat Stock Opname baru untuk menggunakan format per produk.',
                ]);
            }

            $differences = $details->filter(
                fn ($detail) => abs((float) $detail->difference_quantity) >= 0.001
            );

            if ($differences->isEmpty()) {
                $opname->update([
                    'status' => 'completed',
                ]);

                return [
                    'opname' => $opname,
                    'difference_count' => 0,
                    'status' => 'completed',
                ];
            }

            foreach ($differences as $detail) {
                StockAdjustmentRequest::create([
                    'stock_opname_detail_id' => $detail->id,
                    'status' => 'pending',
                    'request_reason' => 'Penyesuaian stok berdasarkan hasil Stock Opname.',
                    'requested_by' => auth()->id(),
                ]);
            }

            $opname->update([
                'status' => 'submitted',
            ]);

            return [
                'opname' => $opname,
                'difference_count' => $differences->count(),
                'status' => 'submitted',
            ];
        });

        $opname = $result['opname'];

        $opname->load([
            'details.product',
            'details.adjustmentRequests',
        ]);

        $submittedItems = $opname->details->map(function ($detail) {
            return [
                'product' => $detail->product?->name,
                'product_code' => $detail->product?->code,
                'system_quantity' => (float) $detail->system_quantity,
                'physical_quantity' => (float) $detail->physical_quantity,
                'difference_quantity' => (float) $detail->difference_quantity,
                'notes' => $detail->notes,
                'adjustment_requests' => $detail->adjustmentRequests
                    ->map(function ($adjustmentRequest) {
                        return [
                            'id' => $adjustmentRequest->id,
                            'status' => $adjustmentRequest->status,
                            'request_reason' => $adjustmentRequest->request_reason,
                        ];
                    })
                    ->values()
                    ->toArray(),
            ];
        })->values()->toArray();

        $isCompleted = $result['status'] === 'completed';

        ActivityLogger::log(
            $isCompleted
                ? 'stock_opname_completed'
                : 'stock_opname_submitted',
            $isCompleted
                ? 'Stock Opname ' . $opname->opname_number . ' selesai tanpa selisih.'
                : 'Petugas mengirim Stock Opname ' . $opname->opname_number . ' untuk pemeriksaan Admin.',
            $opname,
            [
                'opname_number' => $opname->opname_number,
                'total_items' => $opname->details->count(),
                'items_with_difference' => $result['difference_count'],
                'status' => $opname->status,
                'items' => $submittedItems,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Kirim Notifikasi kepada Admin
        |--------------------------------------------------------------------------
        */

        if (!$isCompleted) {

            // Ambil seluruh Admin yang aktif.
            $admins = User::role('Admin')
                ->where('is_active', true)
                ->get();

            // Kirim satu notifikasi untuk setiap Admin.
            Notification::send(
                $admins,
                new StockOpnameSubmitted(
                    $opname,
                    $result['difference_count']
                )
            );
        }

        return redirect()
            ->route('petugas.stock-opnames.show', $stockOpname)
            ->with(
                'success',
                $isCompleted
                    ? 'Stock Opname berhasil diselesaikan tanpa selisih stok.'
                    : 'Stock Opname berhasil disubmit dan menunggu pemeriksaan Admin.'
            );
    }
}