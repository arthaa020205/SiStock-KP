<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductUnitConversion;
use App\Models\Unit;
use App\Services\ActivityLogger;
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
            'selling_price' => ['nullable', 'numeric', 'min:0'],
        ], [
            'product_id.required' => 'Produk wajib dipilih.',
            'product_id.exists' => 'Produk yang dipilih tidak valid.',
            'unit_id.required' => 'Satuan konversi wajib dipilih.',
            'unit_id.exists' => 'Satuan yang dipilih tidak valid.',
            'unit_id.unique' => 'Satuan tersebut sudah digunakan untuk produk ini.',
            'conversion_factor.required' => 'Faktor konversi wajib diisi.',
            'conversion_factor.numeric' => 'Faktor konversi harus berupa angka.',
            'conversion_factor.gt' => 'Faktor konversi harus lebih besar dari 0.',
            'selling_price.numeric' => 'Harga jual harus berupa angka.',
            'selling_price.min' => 'Harga jual tidak boleh negatif.',
        ]);

        $product = Product::findOrFail($validated['product_id']);

        if ((int) $product->base_unit_id === (int) $validated['unit_id']) {
            return back()
                ->withInput()
                ->withErrors([
                    'unit_id' => 'Satuan konversi tidak boleh sama dengan satuan dasar produk.',
                ]);
        }

        $unitConversion = ProductUnitConversion::create($validated);

        $unitConversion->load([
            'product.baseUnit',
            'unit',
        ]);

        ActivityLogger::log(
            'unit_conversion_created',
            'Menambahkan konversi satuan produk: ' . $unitConversion->product->name,
            $unitConversion,
            [
                'product_id' => $unitConversion->product_id,
                'product_name' => $unitConversion->product->name,
                'base_unit' => $unitConversion->product->baseUnit->symbol,
                'conversion_unit' => $unitConversion->unit->symbol,
                'conversion_factor' => $unitConversion->conversion_factor,
                'selling_price' => $unitConversion->selling_price,
            ]
        );

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
            'selling_price' => ['nullable', 'numeric', 'min:0'],
        ], [
            'product_id.required' => 'Produk wajib dipilih.',
            'product_id.exists' => 'Produk yang dipilih tidak valid.',
            'unit_id.required' => 'Satuan konversi wajib dipilih.',
            'unit_id.exists' => 'Satuan yang dipilih tidak valid.',
            'unit_id.unique' => 'Satuan tersebut sudah digunakan untuk produk ini.',
            'conversion_factor.required' => 'Faktor konversi wajib diisi.',
            'conversion_factor.numeric' => 'Faktor konversi harus berupa angka.',
            'conversion_factor.gt' => 'Faktor konversi harus lebih besar dari 0.',
            'selling_price.numeric' => 'Harga jual harus berupa angka.',
            'selling_price.min' => 'Harga jual tidak boleh negatif.',
        ]);

        $product = Product::findOrFail($validated['product_id']);

        if ((int) $product->base_unit_id === (int) $validated['unit_id']) {
            return back()
                ->withInput()
                ->withErrors([
                    'unit_id' => 'Satuan konversi tidak boleh sama dengan satuan dasar produk.',
                ]);
        }

        $unitConversion->load([
            'product.baseUnit',
            'unit',
        ]);

        $oldData = [
            'product_id' => $unitConversion->product_id,
            'product_name' => $unitConversion->product->name,
            'base_unit' => $unitConversion->product->baseUnit->symbol,
            'conversion_unit' => $unitConversion->unit->symbol,
            'conversion_factor' => $unitConversion->conversion_factor,
            'selling_price' => $unitConversion->selling_price,
        ];

        $unitConversion->update($validated);

        $unitConversion->load([
            'product.baseUnit',
            'unit',
        ]);

        ActivityLogger::log(
            'unit_conversion_updated',
            'Memperbarui konversi satuan produk: ' . $unitConversion->product->name,
            $unitConversion,
            [
                'old' => $oldData,
                'new' => [
                    'product_id' => $unitConversion->product_id,
                    'product_name' => $unitConversion->product->name,
                    'base_unit' => $unitConversion->product->baseUnit->symbol,
                    'conversion_unit' => $unitConversion->unit->symbol,
                    'conversion_factor' => $unitConversion->conversion_factor,
                    'selling_price' => $unitConversion->selling_price,
                ],
            ]
        );

        return redirect()
            ->route('petugas.unit-conversions.index')
            ->with('success', 'Konversi satuan berhasil diperbarui.');
    }

    public function destroy(ProductUnitConversion $unitConversion)
    {
        $unitConversion->load([
            'product.baseUnit',
            'unit',
        ]);

        $conversionData = [
            'id' => $unitConversion->id,
            'product_id' => $unitConversion->product_id,
            'product_name' => $unitConversion->product->name,
            'base_unit' => $unitConversion->product->baseUnit->symbol,
            'conversion_unit' => $unitConversion->unit->symbol,
            'conversion_factor' => $unitConversion->conversion_factor,
        ];

        $productName = $unitConversion->product->name;
        $conversionId = $unitConversion->id;

        $unitConversion->delete();

        ActivityLogger::log(
            'unit_conversion_deleted',
            'Menghapus konversi satuan produk: ' . $productName,
            null,
            $conversionData + [
                'deleted_conversion_id' => $conversionId,
            ]
        );

        return redirect()
            ->route('petugas.unit-conversions.index')
            ->with('success', 'Konversi satuan berhasil dihapus.');
    }
}