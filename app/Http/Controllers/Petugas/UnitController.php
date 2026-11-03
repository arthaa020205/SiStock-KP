<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UnitController extends Controller
{
    public function index()
    {
        $units = Unit::latest()->get();

        return view('petugas.units.index', compact('units'));
    }

    public function create()
    {
        return view('petugas.units.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:units,name'],
            'symbol' => ['required', 'string', 'max:20', 'unique:units,symbol'],
        ], [
            'name.required' => 'Nama satuan wajib diisi.',
            'name.unique' => 'Nama satuan sudah digunakan.',
            'symbol.required' => 'Simbol satuan wajib diisi.',
            'symbol.unique' => 'Simbol satuan sudah digunakan.',
        ]);

        Unit::create($validated);

        return redirect()
            ->route('petugas.units.index')
            ->with('success', 'Satuan berhasil ditambahkan.');
    }

    public function edit(Unit $unit)
    {
        return view('petugas.units.edit', compact('unit'));
    }

    public function update(Request $request, Unit $unit)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('units', 'name')->ignore($unit->id),
            ],
            'symbol' => [
                'required',
                'string',
                'max:20',
                Rule::unique('units', 'symbol')->ignore($unit->id),
            ],
        ], [
            'name.required' => 'Nama satuan wajib diisi.',
            'name.unique' => 'Nama satuan sudah digunakan.',
            'symbol.required' => 'Simbol satuan wajib diisi.',
            'symbol.unique' => 'Simbol satuan sudah digunakan.',
        ]);

        $unit->update($validated);

        return redirect()
            ->route('petugas.units.index')
            ->with('success', 'Satuan berhasil diperbarui.');
    }

    public function destroy(Unit $unit)
    {
        if (
            $unit->products()->exists() ||
            $unit->productConversions()->exists()
        ) {
            return redirect()
                ->route('petugas.units.index')
                ->with('error', 'Satuan tidak dapat dihapus karena sudah digunakan oleh produk atau konversi satuan.');
        }

        $unit->delete();

        return redirect()
            ->route('petugas.units.index')
            ->with('success', 'Satuan berhasil dihapus.');
    }
}