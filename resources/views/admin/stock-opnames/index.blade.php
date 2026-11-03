@extends('layouts.app')

@section('title', 'Stock Opname - SiStock')
@section('page-title', 'Stock Opname')

@section('content')
<div class="space-y-6">

    {{-- Notifikasi --}}
    @if (session('success'))
        <div class="rounded-lg bg-green-100 px-4 py-3 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="rounded-lg bg-red-100 px-4 py-3 text-sm text-red-700">
            {{ $errors->first() }}
        </div>
    @endif

    {{-- Informasi --}}
    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-gray-800">
            Data Stock Opname
        </h2>

        <p class="mt-1 text-sm text-gray-500">
            Daftar pemeriksaan stok yang diajukan oleh Petugas Gudang.
            Admin dapat memeriksa hasil penghitungan dan permintaan penyesuaian stok.
        </p>
    </div>

    {{-- Filter dan Pencarian --}}
    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">

        <form action="{{ route('admin.stock-opnames.index') }}"
              method="GET"
              class="flex flex-col gap-3 md:flex-row">

            <input type="text"
                   name="search"
                   value="{{ request('search') }}"
                   placeholder="Cari nomor Stock Opname..."
                   class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">

            <select name="status"
                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 md:w-52">

                <option value="">Semua Status</option>
                <option value="submitted" @selected(request('status') === 'submitted')>
                    Diajukan
                </option>
                <option value="approved" @selected(request('status') === 'approved')>
                    Disetujui
                </option>
                <option value="rejected" @selected(request('status') === 'rejected')>
                    Ditolak
                </option>
                <option value="completed" @selected(request('status') === 'completed')>
                    Selesai
                </option>

            </select>

            <button type="submit"
                    class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-blue-700">
                Cari
            </button>

            <a href="{{ route('admin.stock-opnames.index') }}"
               class="rounded-lg border border-gray-300 px-5 py-2.5 text-center text-sm font-medium text-gray-700 hover:bg-gray-50">
                Reset
            </a>

        </form>

    </div>

    {{-- Tabel Stock Opname --}}
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

        <div class="border-b border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-800">
                Daftar Stock Opname
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                Data pemeriksaan stok yang tercatat di sistem.
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">

                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-600">
                            No.
                        </th>

                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-600">
                            Nomor Stock Opname
                        </th>

                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-600">
                            Tanggal
                        </th>

                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-600">
                            Petugas
                        </th>

                        <th class="px-5 py-3 text-center text-xs font-semibold uppercase text-gray-600">
                            Total Barang
                        </th>

                        <th class="px-5 py-3 text-center text-xs font-semibold uppercase text-gray-600">
                            Permintaan Penyesuaian
                        </th>

                        <th class="px-5 py-3 text-center text-xs font-semibold uppercase text-gray-600">
                            Status
                        </th>

                        <th class="px-5 py-3 text-center text-xs font-semibold uppercase text-gray-600">
                            Aksi
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">

                    @forelse ($stockOpnames as $stockOpname)

                        @php
                            $pendingCount = $stockOpname->details
                                ->flatMap(fn ($detail) => $detail->adjustmentRequests)
                                ->where('status', 'pending')
                                ->count();

                            $statusClasses = [
                                'submitted' => 'bg-blue-100 text-blue-700',
                                'approved' => 'bg-green-100 text-green-700',
                                'rejected' => 'bg-red-100 text-red-700',
                                'completed' => 'bg-gray-100 text-gray-700',
                            ];

                            $statusLabels = [
                                'submitted' => 'Diajukan',
                                'approved' => 'Disetujui',
                                'rejected' => 'Ditolak',
                                'completed' => 'Selesai',
                            ];
                        @endphp

                        <tr class="hover:bg-gray-50">

                            <td class="whitespace-nowrap px-5 py-4 text-sm text-gray-600">
                                {{ $stockOpnames->firstItem() + $loop->index }}
                            </td>

                            <td class="whitespace-nowrap px-5 py-4">
                                <p class="text-sm font-semibold text-gray-800">
                                    {{ $stockOpname->opname_number }}
                                </p>
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-sm text-gray-700">
                                {{ \Carbon\Carbon::parse($stockOpname->opname_date)->format('d-m-Y') }}
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-sm text-gray-700">
                                {{ $stockOpname->creator->name ?? '-' }}
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-center text-sm text-gray-700">
                                {{ $stockOpname->details->count() }}
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-center">
                                @if ($pendingCount > 0)
                                    <span class="rounded-full bg-yellow-100 px-3 py-1 text-xs font-medium text-yellow-700">
                                        {{ $pendingCount }} Menunggu
                                    </span>
                                @else
                                    <span class="text-sm text-gray-500">
                                        {{ $stockOpname->details
                                            ->flatMap(fn ($detail) => $detail->adjustmentRequests)
                                            ->count() }}
                                        Permintaan
                                    </span>
                                @endif
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-center">
                                <span class="rounded-full px-3 py-1 text-xs font-medium {{ $statusClasses[$stockOpname->status] ?? 'bg-gray-100 text-gray-700' }}">
                                    {{ $statusLabels[$stockOpname->status] ?? ucfirst($stockOpname->status) }}
                                </span>
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-center">
                                <a href="{{ route('admin.stock-opnames.show', $stockOpname->id) }}"
                                    class="inline-flex rounded-lg bg-blue-600 px-4 py-2 text-xs font-medium text-white hover:bg-blue-700">
                                        Detail
                                    </a>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="8"
                                class="px-6 py-10 text-center text-sm text-gray-500">
                                Belum ada data Stock Opname yang diajukan.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>
        </div>

        {{-- Pagination --}}
        @if ($stockOpnames->hasPages())
            <div class="border-t border-gray-200 p-5">
                {{ $stockOpnames->links() }}
            </div>
        @endif

    </div>

</div>
@endsection