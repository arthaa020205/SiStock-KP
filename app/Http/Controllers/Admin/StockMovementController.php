<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use App\Services\ActivityLogger;
use App\Exports\StockMovementExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class StockMovementController extends Controller
{
    public function index(Request $request)
    {
        $query = StockMovement::with([
            'batchStock.productBatch.product.baseUnit',
            'batchStock.rackLocation',
            'creator',
        ]);

        // Pencarian produk dan kode batch
        if ($request->filled('search')) {
            $search = $request->search;

            $query->whereHas('batchStock.productBatch', function ($batchQuery) use ($search) {
                $batchQuery->where('batch_code', 'like', "%{$search}%")
                    ->orWhereHas('product', function ($productQuery) use ($search) {
                        $productQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('code', 'like', "%{$search}%");
                    });
            });
        }

        // Filter jenis pergerakan stok
        if ($request->filled('movement_type')) {
            $query->where('movement_type', $request->movement_type);
        }

        // Filter tanggal mulai
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        // Filter tanggal akhir
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $stockMovements = $query
            ->latest('created_at')
            ->paginate(10)
            ->withQueryString();

        return view('admin.stock-movements.index', compact('stockMovements'));
    }

    public function exportExcel(Request $request)
    {
        $filters = $request->only([
            'search',
            'movement_type',
            'date_from',
            'date_to',
        ]);

        $fileName = 'riwayat-pergerakan-stok-' . now()->format('Y-m-d-His') . '.xlsx';

        $response = Excel::download(
            new StockMovementExport($filters),
            $fileName
        );

        ActivityLogger::log(
            'stock_movements_exported_excel',
            'Admin mengekspor riwayat pergerakan stok ke Excel.',
            null,
            [
                'file_name' => $fileName,
                'filters' => $filters,
                'exported_at' => now()->toDateTimeString(),
            ]
        );

        return $response;
    }

    public function exportPdf(Request $request)
    {
        $query = StockMovement::with([
            'batchStock.productBatch.product.baseUnit',
            'batchStock.rackLocation',
            'creator',
        ]);

        if ($request->filled('search')) {
            $search = $request->search;

            $query->whereHas('batchStock.productBatch', function ($batchQuery) use ($search) {
                $batchQuery->where('batch_code', 'like', "%{$search}%")
                    ->orWhereHas('product', function ($productQuery) use ($search) {
                        $productQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('code', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('movement_type')) {
            $query->where('movement_type', $request->movement_type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $stockMovements = $query
            ->latest('created_at')
            ->get();

        $filters = $request->only([
            'search',
            'movement_type',
            'date_from',
            'date_to',
        ]);

        $fileName = 'riwayat-pergerakan-stok-' . now()->format('Y-m-d-His') . '.pdf';

        $pdf = Pdf::loadView(
            'admin.stock-movements.export-pdf',
            compact('stockMovements')
        )->setPaper('a4', 'landscape');

        $response = $pdf->download($fileName);

        ActivityLogger::log(
            'stock_movements_exported_pdf',
            'Admin mengekspor riwayat pergerakan stok ke PDF.',
            null,
            [
                'file_name' => $fileName,
                'total_records' => $stockMovements->count(),
                'filters' => $filters,
                'exported_at' => now()->toDateTimeString(),
            ]
        );

        return $response;
    }
}