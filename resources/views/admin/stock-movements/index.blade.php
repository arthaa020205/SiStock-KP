@extends('layouts.app')

@section('title', 'Riwayat Pergerakan Stok')

@section('page-title', 'Riwayat Pergerakan Stok')

@section('content')

    <div class="space-y-6">

        {{-- Filter dan Pencarian --}}
        <div class="rounded-xl bg-white p-6 shadow-sm">
            <form method="GET"
                  action="{{ route('admin.stock-movements.index') }}"
                  class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-5">

                <div class="lg:col-span-2">
                    <label for="search"
                           class="mb-2 block text-sm font-medium text-gray-700">
                        Cari Produk / Batch
                    </label>

                    <input
                        type="text"
                        name="search"
                        id="search"
                        value="{{ request('search') }}"
                        placeholder="Nama produk, kode produk, atau batch..."
                        class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                </div>

                <div>
                    <label for="movement_type"
                           class="mb-2 block text-sm font-medium text-gray-700">
                        Jenis Pergerakan
                    </label>

                    <select
                        name="movement_type"
                        id="movement_type"
                        class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option value="">Semua Jenis</option>
                        <option value="in" @selected(request('movement_type') === 'in')>
                            Barang Masuk
                        </option>
                        <option value="out" @selected(request('movement_type') === 'out')>
                            Barang Keluar
                        </option>
                        <option value="transfer_in" @selected(request('movement_type') === 'transfer_in')>
                            Transfer Masuk
                        </option>
                        <option value="transfer_out" @selected(request('movement_type') === 'transfer_out')>
                            Transfer Keluar
                        </option>
                        <option value="adjustment_in" @selected(request('movement_type') === 'adjustment_in')>
                            Penyesuaian Masuk
                        </option>
                        <option value="adjustment_out" @selected(request('movement_type') === 'adjustment_out')>
                            Penyesuaian Keluar
                        </option>
                    </select>
                </div>

                <div>
                    <label for="date_from"
                           class="mb-2 block text-sm font-medium text-gray-700">
                        Dari Tanggal
                    </label>

                    <input
                        type="date"
                        name="date_from"
                        id="date_from"
                        value="{{ request('date_from') }}"
                        class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                </div>

                <div>
                    <label for="date_to"
                           class="mb-2 block text-sm font-medium text-gray-700">
                        Sampai Tanggal
                    </label>

                    <input
                        type="date"
                        name="date_to"
                        id="date_to"
                        value="{{ request('date_to') }}"
                        class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                </div>

                <div class="flex items-end gap-2 lg:col-span-5">
                    <button
                        type="submit"
                        class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-indigo-700"
                    >
                        Terapkan Filter
                    </button>

                    <a
                        href="{{ route('admin.stock-movements.index') }}"
                        class="rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50"
                    >
                        Reset
                    </a>
                </div>
            </form>
        </div>

        {{-- Tabel Riwayat --}}
        <div class="overflow-hidden rounded-xl bg-white shadow-sm">

            <div class="flex flex-col gap-4 border-b border-gray-200 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">

                <div>
                    <h3 class="font-semibold text-gray-800">
                        Data Pergerakan Stok
                    </h3>

                    <p class="mt-1 text-sm text-gray-500">
                        Menampilkan {{ $stockMovements->total() }} riwayat pergerakan stok.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2">

                    {{-- Export Excel --}}
                    <a
                        href="{{ route('admin.stock-movements.export-excel', request()->except('page')) }}"
                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-green-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-green-700"
                    >
                        <span>↓</span>
                        Export Excel
                    </a>

                    {{-- Export PDF --}}
                    <a
                        href="{{ route('admin.stock-movements.export-pdf', request()->except('page')) }}"
                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-red-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-red-700"
                    >
                        <span>↓</span>
                        Export PDF
                    </a>

                </div>

            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">

                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">
                                No
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">
                                Tanggal
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">
                                Produk
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">
                                Kode Batch
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">
                                Lokasi Rak
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">
                                Jenis
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-semibold uppercase text-gray-500">
                                Jumlah
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-semibold uppercase text-gray-500">
                                Stok Sebelum
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-semibold uppercase text-gray-500">
                                Stok Sesudah
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">
                                Petugas
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">
                                Keterangan
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200 bg-white">

                        @forelse ($stockMovements as $index => $movement)

                            @php
                                $movementLabels = [
                                    'in' => 'Barang Masuk',
                                    'out' => 'Barang Keluar',
                                    'transfer_in' => 'Transfer Masuk',
                                    'transfer_out' => 'Transfer Keluar',
                                    'adjustment_in' => 'Penyesuaian Masuk',
                                    'adjustment_out' => 'Penyesuaian Keluar',
                                ];

                                $movementColors = [
                                    'in' => 'bg-green-100 text-green-700',
                                    'out' => 'bg-red-100 text-red-700',
                                    'transfer_in' => 'bg-blue-100 text-blue-700',
                                    'transfer_out' => 'bg-orange-100 text-orange-700',
                                    'adjustment_in' => 'bg-emerald-100 text-emerald-700',
                                    'adjustment_out' => 'bg-rose-100 text-rose-700',
                                ];

                                $isIncrease = in_array($movement->movement_type, [
                                    'in',
                                    'transfer_in',
                                    'adjustment_in',
                                ]);
                            @endphp

                            <tr class="hover:bg-gray-50">

                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                    {{ $stockMovements->firstItem() + $index }}
                                </td>

                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                                    {{ $movement->created_at?->format('d/m/Y H:i') ?? '-' }}
                                </td>

                                <td class="px-6 py-4 text-sm">
                                    <div class="font-medium text-gray-900">
                                        {{ $movement->batchStock?->productBatch?->product?->name ?? '-' }}
                                    </div>

                                    <div class="text-xs text-gray-500">
                                        {{ $movement->batchStock?->productBatch?->product?->code ?? '-' }}
                                    </div>
                                </td>

                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                                    {{ $movement->batchStock?->productBatch?->batch_code ?? '-' }}
                                </td>

                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                                    {{ $movement->batchStock?->rackLocation?->name ?? '-' }}
                                </td>

                                <td class="whitespace-nowrap px-6 py-4">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium {{ $movementColors[$movement->movement_type] ?? 'bg-gray-100 text-gray-700' }}">
                                        {{ $movementLabels[$movement->movement_type] ?? $movement->movement_type }}
                                    </span>
                                </td>

                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-semibold {{ $isIncrease ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $isIncrease ? '+' : '-' }}{{ number_format((float) $movement->quantity, 3, ',', '.') }}
                                </td>

                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm text-gray-700">
                                    {{ number_format((float) $movement->balance_before, 3, ',', '.') }}
                                </td>

                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium text-gray-900">
                                    {{ number_format((float) $movement->balance_after, 3, ',', '.') }}
                                </td>

                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                                    {{ $movement->creator?->name ?? '-' }}
                                </td>

                                <td class="min-w-48 px-6 py-4 text-sm text-gray-600">
                                    {{ $movement->notes ?: '-' }}
                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="11"
                                    class="px-6 py-12 text-center text-sm text-gray-500">
                                    Belum ada riwayat pergerakan stok.
                                </td>
                            </tr>

                        @endforelse

                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if ($stockMovements->hasPages())
                <div class="border-t border-gray-200 px-6 py-4">
                    {{ $stockMovements->links() }}
                </div>
            @endif

        </div>

    </div>
@endsection