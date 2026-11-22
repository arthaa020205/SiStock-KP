<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use App\Services\ActivityLogger;
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

        $unit = Unit::create($validated);

        ActivityLogger::log(
            'unit_created',
            'Menambahkan satuan: ' . $unit->name,
            $unit,
            [
                'name' => $unit->name,
                'symbol' => $unit->symbol,
            ]
        );

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

        $oldData = $unit->only([
            'name',
            'symbol',
        ]);

        $unit->update($validated);

        ActivityLogger::log(
            'unit_updated',
            'Memperbarui satuan: ' . $unit->name,
            $unit,
            [
                'old' => $oldData,
                'new' => $unit->only([
                    'name',
                    'symbol',
                ]),
            ]
        );

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

        $unitData = [
            'id' => $unit->id,
            'name' => $unit->name,
            'symbol' => $unit->symbol,
        ];

        $unitName = $unit->name;
        $unitId = $unit->id;

        $unit->delete();

        ActivityLogger::log(
            'unit_deleted',
            'Menghapus satuan: ' . $unitName,
            null,
            $unitData + [
                'deleted_unit_id' => $unitId,
            ]
        );

        return redirect()
            ->route('petugas.units.index')
            ->with('success', 'Satuan berhasil dihapus.');
    }
}