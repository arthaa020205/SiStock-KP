
@extends('layouts.app')

@section('title', 'Barang Keluar - SiStock')
@section('page-title', 'Barang Keluar')

@section('content')
<div class="space-y-6">

    @if (session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
            <ul class="list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-lg font-semibold text-gray-800">
                Riwayat Barang Keluar
            </h2>
            <p class="text-sm text-gray-500 mt-1">
                Daftar transaksi pengeluaran stok dari gudang.
            </p>
        </div>

        <a href="{{ route('petugas.outbounds.create') }}"
           class="inline-flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-lg text-sm font-medium">
            + Tambah Barang Keluar
        </a>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="px-5 py-4 font-semibold">No.</th>
                        <th class="px-5 py-4 font-semibold">Nomor Transaksi</th>
                        <th class="px-5 py-4 font-semibold">Tanggal</th>
                        <th class="px-5 py-4 font-semibold">Alasan</th>
                        <th class="px-5 py-4 font-semibold">Pelanggan</th>
                        <th class="px-5 py-4 font-semibold">Petugas</th>
                        <th class="px-5 py-4 font-semibold">Status</th>
                        <th class="px-5 py-4 font-semibold text-center">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">

                    @forelse ($outbounds as $index => $outbound)
                        <tr class="hover:bg-gray-50">

                            <td class="px-5 py-4 text-gray-600">
                                {{ $outbounds->firstItem() + $index }}
                            </td>

                            <td class="px-5 py-4 font-medium text-gray-800">
                                {{ $outbound->transaction_number }}
                            </td>

                            <td class="px-5 py-4 text-gray-600">
                                {{ \Carbon\Carbon::parse($outbound->transaction_date)->format('d-m-Y') }}
                            </td>

                            <td class="px-5 py-4 text-gray-600">
                                @switch($outbound->reason)
                                    @case('penjualan')
                                        Penjualan
                                        @break
                                    @case('transfer_toko')
                                        Transfer ke Toko
                                        @break
                                    @case('rusak')
                                        Barang Rusak
                                        @break
                                    @case('kadaluarsa')
                                        Barang Kadaluarsa
                                        @break
                                    @case('pemakaian')
                                        Pemakaian
                                        @break
                                    @default
                                        Lainnya
                                @endswitch
                            </td>

                            <td class="px-5 py-4 text-gray-600">
                                {{ $outbound->customer?->name ?? '-' }}
                            </td>

                            <td class="px-5 py-4 text-gray-600">
                                {{ $outbound->creator?->name ?? '-' }}
                            </td>

                            <td class="px-5 py-4">
                                @if ($outbound->status === 'draft')
                                    <span class="px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700">
                                        Draft
                                    </span>
                                @elseif ($outbound->status === 'posted')
                                    <span class="px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                        Posted
                                    </span>
                                @else
                                    <span class="px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                        {{ ucfirst($outbound->status) }}
                                    </span>
                                @endif
                            </td>

                            <td class="px-5 py-4">
                                <div class="flex items-center justify-center gap-2">

                                    <a href="{{ route('petugas.outbounds.show', $outbound->id) }}"
                                    class="px-3 py-1.5 bg-blue-50 text-blue-600 hover:bg-blue-100 rounded-lg text-xs font-medium">
                                        Detail
                                    </a>

                                    @if ($outbound->status === 'draft')
                                        <a href="{{ route('petugas.outbounds.edit', $outbound->id) }}"
                                        class="px-3 py-1.5 bg-yellow-50 text-yellow-700 hover:bg-yellow-100 rounded-lg text-xs font-medium">
                                            Edit
                                        </a>
                                    @endif

                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-12 text-center text-gray-500">
                                Belum ada transaksi barang keluar.
                            </td>
                        </tr>
                    @endforelse

                </tbody>
            </table>
        </div>

        <div class="px-5 py-4 border-t border-gray-100">
            {{ $outbounds->links() }}
        </div>

    </div>

</div>
@endsection
