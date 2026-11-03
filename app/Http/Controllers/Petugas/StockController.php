<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::where('is_active', true)
            ->orderBy('name')
            ->get();

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
        ])->where('is_active', true);

        // Pencarian berdasarkan kode, nama produk, atau kode batch.
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
            'categories'
        ));
    }
}