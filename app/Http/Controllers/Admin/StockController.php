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
        /*
        |--------------------------------------------------------------------------
        | DATA KATEGORI
        |--------------------------------------------------------------------------
        */

        $categories = Category::where('is_active', true)
            ->orderBy('name')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | RINGKASAN STOK SELURUH PRODUK AKTIF
        |--------------------------------------------------------------------------
        */

        $stockSummary = Product::query()
            ->leftJoin(
                'product_batches',
                'products.id',
                '=',
                'product_batches.product_id'
            )
            ->leftJoin(
                'batch_stocks',
                'product_batches.id',
                '=',
                'batch_stocks.product_batch_id'
            )
            ->where('products.is_active', true)
            ->select(
                'products.id',
                'products.minimum_stock'
            )
            ->selectRaw(
                'COALESCE(SUM(batch_stocks.quantity), 0) as total_stock'
            )
            ->groupBy(
                'products.id',
                'products.minimum_stock'
            )
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


        /*
        |--------------------------------------------------------------------------
        | KESEHATAN INVENTORY
        |--------------------------------------------------------------------------
        */

        $inventoryHealth = $totalProducts > 0
            ? ($safeProducts / $totalProducts) * 100
            : 0;

        if ($totalProducts === 0) {

            $inventoryHealthStatus = 'Belum Ada Data';
            $inventoryHealthColor = 'yellow';

        } elseif ($inventoryHealth >= 80) {

            $inventoryHealthStatus = 'Sehat';
            $inventoryHealthColor = 'green';

        } elseif ($inventoryHealth >= 60) {

            $inventoryHealthStatus = 'Perlu Perhatian';
            $inventoryHealthColor = 'yellow';

        } else {

            $inventoryHealthStatus = 'Kritis';
            $inventoryHealthColor = 'red';
        }


        /*
        |--------------------------------------------------------------------------
        | MONITORING KEDALUWARSA
        |--------------------------------------------------------------------------
        */

        $today = Carbon::today();

        $expiryLimit = Carbon::today()->addDays(30);

        $expiryQuery = DB::table('product_batches as pb')
            ->join('products as p', 'p.id', '=', 'pb.product_id')
            ->join('batch_stocks as bs', 'bs.product_batch_id', '=', 'pb.id')
            ->where('p.is_active', true)
            ->where('bs.quantity', '>', 0)
            ->whereNotNull('pb.expiry_date')
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
            );


        // Batch yang sudah kedaluwarsa.

        $expiredBatches = (clone $expiryQuery)
            ->whereDate('pb.expiry_date', '<', $today->toDateString())
            ->orderBy('pb.expiry_date')
            ->get();


        // Batch yang kedaluwarsa hari ini sampai 30 hari ke depan.

        $expiringBatches = (clone $expiryQuery)
            ->whereBetween('pb.expiry_date', [
                $today->toDateString(),
                $expiryLimit->toDateString(),
            ])
            ->orderBy('pb.expiry_date')
            ->get();


        $totalExpiredBatches = $expiredBatches->count();

        $totalExpiringBatches = $expiringBatches->count();


        /*
        |--------------------------------------------------------------------------
        | DATA GRAFIK DISTRIBUSI STOK
        |--------------------------------------------------------------------------
        */

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


        /*
        |--------------------------------------------------------------------------
        | QUERY TABEL MONITORING
        |--------------------------------------------------------------------------
        */

        $stockSubquery = DB::table('product_batches as pb')
            ->leftJoin(
                'batch_stocks as bs',
                'pb.id',
                '=',
                'bs.product_batch_id'
            )
            ->whereColumn('pb.product_id', 'products.id')
            ->selectRaw(
                'COALESCE(SUM(bs.quantity), 0)'
            );


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


        /*
        |--------------------------------------------------------------------------
        | PENCARIAN PRODUK DAN BATCH
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {

            $search = trim($request->search);

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


        /*
        |--------------------------------------------------------------------------
        | FILTER KATEGORI
        |--------------------------------------------------------------------------
        */

        if ($request->filled('category_id')) {

            $query->where(
                'category_id',
                $request->category_id
            );
        }


        /*
        |--------------------------------------------------------------------------
        | PAGINATION
        |--------------------------------------------------------------------------
        */

        $products = $query
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();


        /*
        |--------------------------------------------------------------------------
        | KIRIM DATA KE VIEW
        |--------------------------------------------------------------------------
        */

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