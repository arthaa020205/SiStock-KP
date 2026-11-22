@extends('layouts.app')

@section('title', 'Barang Masuk - SiStock')
@section('page-title', 'Barang Masuk')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-semibold text-gray-800">
                Data Barang Masuk
            </h2>
            <p class="text-sm text-gray-500 mt-1">
                Kelola transaksi penerimaan barang dari supplier.
            </p>
        </div>

        <a href="{{ route('petugas.inbounds.create') }}"
           class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
            + Tambah Barang Masuk
        </a>
    </div>

    {{-- Notifikasi --}}
    @if (session('success'))
        <div class="p-4 rounded-lg bg-green-50 text-green-700 text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="p-4 rounded-lg bg-red-50 text-red-700 text-sm">
            {{ session('error') }}
        </div>
    @endif

    {{-- Pencarian --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
        <form action="{{ route('petugas.inbounds.index') }}"
              method="GET"
              class="flex flex-col sm:flex-row gap-3">

            <div class="flex-1">
                <input type="text"
                       name="search"
                       value="{{ $search ?? request('search') }}"
                       placeholder="Cari nomor faktur, nomor transaksi, atau nama supplier..."
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm">

            </div>

            <div class="flex gap-2">
                <button type="submit"
                        class="px-5 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                    Cari
                </button>

                @if (!empty($search))
                    <a href="{{ route('petugas.inbounds.index') }}"
                       class="px-5 py-2.5 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50">
                        Reset
                    </a>
                @endif
            </div>

        </form>

        @if (!empty($search))
            <p class="text-xs text-gray-500 mt-3">
                Hasil pencarian untuk:
                <span class="font-semibold text-gray-700">{{ $search }}</span>
            </p>
        @endif
    </div>

    {{-- Tabel Barang Masuk --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-600">

                <thead class="bg-gray-50 text-gray-700">
                    <tr>
                        <th class="px-6 py-4 font-semibold">No.</th>
                        <th class="px-6 py-4 font-semibold">Nomor Transaksi</th>
                        <th class="px-6 py-4 font-semibold">Nomor Faktur</th>
                        <th class="px-6 py-4 font-semibold">Tanggal</th>
                        <th class="px-6 py-4 font-semibold">Supplier</th>
                        <th class="px-6 py-4 font-semibold">Petugas</th>
                        <th class="px-6 py-4 font-semibold text-center">Status</th>
                        <th class="px-6 py-4 font-semibold text-center">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">

                    @forelse ($inbounds as $inbound)
                        <tr class="hover:bg-gray-50">

                            {{-- Nomor --}}
                            <td class="px-6 py-4">
                                {{ $inbounds->firstItem() + $loop->index }}
                            </td>

                            {{-- Nomor Transaksi --}}
                            <td class="px-6 py-4 font-medium text-gray-800 whitespace-nowrap">
                                {{ $inbound->transaction_number }}
                            </td>

                            {{-- Nomor Faktur Supplier --}}
                            <td class="px-6 py-4 font-medium text-blue-700 whitespace-nowrap">
                                {{ $inbound->supplier_invoice_number ?? '-' }}
                            </td>

                            {{-- Tanggal --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                {{ $inbound->transaction_date->format('d-m-Y H:i') }}
                            </td>

                            {{-- Supplier --}}
                            <td class="px-6 py-4">
                                {{ $inbound->supplier->name ?? '-' }}
                            </td>

                            {{-- Petugas --}}
                            <td class="px-6 py-4">
                                {{ $inbound->creator->name ?? '-' }}
                            </td>

                            {{-- Status --}}
                            <td class="px-6 py-4 text-center">
                                @if ($inbound->status === 'draft')
                                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700">
                                        Draft
                                    </span>
                                @elseif ($inbound->status === 'posted')
                                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                        Posted
                                    </span>
                                @else
                                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                                        {{ ucfirst($inbound->status) }}
                                    </span>
                                @endif
                            </td>

                            {{-- Aksi --}}
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-2 flex-wrap">

                                    <a href="{{ route('petugas.inbounds.show', $inbound->id) }}"
                                       class="px-3 py-2 bg-blue-100 text-blue-700 text-xs font-medium rounded-lg hover:bg-blue-200">
                                        Detail
                                    </a>

                                    @if ($inbound->status === 'draft')

                                        <a href="{{ route('petugas.inbounds.edit', $inbound->id) }}"
                                           class="px-3 py-2 bg-yellow-100 text-yellow-700 text-xs font-medium rounded-lg hover:bg-yellow-200">
                                            Edit
                                        </a>

                                        <form action="{{ route('petugas.inbounds.destroy', $inbound->id) }}"
                                              method="POST"
                                              onsubmit="return confirm('Yakin ingin menghapus draft barang masuk ini?')">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit"
                                                    class="px-3 py-2 bg-red-100 text-red-700 text-xs font-medium rounded-lg hover:bg-red-200">
                                                Hapus
                                            </button>
                                        </form>

                                        <form action="{{ route('petugas.inbounds.post', $inbound->id) }}"
                                              method="POST"
                                              onsubmit="return confirm('Posting transaksi ini? Stok akan langsung diperbarui.')">
                                            @csrf

                                            <button type="submit"
                                                    class="px-3 py-2 bg-green-600 text-white text-xs font-medium rounded-lg hover:bg-green-700">
                                                Posting
                                            </button>
                                        </form>

                                    @endif

                                </div>
                            </td>

                        </tr>

                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-10 text-center text-gray-400">

                                @if (!empty($search))
                                    Tidak ditemukan transaksi Barang Masuk dengan kata kunci "{{ $search }}".
                                @else
                                    Belum ada transaksi barang masuk.
                                @endif

                            </td>
                        </tr>
                    @endforelse

                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($inbounds->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $inbounds->links() }}
            </div>
        @endif

    </div>

</div>
@endsection