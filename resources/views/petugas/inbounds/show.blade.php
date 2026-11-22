@extends('layouts.app')

@section('title', 'Detail Barang Masuk - SiStock')
@section('page-title', 'Detail Barang Masuk')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h2 class="text-xl font-semibold text-gray-800">
                Detail Transaksi Barang Masuk
            </h2>
            <p class="text-sm text-gray-500 mt-1">
                Informasi lengkap penerimaan barang.
            </p>
        </div>

        <a href="{{ route('petugas.inbounds.index') }}"
           class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50 text-center">
            Kembali
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

            {{-- Nomor Transaksi --}}
            <div>
                <p class="text-sm text-gray-500">Nomor Transaksi</p>
                <p class="font-semibold text-gray-800 mt-1">
                    {{ $inbound->transaction_number }}
                </p>
            </div>

            {{-- Nomor Faktur Supplier --}}
            <div>
                <p class="text-sm text-gray-500">Nomor Faktur Supplier</p>
                <p class="font-semibold text-blue-700 mt-1">
                    {{ $inbound->supplier_invoice_number ?: '-' }}
                </p>
            </div>

            {{-- Tanggal Transaksi --}}
            <div>
                <p class="text-sm text-gray-500">Tanggal Transaksi</p>
                <p class="font-medium text-gray-800 mt-1">
                    {{ $inbound->transaction_date->format('d-m-Y H:i') }}
                </p>
            </div>

            {{-- Supplier --}}
            <div>
                <p class="text-sm text-gray-500">Supplier</p>
                <p class="font-medium text-gray-800 mt-1">
                    {{ $inbound->supplier->name ?? '-' }}
                </p>
            </div>

            {{-- Petugas --}}
            <div>
                <p class="text-sm text-gray-500">Petugas</p>
                <p class="font-medium text-gray-800 mt-1">
                    {{ $inbound->creator->name ?? '-' }}
                </p>
            </div>

            {{-- Status --}}
            <div>
                <p class="text-sm text-gray-500">Status</p>
                <p class="font-medium mt-1">
                    {{ ucfirst($inbound->status) }}
                </p>
            </div>

            {{-- Catatan --}}
            <div class="md:col-span-2">
                <p class="text-sm text-gray-500">Catatan</p>
                <p class="font-medium text-gray-800 mt-1">
                    {{ $inbound->notes ?: '-' }}
                </p>
            </div>

        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-6 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800">Daftar Barang</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-600">
                <thead class="bg-gray-50 text-gray-700">
                    <tr>
                        <th class="px-5 py-4">No.</th>
                        <th class="px-5 py-4">Produk</th>
                        <th class="px-5 py-4">Jumlah</th>
                        <th class="px-5 py-4">Satuan</th>
                        <th class="px-5 py-4">Jumlah Dasar</th>
                        <th class="px-5 py-4">Batch</th>
                        <th class="px-5 py-4">Kedaluwarsa</th>
                        <th class="px-5 py-4">Lokasi Rak</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">
                    @foreach ($inbound->details as $detail)
                        @foreach ($detail->batchDetails as $batchDetail)
                            @php
                                $batchStock = $batchDetail->destinationBatchStock;
                                $batch = $batchStock?->productBatch;
                            @endphp

                            <tr>
                                <td class="px-5 py-4">{{ $loop->parent->iteration }}</td>
                                <td class="px-5 py-4 font-medium text-gray-800">
                                    {{ $detail->product->name ?? '-' }}
                                </td>
                                <td class="px-5 py-4">
                                    {{ number_format((float) $detail->quantity, 3, ',', '.') }}
                                </td>
                                <td class="px-5 py-4">
                                    {{ $detail->unit->symbol ?? $detail->unit->name ?? '-' }}
                                </td>
                                <td class="px-5 py-4">
                                    {{ number_format((float) $batchDetail->quantity, 3, ',', '.') }}
                                </td>
                                <td class="px-5 py-4">
                                    {{ $batch?->batch_code ?? '-' }}
                                </td>
                                <td class="px-5 py-4">
                                    {{ $batch?->expiry_date?->format('d-m-Y') ?? '-' }}
                                </td>
                                <td class="px-5 py-4">
                                    {{ $batchStock?->rackLocation?->code ?? '-' }}
                                </td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @if ($inbound->status === 'draft')
        <div class="flex justify-end">
            <a href="{{ route('petugas.inbounds.edit', $inbound->id) }}"
               class="px-5 py-2.5 bg-yellow-500 text-white text-sm font-medium rounded-lg hover:bg-yellow-600">
                Edit Draft
            </a>
        </div>
    @endif

</div>
@endsection