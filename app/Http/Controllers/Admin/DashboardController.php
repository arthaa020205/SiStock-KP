<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        /*
        |--------------------------------------------------------------------------
        | Ringkasan Stok Produk
        |--------------------------------------------------------------------------
        */

        // Menghitung stok berdasarkan produk.
        // LEFT JOIN memastikan produk tanpa batch tetap terhitung.
        $stockByProduct = DB::table('product_batches as pb')
            ->leftJoin('batch_stocks as bs', 'bs.product_batch_id', '=', 'pb.id')
            ->groupBy('pb.product_id')
            ->selectRaw('
                pb.product_id,
                COALESCE(SUM(bs.quantity), 0) as total_stock
            ');

        $products = Product::query()
            ->leftJoinSub(
                $stockByProduct,
                'product_stock_totals',
                function ($join) {
                    $join->on(
                        'products.id',
                        '=',
                        'product_stock_totals.product_id'
                    );
                }
            )
            ->where('products.is_active', true)
            ->with('baseUnit')
            ->select('products.*')
            ->selectRaw('
                COALESCE(product_stock_totals.total_stock, 0) as total_stock
            ')
            ->orderBy('products.name')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Statistik Dashboard
        |--------------------------------------------------------------------------
        */

        $totalProducts = $products->count();

        // Menghitung batch yang masih memiliki stok.
        $totalActiveBatches = DB::table('product_batches as pb')
            ->join('batch_stocks as bs', 'bs.product_batch_id', '=', 'pb.id')
            ->join('products as p', 'p.id', '=', 'pb.product_id')
            ->where('p.is_active', true)
            ->where('bs.quantity', '>', 0)
            ->distinct()
            ->count('pb.id');

        // Produk dengan stok menipis.
        $lowStockProducts = $products->filter(function ($product) {
            return (float) $product->total_stock > 0
                && (float) $product->total_stock <= (float) $product->minimum_stock;
        })->values();

        // Produk dengan stok habis.
        $outOfStockProducts = $products->filter(function ($product) {
            return (float) $product->total_stock <= 0;
        })->values();

        /*
        |--------------------------------------------------------------------------
        | Riwayat Pergerakan Stok
        |--------------------------------------------------------------------------
        */

        $latestMovements = StockMovement::with([
            'batchStock.productBatch.product',
            'batchStock.rackLocation',
            'creator',
        ])
            ->latest()
            ->take(10)
            ->get();

        return view('admin.dashboard', compact(
            'totalProducts',
            'totalActiveBatches',
            'lowStockProducts',
            'outOfStockProducts',
            'latestMovements'
        ));
    }
}