<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\BatchStock;
use App\Models\Product;
use App\Models\RackLocation;
use App\Models\StockMovement;
use App\Models\StockTransaction;
use App\Models\StockTransactionBatchDetail;
use App\Models\StockTransactionDetail;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TransferController extends Controller
{
    /**
     * Menampilkan daftar transaksi transfer.
     */
    public function index()
    {
        $transfers = StockTransaction::with('creator')
            ->where('transaction_type', 'transfer')
            ->latest()
            ->paginate(10);

        return view('petugas.transfers.index', compact('transfers'));
    }

    /**
     * Menampilkan form transfer.
     */
    public function create()
    {
        $products = Product::with([
            'baseUnit',
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
                                'rack_location_id' => $stock->rack_location_id,
                                'rack_code' => $stock->rackLocation?->code ?? '-',
                                'rack_name' => $stock->rackLocation?->name ?? '-',
                                'quantity' => (float) $stock->quantity,
                            ];
                        });
                })->values(),
            ];
        })->values();

        $rackLocations = RackLocation::where('is_active', true)
            ->orderBy('code')
            ->get();

        return view('petugas.transfers.create', compact(
            'products',
            'productData',
            'rackLocations'
        ));
    }

    /**
     * Menyimpan transaksi transfer sebagai Draft.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'transaction_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.source_batch_stock_id' => [
                'required',
                'exists:batch_stocks,id',
            ],
            'items.*.destination_rack_location_id' => [
                'required',
                'exists:rack_locations,id',
            ],
            'items.*.quantity' => [
                'required',
                'numeric',
                'gt:0',
            ],
        ]);

        $transaction = DB::transaction(function () use ($validated) {
            $transaction = StockTransaction::create([
                'transaction_number' => $this->generateTransactionNumber(),
                'transaction_type' => 'transfer',
                'transaction_date' => $validated['transaction_date'],
                'supplier_id' => null,
                'customer_id' => null,
                'reason' => 'transfer_rak',
                'status' => 'draft',
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            foreach ($validated['items'] as $item) {
                $source = BatchStock::with('productBatch')
                    ->lockForUpdate()
                    ->findOrFail($item['source_batch_stock_id']);

                $product = Product::findOrFail($item['product_id']);

                if ($source->productBatch->product_id != $product->id) {
                    throw ValidationException::withMessages([
                        'items' => 'Batch yang dipilih tidak sesuai dengan produk.',
                    ]);
                }

                if (
                    (int) $source->rack_location_id ===
                    (int) $item['destination_rack_location_id']
                ) {
                    throw ValidationException::withMessages([
                        'items' => 'Rak asal dan rak tujuan tidak boleh sama.',
                    ]);
                }

                if ((float) $source->quantity < (float) $item['quantity']) {
                    throw ValidationException::withMessages([
                        'items' => 'Stok pada rak asal tidak mencukupi.',
                    ]);
                }

                $destination = BatchStock::firstOrCreate(
                    [
                        'product_batch_id' => $source->product_batch_id,
                        'rack_location_id' => $item['destination_rack_location_id'],
                    ],
                    [
                        'quantity' => 0,
                    ]
                );

                $detail = StockTransactionDetail::create([
                    'stock_transaction_id' => $transaction->id,
                    'product_id' => $product->id,
                    'unit_id' => $product->base_unit_id,
                    'quantity' => $item['quantity'],
                    'base_quantity' => $item['quantity'],
                ]);

                StockTransactionBatchDetail::create([
                    'stock_transaction_detail_id' => $detail->id,
                    'source_batch_stock_id' => $source->id,
                    'destination_batch_stock_id' => $destination->id,
                    'quantity' => $item['quantity'],
                ]);
            }
            return $transaction;
            });

            $transaction->load([
                'details.product',
                'details.batchDetails.sourceBatchStock.productBatch',
                'details.batchDetails.sourceBatchStock.rackLocation',
                'details.batchDetails.destinationBatchStock.rackLocation',
            ]);

            $transferItems = $transaction->details->map(function ($detail) {
                return [
                    'product' => $detail->product->name,
                    'quantity' => (float) $detail->base_quantity,
                    'allocations' => $detail->batchDetails->map(function ($allocation) {
                        return [
                            'batch_code' => $allocation->sourceBatchStock->productBatch->batch_code,
                            'source_rack' => $allocation->sourceBatchStock->rackLocation->code,
                            'destination_rack' => $allocation->destinationBatchStock->rackLocation->code,
                            'quantity' => (float) $allocation->quantity,
                        ];
                    })->values()->toArray(),
                ];
            })->values()->toArray();

            ActivityLogger::log(
                'transfer_created',
                'Membuat draft transfer stok dengan nomor transaksi ' . $transaction->transaction_number,
                $transaction,
                [
                    'transaction_number' => $transaction->transaction_number,
                    'total_items' => count($validated['items']),
                    'status' => 'draft',
                    'items' => $transferItems,
                ]
            );

            return redirect()
                ->route('petugas.transfers.index')
                ->with('success', 'Transaksi transfer berhasil disimpan sebagai Draft.');
        }

    /**
     * Menampilkan detail transaksi transfer.
     */
    public function show($transfer)
    {
        $transfer = StockTransaction::with([
            'creator',
            'details.product.baseUnit',
            'details.unit',
            'details.batchDetails.sourceBatchStock.productBatch',
            'details.batchDetails.sourceBatchStock.rackLocation',
            'details.batchDetails.destinationBatchStock.productBatch',
            'details.batchDetails.destinationBatchStock.rackLocation',
        ])
            ->where('transaction_type', 'transfer')
            ->findOrFail($transfer);

        return view('petugas.transfers.show', compact('transfer'));
    }

    /**
     * Melakukan Posting transaksi transfer.
     */
    public function post($transfer)
    {
        $transaction = DB::transaction(function () use ($transfer) {
            $transaction = StockTransaction::where(
                'transaction_type',
                'transfer'
            )
                ->lockForUpdate()
                ->findOrFail($transfer);

            if ($transaction->status !== 'draft') {
                throw ValidationException::withMessages([
                    'transfer' => 'Hanya transaksi Draft yang dapat diposting.',
                ]);
            }

            $transaction->load([
                'details.product',
                'details.batchDetails',
            ]);

            /*
             * Hitung total kebutuhan setiap stok asal.
             * Ini mencegah beberapa alokasi pada batch yang sama
             * mengambil stok melebihi jumlah tersedia.
             */
            $requiredStocks = [];

            foreach ($transaction->details as $detail) {
                foreach ($detail->batchDetails as $allocation) {
                    $sourceId = $allocation->source_batch_stock_id;

                    if (!isset($requiredStocks[$sourceId])) {
                        $requiredStocks[$sourceId] = 0;
                    }

                    $requiredStocks[$sourceId] += (float) $allocation->quantity;
                }
            }

            /*
             * Kunci dan validasi seluruh stok asal terlebih dahulu.
             */
            $lockedSources = [];

            foreach ($requiredStocks as $sourceId => $requiredQuantity) {
                $source = BatchStock::lockForUpdate()->findOrFail($sourceId);

                if ((float) $source->quantity < $requiredQuantity) {
                    throw ValidationException::withMessages([
                        'transfer' =>
                            'Stok tidak mencukupi pada salah satu rak asal. ' .
                            'Stok tersedia: ' . $source->quantity .
                            ', jumlah dibutuhkan: ' . $requiredQuantity . '.',
                    ]);
                }

                $lockedSources[$sourceId] = $source;
            }

            /*
             * Setelah semua stok lolos validasi,
             * lakukan perpindahan stok.
             */
            foreach ($transaction->details as $detail) {
                $allocatedTotal = 0;

                foreach ($detail->batchDetails as $allocation) {
                    $source = $lockedSources[
                        $allocation->source_batch_stock_id
                    ];

                    $destination = BatchStock::lockForUpdate()
                        ->findOrFail($allocation->destination_batch_stock_id);

                    $quantity = (float) $allocation->quantity;

                    $sourceBefore = (float) $source->quantity;
                    $destinationBefore = (float) $destination->quantity;

                    $source->decrement('quantity', $quantity);
                    $destination->increment('quantity', $quantity);

                    $sourceAfter = $sourceBefore - $quantity;
                    $destinationAfter = $destinationBefore + $quantity;

                    StockMovement::create([
                        'batch_stock_id' => $source->id,
                        'movement_type' => 'out',
                        'quantity' => $quantity,
                        'balance_before' => $sourceBefore,
                        'balance_after' => $sourceAfter,
                        'reference_type' => StockTransaction::class,
                        'reference_id' => $transaction->id,
                        'notes' => 'Transfer keluar ke rak ' .
                            ($destination->rackLocation?->code ?? ''),
                        'created_by' => $transaction->created_by,
                    ]);

                    StockMovement::create([
                        'batch_stock_id' => $destination->id,
                        'movement_type' => 'in',
                        'quantity' => $quantity,
                        'balance_before' => $destinationBefore,
                        'balance_after' => $destinationAfter,
                        'reference_type' => StockTransaction::class,
                        'reference_id' => $transaction->id,
                        'notes' => 'Transfer masuk dari rak ' .
                            ($source->rackLocation?->code ?? ''),
                        'created_by' => $transaction->created_by,
                    ]);

                    /*
                     * Perbarui objek sumber agar alokasi berikutnya
                     * menggunakan saldo terbaru.
                     */
                    $source->quantity = $sourceAfter;
                    $destination->quantity = $destinationAfter;

                    $allocatedTotal += $quantity;
                }

                if (
                    abs($allocatedTotal - (float) $detail->base_quantity) > 0.001
                ) {
                    throw ValidationException::withMessages([
                        'transfer' =>
                            'Total alokasi transfer tidak sesuai dengan jumlah barang.',
                    ]);
                }
            }

            $transaction->update([
                'status' => 'posted',
            ]);
            return $transaction;
            });

            $transaction->load([
                'details.product',
                'details.batchDetails.sourceBatchStock.productBatch',
                'details.batchDetails.sourceBatchStock.rackLocation',
                'details.batchDetails.destinationBatchStock.rackLocation',
            ]);

            $transferItems = $transaction->details->map(function ($detail) {
                return [
                    'product' => $detail->product->name,
                    'product_code' => $detail->product->code,
                    'quantity' => (float) $detail->base_quantity,

                    'allocations' => $detail->batchDetails->map(function ($allocation) {
                        return [
                            'batch_code' => $allocation->sourceBatchStock->productBatch->batch_code,
                            'source_rack' => $allocation->sourceBatchStock->rackLocation->code,
                            'destination_rack' => $allocation->destinationBatchStock->rackLocation->code,
                            'quantity' => (float) $allocation->quantity,
                        ];
                    })->values()->toArray(),
                ];
            })->values()->toArray();

            $stockMovements = StockMovement::with([
                'batchStock.productBatch.product',
                'batchStock.rackLocation',
            ])
                ->where('reference_type', StockTransaction::class)
                ->where('reference_id', $transaction->id)
                ->get()
                ->map(function ($movement) {
                    return [
                        'product' => $movement->batchStock->productBatch->product->name,
                        'product_code' => $movement->batchStock->productBatch->product->code,
                        'batch_code' => $movement->batchStock->productBatch->batch_code,
                        'rack' => $movement->batchStock->rackLocation->code,
                        'movement_type' => $movement->movement_type,
                        'quantity' => (float) $movement->quantity,
                        'balance_before' => (float) $movement->balance_before,
                        'balance_after' => (float) $movement->balance_after,
                    ];
                })->values()->toArray();

            ActivityLogger::log(
                'transfer_posted',
                'Melakukan posting transfer stok dengan nomor transaksi ' . $transaction->transaction_number,
                $transaction,
                [
                    'transaction_number' => $transaction->transaction_number,
                    'total_items' => $transaction->details->count(),
                    'status' => 'posted',
                    'items' => $transferItems,
                    'stock_movements' => $stockMovements,
                ]
            );

            return redirect()
                ->route('petugas.transfers.show', $transfer)
                ->with('success', 'Transfer stok berhasil diposting.');
        }

    /**
     * Membuat nomor transaksi transfer.
     */
    private function generateTransactionNumber()
    {
        $date = now()->format('Ymd');

        $count = StockTransaction::where(
            'transaction_type',
            'transfer'
        )
            ->whereDate('created_at', today())
            ->count() + 1;

        return 'TRF-' . $date . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}
