<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\BatchStock;
use App\Models\StockOpname;
use App\Models\StockOpnameDetail;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\StockAdjustmentRequest;

class StockOpnameController extends Controller
{
    /**
     * Menampilkan daftar Stock Opname.
     */
    public function index()
    {
        $opnames = StockOpname::with('creator')
            ->latest()
            ->paginate(10);

        return view('petugas.stock-opnames.index', compact('opnames'));
    }

    /**
     * Menampilkan form pembuatan Stock Opname.
     */
    public function create()
    {
        return view('petugas.stock-opnames.create');
    }

    /**
     * Menyimpan transaksi Stock Opname sebagai Draft.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'opname_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $opname = DB::transaction(function () use ($validated) {

            $batchStocks = BatchStock::where('quantity', '>', 0)
                ->lockForUpdate()
                ->get();

            if ($batchStocks->isEmpty()) {
                throw new \RuntimeException(
                    'Tidak ada stok barang yang tersedia untuk dilakukan Stock Opname.'
                );
            }

            $opname = StockOpname::create([
                'opname_number' => 'SO-' . now()->format('Ymd-His') . '-' . random_int(100, 999),
                'opname_date' => $validated['opname_date'],
                'status' => 'draft',
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            foreach ($batchStocks as $batchStock) {
                StockOpnameDetail::create([
                    'stock_opname_id' => $opname->id,
                    'batch_stock_id' => $batchStock->id,
                    'system_quantity' => $batchStock->quantity,
                    'physical_quantity' => 0,
                    'difference_quantity' => 0,
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

        $opname = DB::transaction(function () use ($validated, $stockOpname) {

            $opname = StockOpname::whereKey($stockOpname->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($opname->status !== 'draft') {
                throw new \RuntimeException(
                    'Hasil Stock Opname hanya dapat diubah selama status Draft.'
                );
            }

            foreach ($validated['items'] as $item) {
                $detail = StockOpnameDetail::where('stock_opname_id', $opname->id)
                    ->whereKey($item['id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                $physicalQuantity = (float) $item['physical_quantity'];
                $systemQuantity = (float) $detail->system_quantity;

                $detail->update([
                    'physical_quantity' => $physicalQuantity,
                    'difference_quantity' => $physicalQuantity - $systemQuantity,
                    'is_counted' => true,
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            return $opname;
        });

        ActivityLogger::log(
            'stock_opname_count_updated',
            'Petugas memperbarui hasil perhitungan fisik Stock Opname ' . $opname->opname_number,
            $opname,
            [
                'opname_number' => $opname->opname_number,
                'updated_items' => count($validated['items']),
                'status' => $opname->status,
            ]
        );

        return redirect()
            ->route('petugas.stock-opnames.show', $stockOpname->id)
            ->with('success', 'Hasil perhitungan fisik berhasil disimpan.');
    }

        public function submit(StockOpname $stockOpname)
        {
            $result = DB::transaction(function () use ($stockOpname) {

                $opname = StockOpname::whereKey($stockOpname->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($opname->status !== 'draft') {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'stock_opname' => 'Stock Opname ini sudah tidak dapat disubmit.',
                    ]);
                }

                $details = $opname->details()
                    ->lockForUpdate()
                    ->get();

                $this->validateStockSnapshot($opname);

                if ($details->isEmpty()) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'stock_opname' => 'Detail Stock Opname masih kosong.',
                    ]);
                }

                if ($details->contains(fn ($detail) => !$detail->is_counted)) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'stock_opname' => 'Semua barang harus dihitung terlebih dahulu.',
                    ]);
                }

                $differences = $details->filter(
                    fn ($detail) => (float) $detail->difference_quantity != 0
                );

                $differenceCount = $differences->count();

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
                    'difference_count' => $differenceCount,
                    'status' => 'submitted',
                ];
            });

            $opname = $result['opname'];

            $action = $result['status'] === 'completed'
                ? 'stock_opname_completed'
                : 'stock_opname_submitted';

            $description = $result['status'] === 'completed'
                ? 'Stock Opname ' . $opname->opname_number . ' selesai tanpa selisih stok.'
                : 'Petugas mengajukan Stock Opname ' . $opname->opname_number . ' untuk pemeriksaan Admin.';

            ActivityLogger::log(
                $action,
                $description,
                $opname,
                [
                    'opname_number' => $opname->opname_number,
                    'total_items' => $opname->details()->count(),
                    'items_with_difference' => $result['difference_count'],
                    'status' => $result['status'],
                ]
            );

            return redirect()
                ->route('petugas.stock-opnames.show', $stockOpname)
                ->with(
                    'success',
                    $result['status'] === 'completed'
                        ? 'Stock Opname berhasil diselesaikan tanpa selisih stok.'
                        : 'Stock Opname berhasil disubmit dan menunggu pemeriksaan Admin.'
                );
        }

        private function validateStockSnapshot(StockOpname $opname): void
        {
            $details = $opname->details()
                ->with('batchStock')
                ->get();

            $currentStocks = BatchStock::where('quantity', '>', 0)
                ->get()
                ->keyBy('id');

            // Pastikan jumlah batch yang tercatat masih sama.
            if ($details->count() !== $currentStocks->count()) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'stock_opname' => 'Data stok telah berubah sejak Stock Opname dibuat. Silakan buat Stock Opname baru.',
                ]);
            }

            // Pastikan setiap batch masih memiliki jumlah stok yang sama.
            foreach ($details as $detail) {
                $currentStock = $currentStocks->get($detail->batch_stock_id);

                if (
                    !$currentStock ||
                    (float) $currentStock->quantity !== (float) $detail->system_quantity
                ) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'stock_opname' => 'Stok salah satu batch telah berubah. Silakan buat Stock Opname baru.',
                    ]);
                }
            }
        }
}