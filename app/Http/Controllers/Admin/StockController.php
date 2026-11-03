<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StockController extends Controller
{
    public function index(Request $request)
    {
        // ==========================================
        // DATA KATEGORI
        // ==========================================

        $categories = Category::where('is_active', true)
            ->orderBy('name')
            ->get();


        // ==========================================
        // RINGKASAN STOK DAN KESEHATAN INVENTORY
        // ==========================================

        $stockSummary = Product::query()
            ->where('is_active', true)
            ->leftJoin('product_batches', 'products.id', '=', 'product_batches.product_id')
            ->leftJoin('batch_stocks', 'product_batches.id', '=', 'batch_stocks.product_batch_id')
            ->select(
                'products.id',
                'products.minimum_stock'
            )
            ->selectRaw('COALESCE(SUM(batch_stocks.quantity), 0) as total_stock')
            ->groupBy('products.id', 'products.minimum_stock')
            ->get();

        $totalProducts = $stockSummary->count();

        $safeProducts = 0;
        $lowStockProducts = 0;
        $outOfStockProducts = 0;

        foreach ($stockSummary as $product) {
            $totalStock = (float) $product->total_stock;
            $minimumStock = (float) $product->minimum_stock;

            if ($totalStock <= 0) {
                $outOfStockProducts++;
            } elseif ($totalStock <= $minimumStock) {
                $lowStockProducts++;
            } else {
                $safeProducts++;
            }
        }


        // ==========================================
        // KESEHATAN INVENTORY
        // ==========================================

        $inventoryHealth = $totalProducts > 0
            ? ($safeProducts / $totalProducts) * 100
            : 0;

        if ($inventoryHealth >= 80) {
            $inventoryHealthStatus = 'Sehat';
            $inventoryHealthColor = 'green';
        } elseif ($inventoryHealth >= 60) {
            $inventoryHealthStatus = 'Perlu Perhatian';
            $inventoryHealthColor = 'yellow';
        } else {
            $inventoryHealthStatus = 'Kritis';
            $inventoryHealthColor = 'red';
        }


        // ==========================================
        // MONITORING KEDALUWARSA
        // ==========================================

        $today = Carbon::today();
        $expiryLimit = Carbon::today()->addDays(30);

        $expiryQuery = DB::table('product_batches')
            ->join('products', 'products.id', '=', 'product_batches.product_id')
            ->join('batch_stocks', 'batch_stocks.product_batch_id', '=', 'product_batches.id')
            ->where('products.is_active', true)
            ->where('batch_stocks.quantity', '>', 0)
            ->whereNotNull('product_batches.expiry_date')
            ->select(
                'product_batches.id',
                'product_batches.batch_code',
                'product_batches.expiry_date',
                'products.code as product_code',
                'products.name as product_name'
            )
            ->selectRaw('SUM(batch_stocks.quantity) as total_quantity')
            ->groupBy(
                'product_batches.id',
                'product_batches.batch_code',
                'product_batches.expiry_date',
                'products.code',
                'products.name'
            );

        // Batch yang sudah kedaluwarsa
        $expiredBatches = (clone $expiryQuery)
            ->whereDate('product_batches.expiry_date', '<', $today)
            ->orderBy('product_batches.expiry_date')
            ->get();

        // Batch yang segera kedaluwarsa
        $expiringBatches = (clone $expiryQuery)
            ->whereBetween('product_batches.expiry_date', [
                $today->toDateString(),
                $expiryLimit->toDateString(),
            ])
            ->orderBy('product_batches.expiry_date')
            ->get();

        $totalExpiredBatches = $expiredBatches->count();
        $totalExpiringBatches = $expiringBatches->count();


        // ==========================================
        // DATA GRAFIK STATUS STOK
        // ==========================================

        $stockChart = [
            'labels' => [
                'Aman',
                'Menipis',
                'Habis',
            ],
            'data' => [
                $safeProducts,
                $lowStockProducts,
                $outOfStockProducts,
            ],
        ];


        // ==========================================
        // QUERY TABEL MONITORING
        // ==========================================

        $stockSubquery = DB::table('product_batches')
            ->join(
                'batch_stocks',
                'product_batches.id',
                '=',
                'batch_stocks.product_batch_id'
            )
            ->selectRaw('COALESCE(SUM(batch_stocks.quantity), 0)')
            ->whereColumn('product_batches.product_id', 'products.id');

        $query = Product::with([
            'category',
            'baseUnit',
            'batches' => function ($query) {
                $query->orderBy('expiry_date');
            },
            'batches.batchStocks' => function ($query) {
                $query->where('quantity', '>', 0)
                    ->with('rackLocation');
            },
        ])
            ->where('is_active', true)
            ->select('products.*')
            ->selectSub($stockSubquery, 'total_stock');


        // Pencarian produk dan batch
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhereHas('batches', function ($batchQuery) use ($search) {
                        $batchQuery->where(
                            'batch_code',
                            'like',
                            "%{$search}%"
                        );
                    });
            });
        }


        // Filter kategori
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }


        $products = $query
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();


        return view('admin.stocks.index', compact(
            'products',
            'categories',
            'totalProducts',
            'safeProducts',
            'lowStockProducts',
            'outOfStockProducts',
            'inventoryHealth',
            'inventoryHealthStatus',
            'inventoryHealthColor',
            'totalExpiredBatches',
            'totalExpiringBatches',
            'expiredBatches',
            'expiringBatches',
            'stockChart'
        ));
    }
}