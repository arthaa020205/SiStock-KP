<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductUnitConversion;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductUnitConversionController extends Controller
{
    public function index()
    {
        $conversions = ProductUnitConversion::with([
            'product.baseUnit',
            'unit'
        ])->latest()->get();

        return view('petugas.unit-conversions.index', compact('conversions'));
    }

    public function create()
    {
        $products = Product::where('is_active', true)
            ->with('baseUnit')
            ->orderBy('name')
            ->get();

        $units = Unit::orderBy('name')->get();

        return view('petugas.unit-conversions.create', compact(
            'products',
            'units'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'unit_id' => [
                'required',
                'exists:units,id',
                Rule::unique('product_unit_conversions', 'unit_id')
                    ->where('product_id', $request->product_id),
            ],
            'conversion_factor' => ['required', 'numeric', 'gt:0'],
        ], [
            'product_id.required' => 'Produk wajib dipilih.',
            'product_id.exists' => 'Produk yang dipilih tidak valid.',
            'unit_id.required' => 'Satuan konversi wajib dipilih.',
            'unit_id.exists' => 'Satuan yang dipilih tidak valid.',
            'unit_id.unique' => 'Satuan tersebut sudah digunakan untuk produk ini.',
            'conversion_factor.required' => 'Faktor konversi wajib diisi.',
            'conversion_factor.numeric' => 'Faktor konversi harus berupa angka.',
            'conversion_factor.gt' => 'Faktor konversi harus lebih besar dari 0.',
        ]);

        $product = Product::findOrFail($validated['product_id']);

        if ((int) $product->base_unit_id === (int) $validated['unit_id']) {
            return back()
                ->withInput()
                ->withErrors([
                    'unit_id' => 'Satuan konversi tidak boleh sama dengan satuan dasar produk.',
                ]);
        }

        ProductUnitConversion::create($validated);

        return redirect()
            ->route('petugas.unit-conversions.index')
            ->with('success', 'Konversi satuan berhasil ditambahkan.');
    }

    public function edit(ProductUnitConversion $unitConversion)
    {
        $products = Product::with('baseUnit')
            ->orderBy('name')
            ->get();

        $units = Unit::orderBy('name')->get();

        return view('petugas.unit-conversions.edit', compact(
            'unitConversion',
            'products',
            'units'
        ));
    }

    public function update(Request $request, ProductUnitConversion $unitConversion)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'unit_id' => [
                'required',
                'exists:units,id',
                Rule::unique('product_unit_conversions', 'unit_id')
                    ->where('product_id', $request->product_id)
                    ->ignore($unitConversion->id),
            ],
            'conversion_factor' => ['required', 'numeric', 'gt:0'],
        ], [
            'product_id.required' => 'Produk wajib dipilih.',
            'product_id.exists' => 'Produk yang dipilih tidak valid.',
            'unit_id.required' => 'Satuan konversi wajib dipilih.',
            'unit_id.exists' => 'Satuan yang dipilih tidak valid.',
            'unit_id.unique' => 'Satuan tersebut sudah digunakan untuk produk ini.',
            'conversion_factor.required' => 'Faktor konversi wajib diisi.',
            'conversion_factor.numeric' => 'Faktor konversi harus berupa angka.',
            'conversion_factor.gt' => 'Faktor konversi harus lebih besar dari 0.',
        ]);

        $product = Product::findOrFail($validated['product_id']);

        if ((int) $product->base_unit_id === (int) $validated['unit_id']) {
            return back()
                ->withInput()
                ->withErrors([
                    'unit_id' => 'Satuan konversi tidak boleh sama dengan satuan dasar produk.',
                ]);
        }

        $unitConversion->update($validated);

        return redirect()
            ->route('petugas.unit-conversions.index')
            ->with('success', 'Konversi satuan berhasil diperbarui.');
    }

    public function destroy(ProductUnitConversion $unitConversion)
    {
        $unitConversion->delete();

        return redirect()
            ->route('petugas.unit-conversions.index')
            ->with('success', 'Konversi satuan berhasil dihapus.');
    }
}