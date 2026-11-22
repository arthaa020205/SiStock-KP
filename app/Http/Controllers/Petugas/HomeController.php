<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\StockMovement;
use App\Models\StockOpname;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index()
    {
        /*
        |--------------------------------------------------------------------------
        | Ringkasan Produk dan Stok
        |--------------------------------------------------------------------------
        */

        $productStocks = DB::table('products as p')
            ->leftJoin('product_batches as pb', 'pb.product_id', '=', 'p.id')
            ->leftJoin('batch_stocks as bs', 'bs.product_batch_id', '=', 'pb.id')
            ->where('p.is_active', true)
            ->groupBy('p.id', 'p.minimum_stock')
            ->selectRaw('
                p.id,
                p.minimum_stock,
                COALESCE(SUM(bs.quantity), 0) as total_stock
            ');

        $stockSummary = DB::query()
            ->fromSub($productStocks, 'product_stocks')
            ->selectRaw('
                COUNT(*) as total_products,
                COALESCE(SUM(total_stock), 0) as total_stock,
                SUM(CASE WHEN total_stock <= 0 THEN 1 ELSE 0 END) as out_of_stock,
                SUM(
                    CASE
                        WHEN total_stock > 0
                        AND total_stock <= minimum_stock
                        THEN 1
                        ELSE 0
                    END
                ) as low_stock
            ')
            ->first();

        /*
        |--------------------------------------------------------------------------
        | Produk yang Membutuhkan Perhatian
        |--------------------------------------------------------------------------
        */

        $lowStockProducts = DB::table('products as p')
            ->leftJoin('product_batches as pb', 'pb.product_id', '=', 'p.id')
            ->leftJoin('batch_stocks as bs', 'bs.product_batch_id', '=', 'pb.id')
            ->where('p.is_active', true)
            ->groupBy('p.id', 'p.code', 'p.name', 'p.minimum_stock')
            ->havingRaw('COALESCE(SUM(bs.quantity), 0) <= p.minimum_stock')
            ->selectRaw('
                p.id,
                p.code,
                p.name,
                p.minimum_stock,
                COALESCE(SUM(bs.quantity), 0) as total_stock
            ')
            ->orderBy('total_stock')
            ->limit(5)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Monitoring Kedaluwarsa
        |--------------------------------------------------------------------------
        */

        $expiringQuery = DB::table('product_batches as pb')
            ->join('products as p', 'p.id', '=', 'pb.product_id')
            ->join('batch_stocks as bs', 'bs.product_batch_id', '=', 'pb.id')
            ->where('p.is_active', true)
            ->where('bs.quantity', '>', 0)
            ->whereNotNull('pb.expiry_date')
            ->whereDate('pb.expiry_date', '<=', now()->addDays(30)->toDateString());

        $expiringBatchCount = (clone $expiringQuery)
            ->distinct()
            ->count('pb.id');

        $expiringBatches = (clone $expiringQuery)
            ->select(
                'pb.id',
                'pb.batch_code',
                'pb.expiry_date',
                'p.code as product_code',
                'p.name as product_name'
            )
            ->selectRaw('SUM(bs.quantity) as total_quantity')
            ->groupBy(
                'pb.id',
                'pb.batch_code',
                'pb.expiry_date',
                'p.code',
                'p.name'
            )
            ->orderBy('pb.expiry_date')
            ->limit(5)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Aktivitas Pergerakan Stok Hari Ini
        |--------------------------------------------------------------------------
        */

        $todayMovements = StockMovement::query()
            ->whereDate('created_at', today())
            ->selectRaw("
                COALESCE(SUM(
                    CASE
                        WHEN movement_type IN (
                            'in',
                            'transfer_in',
                            'adjustment_in'
                        )
                        THEN quantity
                        ELSE 0
                    END
                ), 0) as total_in
            ")
            ->selectRaw("
                COALESCE(SUM(
                    CASE
                        WHEN movement_type IN (
                            'out',
                            'transfer_out',
                            'adjustment_out'
                        )
                        THEN quantity
                        ELSE 0
                    END
                ), 0) as total_out
            ")
            ->first();

        /*
        |--------------------------------------------------------------------------
        | Status Stock Opname
        |--------------------------------------------------------------------------
        */

        $stockOpnameSummary = [
            'draft' => StockOpname::where('status', 'draft')->count(),
            'submitted' => StockOpname::where('status', 'submitted')->count(),
            'completed' => StockOpname::where('status', 'completed')->count(),
        ];

        /*
        |--------------------------------------------------------------------------
        | Tampilkan Dashboard Petugas
        |--------------------------------------------------------------------------
        */

        return view('petugas.home', compact(
            'stockSummary',
            'lowStockProducts',
            'expiringBatches',
            'expiringBatchCount',
            'todayMovements',
            'stockOpnameSummary'
        ));
    }
}