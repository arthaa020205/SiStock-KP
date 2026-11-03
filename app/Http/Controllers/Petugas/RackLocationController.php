<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\RackLocation;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RackLocationController extends Controller
{
    public function index()
    {
        $rackLocations = RackLocation::latest()->get();

        return view('petugas.rack-locations.index', compact('rackLocations'));
    }

    public function create()
    {
        return view('petugas.rack-locations.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:rack_locations,code'],
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
        ], [
            'code.required' => 'Kode rak wajib diisi.',
            'code.unique' => 'Kode rak sudah digunakan.',
            'name.required' => 'Nama rak wajib diisi.',
            'is_active.required' => 'Status rak wajib dipilih.',
        ]);

        RackLocation::create($validated);

        return redirect()
            ->route('petugas.rack-locations.index')
            ->with('success', 'Lokasi rak berhasil ditambahkan.');
    }

    public function edit(RackLocation $rackLocation)
    {
        return view('petugas.rack-locations.edit', compact('rackLocation'));
    }

    public function update(Request $request, RackLocation $rackLocation)
    {
        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('rack_locations', 'code')->ignore($rackLocation->id),
            ],
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
        ], [
            'code.required' => 'Kode rak wajib diisi.',
            'code.unique' => 'Kode rak sudah digunakan.',
            'name.required' => 'Nama rak wajib diisi.',
            'is_active.required' => 'Status rak wajib dipilih.',
        ]);

        $rackLocation->update($validated);

        return redirect()
            ->route('petugas.rack-locations.index')
            ->with('success', 'Lokasi rak berhasil diperbarui.');
    }

    public function destroy(RackLocation $rackLocation)
    {
        if ($rackLocation->batchStocks()->exists()) {
            return redirect()
                ->route('petugas.rack-locations.index')
                ->with('error', 'Lokasi rak tidak dapat dihapus karena sudah digunakan untuk menyimpan stok barang.');
        }

        $rackLocation->delete();

        return redirect()
            ->route('petugas.rack-locations.index')
            ->with('success', 'Lokasi rak berhasil dihapus.');
    }
}