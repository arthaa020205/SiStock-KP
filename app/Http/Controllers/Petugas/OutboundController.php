<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\BatchStock;
use App\Models\Customer;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\StockTransaction;
use App\Models\StockTransactionBatchDetail;
use App\Models\StockTransactionDetail;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Services\ActivityLogger;

class OutboundController extends Controller
{
    public function index()
    {
        $outbounds = StockTransaction::with(['creator', 'customer'])
            ->where('transaction_type', 'outbound')
            ->latest('transaction_date')
            ->paginate(10);

        return view('petugas.outbounds.index', compact('outbounds'));
    }

    
    public function create()
    {
        $products = Product::with([
            'baseUnit',
            'unitConversions.unit',
            'batches.batchStocks.rackLocation',
        ])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $productData = $products->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'code' => $product->code,
                'base_unit_id' => $product->base_unit_id,
                'base_unit_name' => $product->baseUnit?->name ?? '-',
                'base_unit_symbol' => $product->baseUnit?->symbol ?? '',

                'conversions' => $product->unitConversions->map(function ($conversion) {
                    return [
                        'unit_id' => $conversion->unit_id,
                        'unit_name' => $conversion->unit?->name ?? '-',
                        'unit_symbol' => $conversion->unit?->symbol ?? '',
                        'factor' => (float) $conversion->conversion_factor,
                    ];
                })->values(),

                'stocks' => $product->batches->flatMap(function ($batch) {
                    return $batch->batchStocks
                        ->filter(function ($stock) {
                            return (float) $stock->quantity > 0;
                        })
                        ->map(function ($stock) use ($batch) {
                            return [
                                'id' => $stock->id,
                                'batch_code' => $batch->batch_code,
                                'expiry_date' => $batch->expiry_date?->format('d-m-Y') ?? '-',
                                'rack' => $stock->rackLocation?->code ?? '-',
                                'rack_name' => $stock->rackLocation?->name ?? '',
                                'quantity' => (float) $stock->quantity,
                            ];
                        });
                })->values(),
            ];
        })->values();

        $units = Unit::orderBy('name')->get();

        $customers = Customer::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('petugas.outbounds.create', compact(
            'products',
            'productData',
            'units',
            'customers'
        ));
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'transaction_date' => ['required', 'date'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'reason' => [
                'required',
                'in:penjualan,transfer_toko,rusak,kadaluarsa,pemakaian,lainnya'
            ],
            'notes' => ['nullable', 'string'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => [
                'required',
                'exists:products,id'
            ],
            'items.*.unit_id' => [
                'required',
                'exists:units,id'
            ],
            'items.*.quantity' => [
                'required',
                'numeric',
                'gt:0'
            ],

            'items.*.allocations' => [
                'required',
                'array',
                'min:1'
            ],
            'items.*.allocations.*.batch_stock_id' => [
                'required',
                'exists:batch_stocks,id'
            ],
            'items.*.allocations.*.base_quantity' => [
                'required',
                'numeric',
                'gt:0'
            ],
        ]);

        $transaction = DB::transaction(function () use ($validated) {
            $transaction = StockTransaction::create([
                'transaction_number' => $this->generateTransactionNumber(),
                'transaction_type' => 'outbound',
                'transaction_date' => $validated['transaction_date'],
                'customer_id' => $validated['customer_id'] ?? null,
                'reason' => $validated['reason'],
                'status' => 'draft',
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            foreach ($validated['items'] as $item) {
                $product = Product::with('unitConversions')
                    ->findOrFail($item['product_id']);

                $baseQuantity = $this->convertToBaseQuantity(
                    $product,
                    (int) $item['unit_id'],
                    (float) $item['quantity']
                );

                $allocatedQuantity = 0;

                foreach ($item['allocations'] as $allocation) {
                    $batchStock = BatchStock::with('productBatch')
                        ->findOrFail($allocation['batch_stock_id']);

                    if (
                        (int) $batchStock->productBatch->product_id
                        !== (int) $product->id
                    ) {
                        throw ValidationException::withMessages([
                            'items' => 'Batch yang dipilih tidak sesuai dengan barang ' .
                                $product->name . '.'
                        ]);
                    }

                    $allocatedQuantity += (float) $allocation['base_quantity'];
                }

                if (abs($allocatedQuantity - $baseQuantity) > 0.001) {
                    throw ValidationException::withMessages([
                        'items' => 'Total alokasi batch untuk barang ' .
                            $product->name .
                            ' harus sama dengan jumlah barang setelah dikonversi ke satuan dasar.'
                    ]);
                }

                $detail = StockTransactionDetail::create([
                    'stock_transaction_id' => $transaction->id,
                    'product_id' => $product->id,
                    'unit_id' => $item['unit_id'],
                    'quantity' => $item['quantity'],
                    'base_quantity' => $baseQuantity,
                ]);

                foreach ($item['allocations'] as $allocation) {
                    StockTransactionBatchDetail::create([
                        'stock_transaction_detail_id' => $detail->id,
                        'source_batch_stock_id' => $allocation['batch_stock_id'],
                        'destination_batch_stock_id' => null,
                        'quantity' => $allocation['base_quantity'],
                    ]);
                }
            }
            return $transaction;
        });

        ActivityLogger::log(
            'outbound_created',
            'Membuat draft barang keluar dengan nomor transaksi ' . $transaction->transaction_number,
            $transaction,
            [
                'transaction_number' => $transaction->transaction_number,
                'reason' => $transaction->reason,
                'customer_id' => $transaction->customer_id,
                'total_items' => count($validated['items']),
                'status' => 'draft',
            ]
        );

        return redirect()
            ->route('petugas.outbounds.index')
            ->with('success', 'Transaksi barang keluar berhasil disimpan sebagai Draft.');
    }

    public function show(StockTransaction $outbound)
    {
        abort_unless($outbound->transaction_type === 'outbound', 404);

        $outbound->load([
            'creator',
            'customer',
            'details.product',
            'details.unit',
            'details.batchDetails.sourceBatchStock.productBatch',
            'details.batchDetails.sourceBatchStock.rackLocation',
        ]);

        return view('petugas.outbounds.show', compact('outbound'));
    }

    public function post(StockTransaction $outbound)
    {
        abort_unless($outbound->transaction_type === 'outbound', 404);

        $transactionNumber = $outbound->transaction_number;
        $transactionReason = $outbound->reason;
        $customerId = $outbound->customer_id;
        
        DB::transaction(function () use ($outbound) {
            $transaction = StockTransaction::whereKey($outbound->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($transaction->status !== 'draft') {
                throw ValidationException::withMessages([
                    'transaction' => 'Hanya transaksi Draft yang dapat diposting.'
                ]);
            }

            $transaction->load([
                'details.product',
                'details.batchDetails',
            ]);

            foreach ($transaction->details as $detail) {
                $totalPostedQuantity = 0;

                foreach ($detail->batchDetails as $batchDetail) {
                    $batchStock = BatchStock::whereKey(
                        $batchDetail->source_batch_stock_id
                    )
                        ->lockForUpdate()
                        ->firstOrFail();

                    $quantity = (float) $batchDetail->quantity;
                    $balanceBefore = (float) $batchStock->quantity;

                    if ($balanceBefore < $quantity) {
                        throw ValidationException::withMessages([
                            'stock' => 'Stok tidak mencukupi untuk barang ' .
                                $detail->product->name .
                                '. Stok tersedia pada batch/rak yang dipilih: ' .
                                $balanceBefore .
                                ', jumlah yang dibutuhkan: ' . $quantity . '.'
                        ]);
                    }

                    $balanceAfter = $balanceBefore - $quantity;

                    $batchStock->update([
                        'quantity' => $balanceAfter,
                    ]);

                    StockMovement::create([
                        'batch_stock_id' => $batchStock->id,
                        'movement_type' => 'out',
                        'quantity' => $quantity,
                        'balance_before' => $balanceBefore,
                        'balance_after' => $balanceAfter,
                        'reference_type' => StockTransaction::class,
                        'reference_id' => $transaction->id,
                        'notes' => 'Barang keluar: ' . $transaction->reason,
                        'created_by' => auth()->id(),
                    ]);

                    $totalPostedQuantity += $quantity;
                }

                if (
                    abs($totalPostedQuantity - (float) $detail->base_quantity)
                    > 0.001
                ) {
                    throw ValidationException::withMessages([
                        'stock' => 'Total alokasi batch tidak sesuai dengan jumlah barang ' .
                            $detail->product->name . '.'
                    ]);
                }
            }

            $transaction->update([
                'status' => 'posted',
            ]);
                });

            ActivityLogger::log(
                'outbound_posted',
                'Melakukan posting barang keluar dengan nomor transaksi ' . $transactionNumber,
                $outbound,
                [
                    'transaction_number' => $transactionNumber,
                    'reason' => $transactionReason,
                    'customer_id' => $customerId,
                    'total_items' => $outbound->details()->count(),
                    'status' => 'posted',
                ]
            );

            return redirect()
                ->route('petugas.outbounds.show', $outbound->id)
                ->with('success', 'Transaksi barang keluar berhasil diposting dan stok telah diperbarui.');
        }

    private function convertToBaseQuantity(
        Product $product,
        int $unitId,
        float $quantity
    ): float {
        if ((int) $product->base_unit_id === $unitId) {
            return $quantity;
        }

        $conversion = $product->unitConversions
            ->firstWhere('unit_id', $unitId);

        if (!$conversion) {
            throw ValidationException::withMessages([
                'items' => 'Satuan yang dipilih tidak memiliki konversi untuk barang ' .
                    $product->name . '.'
            ]);
        }

        return $quantity * (float) $conversion->conversion_factor;
    }

    private function generateTransactionNumber(): string
    {
        $date = now()->format('Ymd');

        $lastTransaction = StockTransaction::where(
            'transaction_type',
            'outbound'
        )
            ->whereDate('created_at', today())
            ->lockForUpdate()
            ->count();

        return 'OUT-' . $date . '-' . str_pad(
            $lastTransaction + 1,
            4,
            '0',
            STR_PAD_LEFT
        );
    }
}
