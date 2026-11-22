@extends('layouts.app')

@section('title', 'Detail Barang Keluar - SiStock')
@section('page-title', 'Detail Barang Keluar')

@section('content')
@php
    $isSale = $outbound->reason === 'penjualan';

    $grandTotal = $outbound->details->sum(function ($detail) {
        return (float) ($detail->subtotal ?? 0);
    });
@endphp

<div class="space-y-6">

    <div class="flex items-center justify-between">
        <a href="{{ route('petugas.outbounds.index') }}"
           class="text-sm text-gray-600 hover:text-gray-900">
            &larr; Kembali ke Barang Keluar
        </a>

        @if($outbound->status === 'draft')
            <form action="{{ route('petugas.outbounds.post', $outbound->id) }}"
                  method="POST"
                  onsubmit="return confirm('Posting transaksi ini? Stok akan langsung dikurangi.')">
                @csrf
                <button type="submit"
                    class="rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700">
                    Posting Transaksi
                </button>
            </form>
        @endif
    </div>

    @if(session('success'))
        <div class="rounded-lg bg-green-100 p-4 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="rounded-lg bg-red-100 p-4 text-sm text-red-700">
            <ul class="list-inside list-disc">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- INFORMASI TRANSAKSI --}}
    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <h2 class="mb-4 text-lg font-semibold text-gray-800">
            Informasi Transaksi
        </h2>

        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">

            <div>
                <p class="text-sm text-gray-500">Nomor Transaksi</p>
                <p class="font-medium text-gray-800">
                    {{ $outbound->transaction_number }}
                </p>
            </div>

            <div>
                <p class="text-sm text-gray-500">Tanggal Transaksi</p>
                <p class="font-medium text-gray-800">
                    {{ $outbound->transaction_date?->format('d-m-Y') ?? '-' }}
                </p>
            </div>

            <div>
                <p class="text-sm text-gray-500">Jenis Pengeluaran</p>
                <p class="font-medium text-gray-800">
                    {{ ucwords(str_replace('_', ' ', $outbound->reason)) }}
                </p>
            </div>

            <div>
                <p class="text-sm text-gray-500">Customer</p>
                <p class="font-medium text-gray-800">
                    {{ $outbound->customer?->name ?? '-' }}
                </p>
            </div>

            <div>
                <p class="text-sm text-gray-500">Petugas</p>
                <p class="font-medium text-gray-800">
                    {{ $outbound->creator?->name ?? '-' }}
                </p>
            </div>

            <div>
                <p class="text-sm text-gray-500">Status</p>

                @if($outbound->status === 'posted')
                    <span class="inline-flex rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">
                        Posted
                    </span>
                @elseif($outbound->status === 'draft')
                    <span class="inline-flex rounded-full bg-yellow-100 px-3 py-1 text-xs font-semibold text-yellow-700">
                        Draft
                    </span>
                @else
                    <span class="inline-flex rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">
                        {{ ucfirst($outbound->status) }}
                    </span>
                @endif
            </div>

            <div class="md:col-span-2">
                <p class="text-sm text-gray-500">Catatan</p>
                <p class="font-medium text-gray-800">
                    {{ $outbound->notes ?: '-' }}
                </p>
            </div>

        </div>
    </div>

    {{-- DETAIL BARANG KELUAR --}}
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

        <div class="border-b border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-800">
                Detail Barang Keluar
            </h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">

                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="px-6 py-3">No.</th>
                        <th class="px-6 py-3">Produk</th>
                        <th class="px-6 py-3">Jumlah Keluar</th>

                        @if($isSale)
                            <th class="px-6 py-3">Harga Satuan</th>
                            <th class="px-6 py-3">Subtotal</th>
                        @endif

                        <th class="px-6 py-3">Alokasi Batch dan Rak</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">

                    @forelse($outbound->details as $index => $detail)
                        <tr>

                            <td class="px-6 py-4">
                                {{ $index + 1 }}
                            </td>

                            <td class="px-6 py-4">
                                <p class="font-medium text-gray-800">
                                    {{ $detail->product?->name ?? '-' }}
                                </p>

                                <p class="text-xs text-gray-500">
                                    {{ $detail->product?->code ?? '-' }}
                                </p>
                            </td>

                            <td class="px-6 py-4">
                                <p class="font-medium text-gray-800">
                                    {{ number_format((float) $detail->quantity, 3, ',', '.') }}
                                    {{ $detail->unit?->symbol ?? '' }}
                                </p>

                                <p class="text-xs text-gray-500">
                                    Setara
                                    {{ number_format((float) $detail->base_quantity, 3, ',', '.') }}
                                    {{ $detail->product?->baseUnit?->symbol ?? '' }}
                                </p>
                            </td>

                            @if($isSale)
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($detail->unit_price !== null)
                                        <span class="font-medium text-gray-800">
                                            Rp{{ number_format((float) $detail->unit_price, 2, ',', '.') }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($detail->subtotal !== null)
                                        <span class="font-semibold text-blue-700">
                                            Rp{{ number_format((float) $detail->subtotal, 2, ',', '.') }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                            @endif

                            <td class="px-6 py-4">
                                <div class="space-y-3">

                                    @forelse($detail->batchDetails as $allocation)
                                        <div class="rounded-lg bg-gray-50 p-3">

                                            <p class="font-medium text-gray-800">
                                                Batch:
                                                {{ $allocation->sourceBatchStock?->productBatch?->batch_code ?? '-' }}
                                            </p>

                                            <p class="text-gray-600">
                                                Rak:
                                                {{ $allocation->sourceBatchStock?->rackLocation?->code ?? '-' }}
                                                -
                                                {{ $allocation->sourceBatchStock?->rackLocation?->name ?? '-' }}
                                            </p>

                                            <p class="mt-1 font-semibold text-gray-800">
                                                Jumlah:
                                                {{ number_format((float) $allocation->quantity, 3, ',', '.') }}
                                                {{ $detail->product?->baseUnit?->symbol ?? '' }}
                                            </p>

                                        </div>
                                    @empty
                                        <span class="text-gray-400">
                                            Belum ada alokasi batch.
                                        </span>
                                    @endforelse

                                </div>
                            </td>

                        </tr>

                    @empty
                        <tr>
                            <td colspan="{{ $isSale ? 6 : 4 }}"
                                class="px-6 py-8 text-center text-gray-500">
                                Belum ada detail barang.
                            </td>
                        </tr>
                    @endforelse

                </tbody>

                {{-- TOTAL PENJUALAN --}}
                @if($isSale && $outbound->details->isNotEmpty())
                    <tfoot class="bg-blue-50 border-t border-blue-200">
                        <tr>
                            <td colspan="4"
                                class="px-6 py-5 text-right font-semibold text-gray-700">
                                Total Penjualan
                            </td>

                            <td colspan="2"
                                class="px-6 py-5 text-lg font-bold text-blue-700">
                                Rp{{ number_format($grandTotal, 2, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                @endif

            </table>
        </div>
    </div>

</div>
@endsection