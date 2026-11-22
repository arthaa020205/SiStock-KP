<?php
namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = Customer::latest()->get();

        return view('petugas.customers.index', compact('customers'));
    }

    public function create()
    {
        return view('petugas.customers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
        ], [
            'name.required' => 'Nama customer wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'is_active.required' => 'Status customer wajib dipilih.',
        ]);

        $customer = Customer::create($validated);

        ActivityLogger::log(
            'customer_created',
            'Menambahkan customer: ' . $customer->name,
            $customer,
            [
                'name' => $customer->name,
                'phone' => $customer->phone,
                'email' => $customer->email,
                'address' => $customer->address,
                'is_active' => $customer->is_active,
            ]
        );

        return redirect()
            ->route('petugas.customers.index')
            ->with('success', 'Customer berhasil ditambahkan.');
    }

    public function edit(Customer $customer)
    {
        return view('petugas.customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
        ], [
            'name.required' => 'Nama customer wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'is_active.required' => 'Status customer wajib dipilih.',
        ]);

        $oldData = $customer->only([
            'name',
            'phone',
            'email',
            'address',
            'is_active',
        ]);

        $customer->update($validated);

        ActivityLogger::log(
            'customer_updated',
            'Memperbarui customer: ' . $customer->name,
            $customer,
            [
                'old' => $oldData,
                'new' => $customer->only([
                    'name',
                    'phone',
                    'email',
                    'address',
                    'is_active',
                ]),
            ]
        );

        return redirect()
            ->route('petugas.customers.index')
            ->with('success', 'Customer berhasil diperbarui.');
    }

    public function destroy(Customer $customer)
    {
        if ($customer->stockTransactions()->exists()) {
            return redirect()
                ->route('petugas.customers.index')
                ->with('error', 'Customer tidak dapat dihapus karena sudah digunakan dalam transaksi.');
        }

        $customerData = [
            'id' => $customer->id,
            'name' => $customer->name,
            'phone' => $customer->phone,
            'email' => $customer->email,
            'address' => $customer->address,
            'is_active' => $customer->is_active,
        ];

        $customerName = $customer->name;
        $customerId = $customer->id;

        $customer->delete();

        ActivityLogger::log(
            'customer_deleted',
            'Menghapus customer: ' . $customerName,
            null,
            $customerData + [
                'deleted_customer_id' => $customerId,
            ]
        );

        return redirect()
            ->route('petugas.customers.index')
            ->with('success', 'Customer berhasil dihapus.');
    }
}
