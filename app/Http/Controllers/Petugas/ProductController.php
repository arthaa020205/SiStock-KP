<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with(['category', 'baseUnit'])
            ->latest()
            ->get();

        return view('petugas.products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::where('is_active', true)
            ->orderBy('name')
            ->get();

        $units = Unit::orderBy('name')->get();

        return view('petugas.products.create', compact('categories', 'units'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:products,code'],
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'base_unit_id' => ['required', 'exists:units,id'],
            'minimum_stock' => ['required', 'numeric', 'min:0'],

            'price_per_pcs' => ['required', 'numeric', 'min:0'],
            'price_per_pack' => ['required', 'numeric', 'min:0'],

            'description' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
        ], [
            'code.required' => 'Kode produk wajib diisi.',
            'code.unique' => 'Kode produk sudah digunakan.',
            'name.required' => 'Nama produk wajib diisi.',
            'category_id.required' => 'Kategori wajib dipilih.',
            'category_id.exists' => 'Kategori yang dipilih tidak valid.',
            'base_unit_id.required' => 'Satuan dasar wajib dipilih.',
            'base_unit_id.exists' => 'Satuan yang dipilih tidak valid.',
            'minimum_stock.required' => 'Stok minimum wajib diisi.',
            'minimum_stock.numeric' => 'Stok minimum harus berupa angka.',
            'minimum_stock.min' => 'Stok minimum tidak boleh kurang dari 0.',

            'price_per_pcs.required' => 'Harga jual per Pcs wajib diisi.',
            'price_per_pcs.numeric' => 'Harga jual per Pcs harus berupa angka.',
            'price_per_pcs.min' => 'Harga jual per Pcs tidak boleh negatif.',

            'price_per_pack.required' => 'Harga jual per Pack wajib diisi.',
            'price_per_pack.numeric' => 'Harga jual per Pack harus berupa angka.',
            'price_per_pack.min' => 'Harga jual per Pack tidak boleh negatif.',

            'is_active.required' => 'Status produk wajib dipilih.',
        ]);

        $product = Product::create($validated);

        ActivityLogger::log(
            'product_created',
            'Menambahkan produk: ' . $product->name,
            $product,
            [
                'code' => $product->code,
                'name' => $product->name,
                'category_id' => $product->category_id,
                'base_unit_id' => $product->base_unit_id,
                'minimum_stock' => $product->minimum_stock,
                'price_per_pcs' => $product->price_per_pcs,
                'price_per_pack' => $product->price_per_pack,
                'is_active' => $product->is_active,
            ]
        );

        return redirect()
            ->route('petugas.products.index')
            ->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit(Product $product)
    {
        $categories = Category::orderBy('name')->get();
        $units = Unit::orderBy('name')->get();

        return view('petugas.products.edit', compact(
            'product',
            'categories',
            'units'
        ));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('products', 'code')->ignore($product->id),
            ],
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'base_unit_id' => ['required', 'exists:units,id'],
            'minimum_stock' => ['required', 'numeric', 'min:0'],

            'price_per_pcs' => ['required', 'numeric', 'min:0'],
            'price_per_pack' => ['required', 'numeric', 'min:0'],

            'description' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
        ], [
            'code.required' => 'Kode produk wajib diisi.',
            'code.unique' => 'Kode produk sudah digunakan.',
            'name.required' => 'Nama produk wajib diisi.',
            'category_id.required' => 'Kategori wajib dipilih.',
            'category_id.exists' => 'Kategori yang dipilih tidak valid.',
            'base_unit_id.required' => 'Satuan dasar wajib dipilih.',
            'base_unit_id.exists' => 'Satuan yang dipilih tidak valid.',
            'minimum_stock.required' => 'Stok minimum wajib diisi.',
            'minimum_stock.numeric' => 'Stok minimum harus berupa angka.',
            'minimum_stock.min' => 'Stok minimum tidak boleh kurang dari 0.',

            'price_per_pcs.required' => 'Harga jual per Pcs wajib diisi.',
            'price_per_pcs.numeric' => 'Harga jual per Pcs harus berupa angka.',
            'price_per_pcs.min' => 'Harga jual per Pcs tidak boleh negatif.',

            'price_per_pack.required' => 'Harga jual per Pack wajib diisi.',
            'price_per_pack.numeric' => 'Harga jual per Pack harus berupa angka.',
            'price_per_pack.min' => 'Harga jual per Pack tidak boleh negatif.',

            'is_active.required' => 'Status produk wajib dipilih.',
        ]);

        $oldData = $product->only([
            'code',
            'name',
            'category_id',
            'base_unit_id',
            'minimum_stock',
            'price_per_pcs',
            'price_per_pack',
            'description',
            'is_active',
        ]);

        $product->update($validated);

        ActivityLogger::log(
            'product_updated',
            'Memperbarui produk: ' . $product->name,
            $product,
            [
                'old' => $oldData,
                'new' => $product->only([
                    'code',
                    'name',
                    'category_id',
                    'base_unit_id',
                    'minimum_stock',
                    'price_per_pcs',
                    'price_per_pack',
                    'description',
                    'is_active',
                ]),
            ]
        );

        return redirect()
            ->route('petugas.products.index')
            ->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product)
    {
        if (
            $product->batches()->exists() ||
            $product->unitConversions()->exists()
        ) {
            return redirect()
                ->route('petugas.products.index')
                ->with('error', 'Produk tidak dapat dihapus karena sudah memiliki batch stok atau konversi satuan.');
        }

        // Simpan informasi produk sebelum dihapus
        $productData = [
            'id' => $product->id,
            'code' => $product->code,
            'name' => $product->name,
            'category_id' => $product->category_id,
            'base_unit_id' => $product->base_unit_id,
            'minimum_stock' => $product->minimum_stock,
            'description' => $product->description,
            'is_active' => $product->is_active,
        ];

        $productName = $product->name;
        $productId = $product->id;

        $product->delete();

        ActivityLogger::log(
            'product_deleted',
            'Menghapus produk: ' . $productName,
            null,
            $productData + [
                'deleted_product_id' => $productId,
            ]
        );

        return redirect()
            ->route('petugas.products.index')
            ->with('success', 'Produk berhasil dihapus.');
    }
}