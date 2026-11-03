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
        // Subquery untuk menghitung total stok setiap produk
        $stockSubquery = DB::table('product_batches')
            ->join('batch_stocks', 'product_batches.id', '=', 'batch_stocks.product_batch_id')
            ->whereColumn('product_batches.product_id', 'products.id')
            ->selectRaw('COALESCE(SUM(batch_stocks.quantity), 0)');

        // Mengambil produk beserta total stoknya
        $products = Product::query()
            ->where('is_active', true)
            ->select('products.*')
            ->selectSub($stockSubquery, 'total_stock')
            ->get();

        // Total produk aktif
        $totalProducts = $products->count();

        // Total seluruh stok
        $totalStock = $products->sum('total_stock');

        // Produk dengan stok menipis
        $lowStockProducts = $products
            ->filter(function ($product) {
                return $product->total_stock > 0
                    && $product->total_stock <= $product->minimum_stock;
            });

        // Produk dengan stok habis
        $outOfStockProducts = $products
            ->filter(function ($product) {
                return $product->total_stock <= 0;
            });

        // Pergerakan stok terbaru
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
            'totalStock',
            'lowStockProducts',
            'outOfStockProducts',
            'latestMovements'
        ));
    }
}