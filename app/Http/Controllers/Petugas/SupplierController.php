<?php
namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::latest()->get();

        return view('petugas.suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        return view('petugas.suppliers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string'],
            'email' => ['nullable', 'email', 'max:255'],
            'is_active' => ['required', 'boolean'],
        ], [
            'name.required' => 'Nama supplier wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'is_active.required' => 'Status supplier wajib dipilih.',
        ]);

        $supplier = Supplier::create($validated);

        ActivityLogger::log(
            'supplier_created',
            'Menambahkan supplier: ' . $supplier->name,
            $supplier,
            [
                'name' => $supplier->name,
                'contact_person' => $supplier->contact_person,
                'phone' => $supplier->phone,
                'address' => $supplier->address,
                'email' => $supplier->email,
                'is_active' => $supplier->is_active,
            ]
        );

        return redirect()
            ->route('petugas.suppliers.index')
            ->with('success', 'Supplier berhasil ditambahkan.');
    }

    public function edit(Supplier $supplier)
    {
        return view('petugas.suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string'],
            'email' => ['nullable', 'email', 'max:255'],
            'is_active' => ['required', 'boolean'],
        ], [
            'name.required' => 'Nama supplier wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'is_active.required' => 'Status supplier wajib dipilih.',
        ]);

        $oldData = $supplier->only([
            'name',
            'contact_person',
            'phone',
            'address',
            'email',
            'is_active',
        ]);

        $supplier->update($validated);

        ActivityLogger::log(
            'supplier_updated',
            'Memperbarui supplier: ' . $supplier->name,
            $supplier,
            [
                'old' => $oldData,
                'new' => $supplier->only([
                    'name',
                    'contact_person',
                    'phone',
                    'address',
                    'email',
                    'is_active',
                ]),
            ]
        );

        return redirect()
            ->route('petugas.suppliers.index')
            ->with('success', 'Supplier berhasil diperbarui.');
    }

    public function destroy(Supplier $supplier)
    {
        if ($supplier->stockTransactions()->exists()) {
            return redirect()
                ->route('petugas.suppliers.index')
                ->with('error', 'Supplier tidak dapat dihapus karena sudah digunakan dalam transaksi.');
        }

        $supplierData = [
            'id' => $supplier->id,
            'name' => $supplier->name,
            'contact_person' => $supplier->contact_person,
            'phone' => $supplier->phone,
            'address' => $supplier->address,
            'email' => $supplier->email,
            'is_active' => $supplier->is_active,
        ];

        $supplierName = $supplier->name;
        $supplierId = $supplier->id;

        $supplier->delete();

        ActivityLogger::log(
            'supplier_deleted',
            'Menghapus supplier: ' . $supplierName,
            null,
            $supplierData + [
                'deleted_supplier_id' => $supplierId,
            ]
        );

        return redirect()
            ->route('petugas.suppliers.index')
            ->with('success', 'Supplier berhasil dihapus.');
    }
}
