<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\BatchStock;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\RackLocation;
use App\Models\StockMovement;
use App\Models\StockTransaction;
use App\Models\StockTransactionBatchDetail;
use App\Models\StockTransactionDetail;
use App\Models\Supplier;
use App\Models\Unit;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class InboundController extends Controller
{
    
    public function index(Request $request)
    {
        $search = trim($request->input('search', ''));

        $query = StockTransaction::with(['supplier', 'creator'])
            ->where('transaction_type', 'inbound')
            ->orderByRaw("CASE WHEN status = 'draft' THEN 0 ELSE 1 END")
            ->latest('transaction_date');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('transaction_number', 'like', "%{$search}%")
                    ->orWhere('supplier_invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('supplier', function ($supplierQuery) use ($search) {
                        $supplierQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $inbounds = $query->paginate(10)->withQueryString();

        return view('petugas.inbounds.index', compact('inbounds', 'search'));
    }

    public function create()
    {
        $suppliers = Supplier::where('is_active', true)
            ->orderBy('name')
            ->get();

        $products = Product::where('is_active', true)
            ->with(['baseUnit', 'unitConversions.unit'])
            ->orderBy('name')
            ->get();

        $units = Unit::orderBy('name')->get();

        $rackLocations = RackLocation::where('is_active', true)
            ->orderBy('code')
            ->get();

        return view('petugas.inbounds.create', compact(
            'suppliers',
            'products',
            'units',
            'rackLocations'
        ));
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'transaction_date' => ['required', 'date'],

            'supplier_id' => [
                'required',
                Rule::exists('suppliers', 'id')
                    ->where('is_active', true),
            ],

            'supplier_invoice_number' => [
                'required',
                'string',
                'max:100',
                Rule::unique('stock_transactions', 'supplier_invoice_number')
                    ->where(fn ($query) => $query->where('supplier_id', $request->supplier_id)),
            ],

            'notes' => ['nullable', 'string'],

            'items' => ['required', 'array', 'min:1'],

            'items.*.product_id' => [
                'required',
                Rule::exists('products', 'id')
                    ->where('is_active', true),
            ],

            'items.*.unit_id' => [
                'required',
                'exists:units,id',
            ],

            'items.*.quantity' => [
                'required',
                'numeric',
                'gt:0',
            ],

            'items.*.batch_code' => [
                'required',
                'string',
                'max:255',
            ],

            'items.*.expiry_date' => [
                'nullable',
                'date',
                'after_or_equal:transaction_date',
            ],

            'items.*.rack_location_id' => [
                'required',
                Rule::exists('rack_locations', 'id')
                    ->where('is_active', true),
            ],
        ], [
            'transaction_date.required' => 'Tanggal barang masuk wajib diisi.',

            'supplier_id.required' => 'Supplier wajib dipilih.',
            'supplier_id.exists' => 'Supplier tidak ditemukan atau sudah nonaktif.',

            'supplier_invoice_number.required' => 'Nomor faktur supplier wajib diisi.',
            'supplier_invoice_number.unique' => 'Nomor faktur tersebut sudah digunakan oleh supplier ini.',

            'items.required' => 'Minimal harus ada satu produk.',
            'items.min' => 'Minimal harus ada satu produk.',

            'items.*.product_id.required' => 'Produk wajib dipilih.',
            'items.*.product_id.exists' => 'Produk tidak ditemukan atau sudah nonaktif.',

            'items.*.unit_id.required' => 'Satuan wajib dipilih.',
            'items.*.unit_id.exists' => 'Satuan tidak ditemukan.',

            'items.*.quantity.required' => 'Jumlah barang wajib diisi.',
            'items.*.quantity.gt' => 'Jumlah barang harus lebih dari 0.',

            'items.*.batch_code.required' => 'Kode batch wajib diisi.',

            'items.*.expiry_date.after_or_equal' => 'Tanggal kedaluwarsa tidak boleh lebih awal dari tanggal penerimaan.',

            'items.*.rack_location_id.required' => 'Lokasi rak wajib dipilih.',
            'items.*.rack_location_id.exists' => 'Lokasi rak tidak ditemukan atau sudah nonaktif.',
        ]);

        DB::transaction(function () use ($validated) {
            $transaction = StockTransaction::create([
                'transaction_number' => 'IN-' . now()->format('YmdHis') . '-' . strtoupper(Str::random(4)),
                'transaction_type' => 'inbound',
                'transaction_date' => $validated['transaction_date'],
                'supplier_id' => $validated['supplier_id'],
                'supplier_invoice_number' => $validated['supplier_invoice_number'],
                'customer_id' => null,
                'reason' => 'Penerimaan dari supplier',
                'status' => 'draft',
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            $this->createInboundDetails($transaction, $validated);

            ActivityLogger::log(
                'inbound_created',
                'Membuat draft barang masuk dengan nomor transaksi ' . $transaction->transaction_number,
                $transaction,
                [
                    'transaction_number' => $transaction->transaction_number,
                    'supplier_invoice_number' => $transaction->supplier_invoice_number,
                    'supplier_id' => $transaction->supplier_id,
                    'total_items' => count($validated['items']),
                    'status' => 'draft',
                ]
            );
        });

        return redirect()
            ->route('petugas.inbounds.index')
            ->with('success', 'Draft barang masuk berhasil disimpan. Stok belum bertambah.');
    }

    public function show(StockTransaction $inbound)
    {
        abort_unless($inbound->transaction_type === 'inbound', 404);

        $inbound->load([
            'supplier',
            'creator',
            'details.product',
            'details.unit',
            'details.batchDetails.destinationBatchStock.productBatch',
            'details.batchDetails.destinationBatchStock.rackLocation',
        ]);

        return view('petugas.inbounds.show', compact('inbound'));
    }

    public function edit(StockTransaction $inbound)
    {
        abort_unless($inbound->transaction_type === 'inbound', 404);

        if ($inbound->status !== 'draft') {
            return redirect()
                ->route('petugas.inbounds.index')
                ->with('error', 'Hanya transaksi draft yang dapat diedit.');
        }

        $inbound->load([
            'details.product',
            'details.unit',
            'details.batchDetails.destinationBatchStock.productBatch',
            'details.batchDetails.destinationBatchStock.rackLocation',
        ]);

        $suppliers = Supplier::where('is_active', true)
            ->orderBy('name')
            ->get();

        $products = Product::where('is_active', true)
            ->with(['baseUnit', 'unitConversions.unit'])
            ->orderBy('name')
            ->get();

        $units = Unit::orderBy('name')->get();

        $rackLocations = RackLocation::where('is_active', true)
            ->orderBy('code')
            ->get();

        return view('petugas.inbounds.edit', compact(
            'inbound',
            'suppliers',
            'products',
            'units',
            'rackLocations'
        ));
    }


    public function update(Request $request, StockTransaction $inbound)
    {
        abort_unless($inbound->transaction_type === 'inbound', 404);

        if ($inbound->status !== 'draft') {
            return redirect()
                ->route('petugas.inbounds.index')
                ->with('error', 'Hanya transaksi draft yang dapat diedit.');
        }

        $validated = $request->validate([
            'transaction_date' => ['required', 'date'],

            'supplier_id' => [
                'required',
                Rule::exists('suppliers', 'id')
                    ->where('is_active', true),
            ],

            'supplier_invoice_number' => [
                'required',
                'string',
                'max:100',
                Rule::unique('stock_transactions', 'supplier_invoice_number')
                    ->where(fn ($query) => $query->where('supplier_id', $request->supplier_id))
                    ->ignore($inbound->id),
            ],

            'notes' => ['nullable', 'string'],

            'items' => ['required', 'array', 'min:1'],

            'items.*.product_id' => [
                'required',
                Rule::exists('products', 'id')
                    ->where('is_active', true),
            ],

            'items.*.unit_id' => [
                'required',
                'exists:units,id',
            ],

            'items.*.quantity' => [
                'required',
                'numeric',
                'gt:0',
            ],

            'items.*.batch_code' => [
                'required',
                'string',
                'max:255',
            ],

            'items.*.expiry_date' => [
                'nullable',
                'date',
                'after_or_equal:transaction_date',
            ],

            'items.*.rack_location_id' => [
                'required',
                Rule::exists('rack_locations', 'id')
                    ->where('is_active', true),
            ],
        ], [
            'transaction_date.required' => 'Tanggal barang masuk wajib diisi.',

            'supplier_id.required' => 'Supplier wajib dipilih.',
            'supplier_id.exists' => 'Supplier tidak ditemukan atau sudah nonaktif.',

            'supplier_invoice_number.required' => 'Nomor faktur supplier wajib diisi.',
            'supplier_invoice_number.unique' => 'Nomor faktur tersebut sudah digunakan oleh supplier ini.',

            'items.required' => 'Minimal harus ada satu produk.',
            'items.min' => 'Minimal harus ada satu produk.',

            'items.*.product_id.required' => 'Produk wajib dipilih.',
            'items.*.product_id.exists' => 'Produk tidak ditemukan atau sudah nonaktif.',

            'items.*.unit_id.required' => 'Satuan wajib dipilih.',
            'items.*.unit_id.exists' => 'Satuan tidak ditemukan.',

            'items.*.quantity.required' => 'Jumlah barang wajib diisi.',
            'items.*.quantity.gt' => 'Jumlah barang harus lebih dari 0.',

            'items.*.batch_code.required' => 'Kode batch wajib diisi.',

            'items.*.expiry_date.after_or_equal' => 'Tanggal kedaluwarsa tidak boleh lebih awal dari tanggal penerimaan.',

            'items.*.rack_location_id.required' => 'Lokasi rak wajib dipilih.',
            'items.*.rack_location_id.exists' => 'Lokasi rak tidak ditemukan atau sudah nonaktif.',
        ]);

        DB::transaction(function () use ($validated, $inbound) {
            $transaction = StockTransaction::whereKey($inbound->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($transaction->status !== 'draft') {
                throw ValidationException::withMessages([
                    'transaction' => 'Transaksi sudah diposting dan tidak dapat diedit.',
                ]);
            }

            $transaction->update([
                'transaction_date' => $validated['transaction_date'],
                'supplier_id' => $validated['supplier_id'],
                'supplier_invoice_number' => $validated['supplier_invoice_number'],
                'notes' => $validated['notes'] ?? null,
            ]);

            // Hapus detail lama. Detail batch ikut terhapus melalui cascade.
            $transaction->details()->delete();

            // Buat ulang detail berdasarkan input terbaru.
            $this->createInboundDetails($transaction, $validated);

            ActivityLogger::log(
                'inbound_updated',
                'Memperbarui draft barang masuk dengan nomor transaksi ' . $transaction->transaction_number,
                $transaction,
                [
                    'transaction_number' => $transaction->transaction_number,
                    'supplier_invoice_number' => $transaction->supplier_invoice_number,
                    'supplier_id' => $transaction->supplier_id,
                    'total_items' => count($validated['items']),
                    'status' => 'draft',
                ]
            );
        });

        return redirect()
            ->route('petugas.inbounds.index')
            ->with('success', 'Draft barang masuk berhasil diperbarui.');
    }

    public function destroy(StockTransaction $inbound)
    {
        abort_unless($inbound->transaction_type === 'inbound', 404);

        if ($inbound->status !== 'draft') {
            return redirect()
                ->route('petugas.inbounds.index')
                ->with('error', 'Hanya transaksi draft yang dapat dihapus.');
        }

        DB::transaction(function () use ($inbound) {
            $transaction = StockTransaction::whereKey($inbound->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($transaction->status !== 'draft') {
                throw ValidationException::withMessages([
                    'transaction' => 'Transaksi sudah diposting dan tidak dapat dihapus.',
                ]);
            }

            // Catat aktivitas sebelum transaksi dihapus.
            ActivityLogger::log(
                'inbound_deleted',
                'Menghapus draft barang masuk dengan nomor transaksi ' . $transaction->transaction_number,
                $transaction,
                [
                    'transaction_number' => $transaction->transaction_number,
                    'supplier_invoice_number' => $transaction->supplier_invoice_number,
                    'supplier_id' => $transaction->supplier_id,
                    'status' => 'draft',
                ]
            );

            // Hapus transaksi beserta detail dan detail batch.
            $transaction->delete();
        });

        return redirect()
            ->route('petugas.inbounds.index')
            ->with('success', 'Draft barang masuk berhasil dihapus.');
    }

    public function post(StockTransaction $inbound)
    {
        if ($inbound->transaction_type !== 'inbound') {
            abort(404);
        }

        DB::transaction(function () use ($inbound) {
            // Kunci transaksi agar tidak diposting dua kali.
            $transaction = StockTransaction::whereKey($inbound->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($transaction->transaction_type !== 'inbound') {
                abort(404);
            }

            if ($transaction->status !== 'draft') {
                throw ValidationException::withMessages([
                    'transaction' => 'Hanya transaksi draft yang dapat diposting.',
                ]);
            }

            $details = StockTransactionDetail::where(
                'stock_transaction_id',
                $transaction->id
            )->with('batchDetails')->get();

            if ($details->isEmpty()) {
                throw ValidationException::withMessages([
                    'transaction' => 'Transaksi tidak memiliki detail barang.',
                ]);
            }

            foreach ($details as $detail) {
                foreach ($detail->batchDetails as $batchDetail) {
                    if (!$batchDetail->destination_batch_stock_id) {
                        throw ValidationException::withMessages([
                            'transaction' => 'Lokasi stok tujuan tidak ditemukan.',
                        ]);
                    }

                    $batchStock = BatchStock::whereKey(
                        $batchDetail->destination_batch_stock_id
                    )->lockForUpdate()->firstOrFail();

                    $before = (float) $batchStock->quantity;
                    $quantity = (float) $batchDetail->quantity;
                    $after = $before + $quantity;

                    $batchStock->update([
                        'quantity' => $after,
                    ]);

                    StockMovement::create([
                        'batch_stock_id' => $batchStock->id,
                        'movement_type' => 'in',
                        'quantity' => $quantity,
                        'balance_before' => $before,
                        'balance_after' => $after,
                        'reference_type' => StockTransaction::class,
                        'reference_id' => $transaction->id,
                        'notes' => 'Barang masuk: ' . $transaction->transaction_number,
                        'created_by' => auth()->id(),
                    ]);
                }
            }

            // Ubah status transaksi.
            $transaction->update([
                'status' => 'posted',
            ]);

            // Catat aktivitas dalam transaksi yang sama.
            ActivityLogger::log(
                'inbound_posted',
                'Melakukan posting barang masuk dengan nomor transaksi ' . $transaction->transaction_number,
                $transaction,
                [
                    'transaction_number' => $transaction->transaction_number,
                    'supplier_id' => $transaction->supplier_id,
                    'total_items' => $details->count(),
                    'status' => 'posted',
                ]
            );
        });

        return redirect()
            ->route('petugas.inbounds.index')
            ->with('success', 'Barang masuk berhasil diposting dan stok telah diperbarui.');
    }

    private function createInboundDetails(
    StockTransaction $transaction,
    array $validated
    ): void {
        foreach ($validated['items'] as $item) {
            $product = Product::with('unitConversions')
                ->findOrFail($item['product_id']);

            $unit = Unit::findOrFail($item['unit_id']);

            // Validasi satuan dasar atau satuan konversi produk.
            if ((int) $product->base_unit_id === (int) $unit->id) {
                $conversionFactor = 1;
            } else {
                $conversion = $product->unitConversions
                    ->firstWhere('unit_id', $unit->id);

                if (!$conversion) {
                    throw ValidationException::withMessages([
                        'items' => "Satuan {$unit->name} tidak terdaftar untuk produk {$product->name}.",
                    ]);
                }

                $conversionFactor = (float) $conversion->conversion_factor;
            }

            $baseQuantity = round(
                (float) $item['quantity'] * $conversionFactor,
                3
            );

            $detail = StockTransactionDetail::create([
                'stock_transaction_id' => $transaction->id,
                'product_id' => $product->id,
                'unit_id' => $unit->id,
                'quantity' => $item['quantity'],
                'base_quantity' => $baseQuantity,
            ]);

            $batch = ProductBatch::firstOrCreate(
                [
                    'product_id' => $product->id,
                    'batch_code' => $item['batch_code'],
                ],
                [
                    'received_date' => $validated['transaction_date'],
                    'expiry_date' => $item['expiry_date'] ?? null,
                ]
            );

            if (
                $batch->expiry_date?->format('Y-m-d') !==
                ($item['expiry_date'] ?? null)
            ) {
                throw ValidationException::withMessages([
                    'items' => "Tanggal kedaluwarsa batch {$item['batch_code']} tidak sesuai dengan data batch yang sudah ada.",
                ]);
            }

            $rack = RackLocation::where('is_active', true)
                ->findOrFail($item['rack_location_id']);

            $batchStock = BatchStock::firstOrCreate(
                [
                    'product_batch_id' => $batch->id,
                    'rack_location_id' => $rack->id,
                ],
                [
                    'quantity' => 0,
                ]
            );

            StockTransactionBatchDetail::create([
                'stock_transaction_detail_id' => $detail->id,
                'source_batch_stock_id' => null,
                'destination_batch_stock_id' => $batchStock->id,
                'quantity' => $baseQuantity,
            ]);
        }
    } 
}