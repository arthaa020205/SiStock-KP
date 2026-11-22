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
use App\Models\ProductUnitConversion;

class OutboundController extends Controller
{

    public function index()
    {
        $outbounds = StockTransaction::with(['creator', 'customer'])
            ->where('transaction_type', 'outbound')
            ->orderByRaw("CASE
                WHEN status = 'draft' THEN 0
                WHEN status = 'posted' THEN 1
                ELSE 2
            END")
            ->orderByDesc('transaction_date')
            ->orderByDesc('created_at')
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

                // Harga jual satuan dasar
                'price_per_base_unit' => (float) $product->price_per_base_unit,

                // Harga dan faktor setiap satuan konversi
                'conversions' => $product->unitConversions->map(function ($conversion) {
                    return [
                        'unit_id' => $conversion->unit_id,
                        'unit_name' => $conversion->unit?->name ?? '-',
                        'unit_symbol' => $conversion->unit?->symbol ?? '',
                        'factor' => (float) $conversion->conversion_factor,
                        'selling_price' => $conversion->selling_price !== null
                            ? (float) $conversion->selling_price
                            : null,
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

            // Alokasi boleh tidak dikirim untuk menggunakan FEFO otomatis.
            'items.*.allocations' => [
                'sometimes',
                'array'
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

            /*
            |--------------------------------------------------------------------------
            | Alokasi Manual Wajib untuk Barang Rusak dan Kadaluarsa
            |--------------------------------------------------------------------------
            */

            $manualRequiredReasons = ['rusak', 'kadaluarsa'];

            /*
            |--------------------------------------------------------------------------
            | Menyimpan Total Stok yang Sudah Dialokasikan dalam Draft Ini
            |--------------------------------------------------------------------------
            |
            | Berguna ketika satu batch dipakai oleh lebih dari satu detail barang.
            | Stok tidak boleh dialokasikan melebihi stok aktual.
            |
            */

            $plannedQuantities = [];

            foreach ($validated['items'] as $item) {

                $product = Product::with([
                    'unitConversions',
                    'baseUnit',
                ])->findOrFail($item['product_id']);

                $unit = Unit::findOrFail($item['unit_id']);

                $quantity = (float) $item['quantity'];

                $baseQuantity = $this->convertToBaseQuantity(
                    $product,
                    (int) $item['unit_id'],
                    $quantity
                );

                /*
                |--------------------------------------------------------------------------
                | Menentukan Harga Penjualan
                |--------------------------------------------------------------------------
                */

                $unitPrice = null;
                $subtotal = null;

                if ($validated['reason'] === 'penjualan') {

                    // Jika menggunakan satuan dasar
                    if ((int) $product->base_unit_id === (int) $item['unit_id']) {

                        $unitPrice = $product->price_per_base_unit !== null
                            ? (float) $product->price_per_base_unit
                            : null;

                    } else {

                        // Jika menggunakan satuan konversi
                        $conversion = $product->unitConversions
                            ->firstWhere('unit_id', (int) $item['unit_id']);

                        $unitPrice = $conversion && $conversion->selling_price !== null
                            ? (float) $conversion->selling_price
                            : null;
                    }

                    if ($unitPrice === null || $unitPrice <= 0) {
                        throw ValidationException::withMessages([
                            'items' => 'Harga jual satuan ' .
                                $unit->name .
                                ' untuk barang ' .
                                $product->name .
                                ' belum diatur atau masih bernilai 0. Silakan atur harga jual terlebih dahulu.'
                        ]);
                    }

                    $subtotal = round($unitPrice * $quantity, 2);
                }

                /*
                |--------------------------------------------------------------------------
                | Menentukan Metode Alokasi Batch
                |--------------------------------------------------------------------------
                */

                $manualAllocations = $item['allocations'] ?? [];

                $isManualRequired = in_array(
                    $validated['reason'],
                    $manualRequiredReasons
                );

                if ($isManualRequired && empty($manualAllocations)) {
                    throw ValidationException::withMessages([
                        'items' => 'Barang ' . $product->name .
                            ' harus memiliki alokasi batch manual.'
                    ]);
                }

                $allocationsToSave = [];

                /*
                |--------------------------------------------------------------------------
                | A. ALOKASI MANUAL
                |--------------------------------------------------------------------------
                */

                if (!empty($manualAllocations)) {

                    $allocatedQuantity = 0;

                    foreach ($manualAllocations as $allocation) {

                        $batchStock = BatchStock::with('productBatch')
                            ->whereKey($allocation['batch_stock_id'])
                            ->lockForUpdate()
                            ->firstOrFail();

                        if (
                            !$batchStock->productBatch ||
                            (int) $batchStock->productBatch->product_id
                                !== (int) $product->id
                        ) {
                            throw ValidationException::withMessages([
                                'items' => 'Batch yang dipilih tidak sesuai dengan barang ' .
                                    $product->name . '.'
                            ]);
                        }

                        $allocationQuantity = (float) $allocation['base_quantity'];

                        $alreadyPlanned = $plannedQuantities[$batchStock->id] ?? 0;

                        $availableQuantity =
                            (float) $batchStock->quantity - $alreadyPlanned;

                        if ($allocationQuantity > $availableQuantity + 0.000001) {
                            throw ValidationException::withMessages([
                                'items' => 'Stok batch ' .
                                    $batchStock->productBatch->batch_code .
                                    ' tidak mencukupi untuk barang ' .
                                    $product->name . '.'
                            ]);
                        }

                        $plannedQuantities[$batchStock->id] =
                            $alreadyPlanned + $allocationQuantity;

                        $allocatedQuantity += $allocationQuantity;

                        $allocationsToSave[] = [
                            'batch_stock_id' => $batchStock->id,
                            'quantity' => $allocationQuantity,
                        ];
                    }

                    if (abs($allocatedQuantity - $baseQuantity) > 0.001) {
                        throw ValidationException::withMessages([
                            'items' => 'Total alokasi batch untuk barang ' .
                                $product->name .
                                ' harus sama dengan jumlah setelah dikonversi ke satuan dasar.'
                        ]);
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | B. ALOKASI OTOMATIS FEFO
                |--------------------------------------------------------------------------
                */

                else {

                    $remainingQuantity = $baseQuantity;

                    $batchStocks = BatchStock::query()
                        ->join(
                            'product_batches',
                            'batch_stocks.product_batch_id',
                            '=',
                            'product_batches.id'
                        )
                        ->where('product_batches.product_id', $product->id)
                        ->where('batch_stocks.quantity', '>', 0)

                        // Batch kedaluwarsa tidak digunakan untuk pengeluaran normal.
                        ->where(function ($query) use ($validated) {
                            $query->whereNull('product_batches.expiry_date')
                                ->orWhereDate(
                                    'product_batches.expiry_date',
                                    '>=',
                                    $validated['transaction_date']
                                );
                        })
                        // FEFO: tanggal kedaluwarsa terdekat diprioritaskan.
                        ->orderByRaw('product_batches.expiry_date IS NULL ASC')
                        ->orderBy('product_batches.expiry_date', 'asc')
                        ->orderBy('batch_stocks.id', 'asc')
                        ->select('batch_stocks.*')
                        ->with('productBatch')
                        ->lockForUpdate()
                        ->get();

                    foreach ($batchStocks as $batchStock) {

                        if ($remainingQuantity <= 0.000001) {
                            break;
                        }

                        $alreadyPlanned =
                            $plannedQuantities[$batchStock->id] ?? 0;

                        $availableQuantity =
                            (float) $batchStock->quantity - $alreadyPlanned;

                        if ($availableQuantity <= 0) {
                            continue;
                        }

                        $takeQuantity = min(
                            $remainingQuantity,
                            $availableQuantity
                        );

                        $plannedQuantities[$batchStock->id] =
                            $alreadyPlanned + $takeQuantity;

                        $allocationsToSave[] = [
                            'batch_stock_id' => $batchStock->id,
                            'quantity' => $takeQuantity,
                        ];

                        $remainingQuantity -= $takeQuantity;
                    }

                    if ($remainingQuantity > 0.001) {
                        throw ValidationException::withMessages([
                            'items' => 'Stok tersedia untuk barang ' .
                                $product->name .
                                ' tidak mencukupi. Kekurangan stok: ' .
                                number_format($remainingQuantity, 3, ',', '.') .
                                ' ' . ($product->baseUnit?->symbol ?? '') . '.'
                        ]);
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | Simpan Detail Transaksi
                |--------------------------------------------------------------------------
                */

                $detail = StockTransactionDetail::create([
                    'stock_transaction_id' => $transaction->id,
                    'product_id' => $product->id,
                    'unit_id' => $item['unit_id'],
                    'quantity' => $quantity,
                    'base_quantity' => $baseQuantity,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                ]);

                /*
                |--------------------------------------------------------------------------
                | Simpan Alokasi Batch
                |--------------------------------------------------------------------------
                */

                foreach ($allocationsToSave as $allocation) {

                    StockTransactionBatchDetail::create([
                        'stock_transaction_detail_id' => $detail->id,
                        'source_batch_stock_id' => $allocation['batch_stock_id'],
                        'destination_batch_stock_id' => null,
                        'quantity' => $allocation['quantity'],
                    ]);
                }
            }

            return $transaction;
        });

        /*
        |--------------------------------------------------------------------------
        | Activity Log
        |--------------------------------------------------------------------------
        */

        ActivityLogger::log(
            'outbound_created',
            'Membuat draft barang keluar dengan nomor transaksi ' .
                $transaction->transaction_number,
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
            ->with(
                'success',
                'Transaksi barang keluar berhasil disimpan sebagai Draft.'
            );
    }

    public function update(Request $request, StockTransaction $outbound)
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
                'sometimes',
                'array'
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

        DB::transaction(function () use ($validated, $outbound) {

            /*
            |--------------------------------------------------------------------------
            | Kunci dan Periksa Transaksi
            |--------------------------------------------------------------------------
            */

            $transaction = StockTransaction::whereKey($outbound->id)
                ->lockForUpdate()
                ->firstOrFail();

            abort_unless(
                $transaction->transaction_type === 'outbound',
                404
            );

            if ($transaction->status !== 'draft') {
                throw ValidationException::withMessages([
                    'transaction' => 'Transaksi yang sudah diposting tidak dapat diubah.'
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Perbarui Header Transaksi
            |--------------------------------------------------------------------------
            */

            $transaction->update([
                'transaction_date' => $validated['transaction_date'],
                'customer_id' => $validated['customer_id'] ?? null,
                'reason' => $validated['reason'],
                'notes' => $validated['notes'] ?? null,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Hapus Detail Lama
            |--------------------------------------------------------------------------
            |
            | Detail alokasi batch ikut terhapus melalui cascade.
            | Tidak ada pengurangan stok karena transaksi masih Draft.
            |
            */

            StockTransactionDetail::where(
                'stock_transaction_id',
                $transaction->id
            )->delete();

            $manualRequiredReasons = ['rusak', 'kadaluarsa'];

            $plannedQuantities = [];

            /*
            |--------------------------------------------------------------------------
            | Buat Detail dan Alokasi Baru
            |--------------------------------------------------------------------------
            */

            foreach ($validated['items'] as $item) {

                $product = Product::with([
                    'unitConversions',
                    'baseUnit',
                ])->findOrFail($item['product_id']);

                $unit = Unit::findOrFail($item['unit_id']);

                $quantity = (float) $item['quantity'];

                $baseQuantity = $this->convertToBaseQuantity(
                    $product,
                    (int) $item['unit_id'],
                    $quantity
                );

                /*
                |--------------------------------------------------------------------------
                | Menentukan Harga Penjualan
                |--------------------------------------------------------------------------
                */

                $unitPrice = null;
                $subtotal = null;

                if ($validated['reason'] === 'penjualan') {

                    if ((int) $product->base_unit_id === (int) $item['unit_id']) {

                        $unitPrice = $product->price_per_base_unit !== null
                            ? (float) $product->price_per_base_unit
                            : null;

                    } else {

                        $conversion = $product->unitConversions
                            ->firstWhere('unit_id', (int) $item['unit_id']);

                        $unitPrice = $conversion && $conversion->selling_price !== null
                            ? (float) $conversion->selling_price
                            : null;
                    }

                    if ($unitPrice === null || $unitPrice <= 0) {
                        throw ValidationException::withMessages([
                            'items' => 'Harga jual satuan ' .
                                $unit->name .
                                ' untuk barang ' .
                                $product->name .
                                ' belum diatur atau masih bernilai 0. Silakan atur harga jual terlebih dahulu.'
                        ]);
                    }

                    $subtotal = round($unitPrice * $quantity, 2);
                }

                /*
                |--------------------------------------------------------------------------
                | Menentukan Metode Alokasi Batch
                |--------------------------------------------------------------------------
                */

                $manualAllocations = $item['allocations'] ?? [];

                $isManualRequired = in_array(
                    $validated['reason'],
                    $manualRequiredReasons
                );

                if ($isManualRequired && empty($manualAllocations)) {
                    throw ValidationException::withMessages([
                        'items' => 'Barang ' . $product->name .
                            ' harus memiliki alokasi batch manual.'
                    ]);
                }

                $allocationsToSave = [];

                /*
                |--------------------------------------------------------------------------
                | A. ALOKASI MANUAL
                |--------------------------------------------------------------------------
                */

                if (!empty($manualAllocations)) {

                    $allocatedQuantity = 0;

                    foreach ($manualAllocations as $allocation) {

                        $batchStock = BatchStock::with('productBatch')
                            ->whereKey($allocation['batch_stock_id'])
                            ->lockForUpdate()
                            ->firstOrFail();

                        if (
                            !$batchStock->productBatch ||
                            (int) $batchStock->productBatch->product_id
                                !== (int) $product->id
                        ) {
                            throw ValidationException::withMessages([
                                'items' => 'Batch yang dipilih tidak sesuai dengan barang ' .
                                    $product->name . '.'
                            ]);
                        }

                        $allocationQuantity = (float) $allocation['base_quantity'];

                        $alreadyPlanned =
                            $plannedQuantities[$batchStock->id] ?? 0;

                        $availableQuantity =
                            (float) $batchStock->quantity - $alreadyPlanned;

                        if ($allocationQuantity > $availableQuantity + 0.000001) {
                            throw ValidationException::withMessages([
                                'items' => 'Stok batch ' .
                                    $batchStock->productBatch->batch_code .
                                    ' tidak mencukupi untuk barang ' .
                                    $product->name . '.'
                            ]);
                        }

                        $plannedQuantities[$batchStock->id] =
                            $alreadyPlanned + $allocationQuantity;

                        $allocatedQuantity += $allocationQuantity;

                        $allocationsToSave[] = [
                            'batch_stock_id' => $batchStock->id,
                            'quantity' => $allocationQuantity,
                        ];
                    }

                    if (abs($allocatedQuantity - $baseQuantity) > 0.001) {
                        throw ValidationException::withMessages([
                            'items' => 'Total alokasi batch untuk barang ' .
                                $product->name .
                                ' harus sama dengan jumlah setelah dikonversi ke satuan dasar.'
                        ]);
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | B. ALOKASI OTOMATIS FEFO
                |--------------------------------------------------------------------------
                */

                else {

                    $remainingQuantity = $baseQuantity;

                    $batchStocks = BatchStock::query()
                        ->join(
                            'product_batches',
                            'batch_stocks.product_batch_id',
                            '=',
                            'product_batches.id'
                        )
                        ->where('product_batches.product_id', $product->id)
                        ->where('batch_stocks.quantity', '>', 0)
                        ->where(function ($query) use ($validated) {
                            $query->whereNull('product_batches.expiry_date')
                                ->orWhereDate(
                                    'product_batches.expiry_date',
                                    '>=',
                                    $validated['transaction_date']
                                );
                        })
                        ->orderByRaw('product_batches.expiry_date IS NULL ASC')
                        ->orderBy('product_batches.expiry_date', 'asc')
                        ->orderBy('batch_stocks.id', 'asc')
                        ->select('batch_stocks.*')
                        ->with('productBatch')
                        ->lockForUpdate()
                        ->get();

                    foreach ($batchStocks as $batchStock) {

                        if ($remainingQuantity <= 0.000001) {
                            break;
                        }

                        $alreadyPlanned =
                            $plannedQuantities[$batchStock->id] ?? 0;

                        $availableQuantity =
                            (float) $batchStock->quantity - $alreadyPlanned;

                        if ($availableQuantity <= 0) {
                            continue;
                        }

                        $takeQuantity = min(
                            $remainingQuantity,
                            $availableQuantity
                        );

                        $plannedQuantities[$batchStock->id] =
                            $alreadyPlanned + $takeQuantity;

                        $allocationsToSave[] = [
                            'batch_stock_id' => $batchStock->id,
                            'quantity' => $takeQuantity,
                        ];

                        $remainingQuantity -= $takeQuantity;
                    }

                    if ($remainingQuantity > 0.001) {
                        throw ValidationException::withMessages([
                            'items' => 'Stok tersedia untuk barang ' .
                                $product->name .
                                ' tidak mencukupi. Kekurangan stok: ' .
                                number_format($remainingQuantity, 3, ',', '.') .
                                ' ' . ($product->baseUnit?->symbol ?? '') . '.'
                        ]);
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | Simpan Detail Transaksi
                |--------------------------------------------------------------------------
                */

                $detail = StockTransactionDetail::create([
                    'stock_transaction_id' => $transaction->id,
                    'product_id' => $product->id,
                    'unit_id' => $item['unit_id'],
                    'quantity' => $quantity,
                    'base_quantity' => $baseQuantity,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                ]);

                /*
                |--------------------------------------------------------------------------
                | Simpan Alokasi Batch
                |--------------------------------------------------------------------------
                */

                foreach ($allocationsToSave as $allocation) {

                    StockTransactionBatchDetail::create([
                        'stock_transaction_detail_id' => $detail->id,
                        'source_batch_stock_id' => $allocation['batch_stock_id'],
                        'destination_batch_stock_id' => null,
                        'quantity' => $allocation['quantity'],
                    ]);
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Activity Log
            |--------------------------------------------------------------------------
            */

            ActivityLogger::log(
                'outbound_updated',
                'Memperbarui draft barang keluar dengan nomor transaksi ' .
                    $transaction->transaction_number,
                $transaction,
                [
                    'transaction_number' => $transaction->transaction_number,
                    'reason' => $transaction->reason,
                    'customer_id' => $transaction->customer_id,
                    'total_items' => count($validated['items']),
                    'status' => 'draft',
                ]
            );
        });

        return redirect()
            ->route('petugas.outbounds.show', $outbound->id)
            ->with(
                'success',
                'Draft barang keluar berhasil diperbarui.'
            );
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


    public function edit(StockTransaction $outbound)
    {
        abort_unless($outbound->transaction_type === 'outbound', 404);

        abort_unless(
            $outbound->status === 'draft',
            403,
            'Hanya transaksi Draft yang dapat diedit.'
        );

        $outbound->load([
            'customer',
            'details.product',
            'details.unit',
            'details.batchDetails.sourceBatchStock.productBatch',
            'details.batchDetails.sourceBatchStock.rackLocation',
        ]);

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

                'price_per_pcs' => (float) $product->price_per_pcs,
                'price_per_pack' => (float) $product->price_per_pack,

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
                        ->filter(fn ($stock) => (float) $stock->quantity > 0)
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

        return view('petugas.outbounds.edit', compact(
            'outbound',
            'products',
            'productData',
            'units',
            'customers'
        ));
    }
}
