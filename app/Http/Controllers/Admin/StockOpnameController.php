<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BatchStock;
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

        // Pencarian nomor Stock Opname
        if ($request->filled('search')) {
            $query->where(
                'opname_number',
                'like',
                '%' . $request->search . '%'
            );
        }

        // Filter status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Admin hanya melihat Stock Opname yang sudah diajukan
        // atau yang sudah selesai diproses.
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
            'details.batchStock.productBatch.product.baseUnit',
            'details.batchStock.rackLocation',
            'details.adjustmentRequests.requester',
            'details.adjustmentRequests.reviewer',
        ]);

        return view('admin.stock-opnames.show', compact(
            'stockOpname'
        ));
    }

    public function approveAdjustment(StockAdjustmentRequest $adjustmentRequest)
    {
        $result = DB::transaction(function () use ($adjustmentRequest) {

            // Kunci permintaan penyesuaian.
            $request = StockAdjustmentRequest::whereKey($adjustmentRequest->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($request->status !== 'pending') {
                throw ValidationException::withMessages([
                    'adjustment' => 'Permintaan ini sudah diproses sebelumnya.',
                ]);
            }

            // Ambil detail Stock Opname.
            $detail = $request->detail()
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

            // Kunci stok batch.
            $batchStock = BatchStock::whereKey($detail->batch_stock_id)
                ->lockForUpdate()
                ->firstOrFail();

            $systemQuantity = (float) $detail->system_quantity;
            $physicalQuantity = (float) $detail->physical_quantity;
            $difference = $physicalQuantity - $systemQuantity;

            // Pastikan stok tidak berubah sejak Stock Opname dibuat.
            if (abs((float) $batchStock->quantity - $systemQuantity) > 0.0005) {
                throw ValidationException::withMessages([
                    'adjustment' => 'Stok batch telah berubah sejak Stock Opname dibuat. Persetujuan dibatalkan untuk mencegah kesalahan penyesuaian.',
                ]);
            }

            if (abs($difference) < 0.0005) {
                throw ValidationException::withMessages([
                    'adjustment' => 'Tidak ada selisih stok yang perlu disesuaikan.',
                ]);
            }

            $balanceBefore = (float) $batchStock->quantity;
            $balanceAfter = $balanceBefore + $difference;

            if ($balanceAfter < 0) {
                throw ValidationException::withMessages([
                    'adjustment' => 'Penyesuaian tidak dapat dilakukan karena stok akan menjadi negatif.',
                ]);
            }

            // Perbarui stok.
            $batchStock->update([
                'quantity' => $balanceAfter,
            ]);

            // Tentukan tipe pergerakan stok.
            $movementType = $difference > 0
                ? 'adjustment_in'
                : 'adjustment_out';

            // Catat pergerakan stok.
            StockMovement::create([
                'batch_stock_id' => $batchStock->id,
                'movement_type' => $movementType,
                'quantity' => abs($difference),
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'reference_type' => StockAdjustmentRequest::class,
                'reference_id' => $request->id,
                'notes' => 'Penyesuaian stok berdasarkan persetujuan Stock Opname ' . $opname->opname_number,
                'created_by' => auth()->id(),
            ]);

            // Tandai permintaan telah disetujui.
            $request->update([
                'status' => 'approved',
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
                'approval_notes' => 'Permintaan penyesuaian stok disetujui.',
            ]);

            // Periksa apakah masih ada permintaan yang menunggu.
            $pendingRequests = StockAdjustmentRequest::whereHas(
                'detail',
                function ($query) use ($opname) {
                    $query->where('stock_opname_id', $opname->id);
                }
            )->where('status', 'pending')->exists();

            if (!$pendingRequests) {
                $opname->update([
                    'status' => 'approved',
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                ]);
            }

            return [
                'opname_id' => $opname->id,
                'opname_number' => $opname->opname_number,
                'adjustment_id' => $request->id,
                'product_batch_id' => $batchStock->product_batch_id,
                'rack_location_id' => $batchStock->rack_location_id,
                'system_quantity' => $systemQuantity,
                'physical_quantity' => $physicalQuantity,
                'difference' => $difference,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'movement_type' => $movementType,
            ];
        });

        ActivityLogger::log(
            'stock_adjustment_approved',
            'Admin menyetujui penyesuaian stok pada Stock Opname ' . $result['opname_number'],
            StockOpname::find($result['opname_id']),
            [
                'opname_number' => $result['opname_number'],
                'adjustment_request_id' => $result['adjustment_id'],
                'product_batch_id' => $result['product_batch_id'],
                'rack_location_id' => $result['rack_location_id'],
                'system_quantity' => $result['system_quantity'],
                'physical_quantity' => $result['physical_quantity'],
                'difference' => $result['difference'],
                'balance_before' => $result['balance_before'],
                'balance_after' => $result['balance_after'],
                'movement_type' => $result['movement_type'],
            ]
        );

        return redirect()
            ->route(
                'admin.stock-opnames.show',
                $result['opname_id']
            )
            ->with('success', 'Permintaan penyesuaian stok berhasil disetujui.');
    }

    public function rejectAdjustment(Request $request, StockAdjustmentRequest $adjustmentRequest)
    {
        $validated = $request->validate([
            'approval_notes' => ['required', 'string', 'max:1000'],
        ]);

        $result = DB::transaction(function () use ($adjustmentRequest, $validated) {

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

            $adjustment->update([
                'status' => 'rejected',
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
                'approval_notes' => $validated['approval_notes'],
            ]);

            // Periksa apakah masih ada permintaan yang menunggu.
            $pendingRequests = StockAdjustmentRequest::whereHas(
                'detail',
                function ($query) use ($opname) {
                    $query->where('stock_opname_id', $opname->id);
                }
            )->where('status', 'pending')->exists();

            if (!$pendingRequests) {
                $opname->update([
                    'status' => 'rejected',
                ]);
            }

            return [
                'opname_id' => $opname->id,
                'opname_number' => $opname->opname_number,
                'adjustment_id' => $adjustment->id,
                'approval_notes' => $adjustment->approval_notes,
                'system_quantity' => $detail->system_quantity,
                'physical_quantity' => $detail->physical_quantity,
                'difference' => $detail->difference_quantity,
            ];
        });

        ActivityLogger::log(
            'stock_adjustment_rejected',
            'Admin menolak penyesuaian stok pada Stock Opname ' . $result['opname_number'],
            StockOpname::find($result['opname_id']),
            [
                'opname_number' => $result['opname_number'],
                'adjustment_request_id' => $result['adjustment_id'],
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
}