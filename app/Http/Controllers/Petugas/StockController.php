<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    public function index(Request $request)
    {
        // Data kategori aktif untuk filter.
        $categories = Category::where('is_active', true)
            ->orderBy('name')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Ringkasan Stok Keseluruhan
        |--------------------------------------------------------------------------
        | Ringkasan dihitung untuk seluruh produk aktif,
        | bukan hanya produk yang tampil pada halaman pagination.
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

        $summary = DB::query()
            ->fromSub($productStocks, 'product_stocks')
            ->selectRaw('
                COUNT(*) as total_products,
                COALESCE(SUM(total_stock), 0) as total_stock,
                SUM(CASE WHEN total_stock <= 0 THEN 1 ELSE 0 END) as out_of_stock,
                SUM(CASE WHEN total_stock > 0 AND total_stock <= minimum_stock THEN 1 ELSE 0 END) as low_stock
            ')
            ->first();

        // Jumlah batch yang sudah atau akan kedaluwarsa dalam 30 hari.
        $expiringBatches = DB::table('product_batches as pb')
            ->join('batch_stocks as bs', 'bs.product_batch_id', '=', 'pb.id')
            ->join('products as p', 'p.id', '=', 'pb.product_id')
            ->where('p.is_active', true)
            ->where('bs.quantity', '>', 0)
            ->whereNotNull('pb.expiry_date')
            ->whereDate('pb.expiry_date', '<=', now()->addDays(30)->toDateString())
            ->count('pb.id');

        /*
        |--------------------------------------------------------------------------
        | Daftar Produk dan Detail Batch
        |--------------------------------------------------------------------------
        */

        $query = Product::with([
            'category',
            'baseUnit',
            'batches' => function ($query) {
                $query->orderByRaw('expiry_date IS NULL')
                    ->orderBy('expiry_date');
            },
            'batches.batchStocks' => function ($query) {
                $query->where('quantity', '>', 0)
                    ->with('rackLocation');
            },
        ])->where('is_active', true);

        // Pencarian kode produk, nama produk, atau kode batch.
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

        // Filter kategori.
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $products = $query
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('petugas.stocks.index', compact(
            'products',
            'categories',
            'summary',
            'expiringBatches'
        ));
    }
}