@extends('layouts.app')

@section('title', 'Detail Transfer - SiStock')
@section('page-title', 'Detail Transfer Lokasi Rak')

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
            <ul class="list-inside list-disc">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Informasi Transaksi --}}
    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <div class="mb-5 flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
            <div>
                <h2 class="text-lg font-semibold text-gray-800">
                    Informasi Transaksi
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Detail informasi perpindahan stok antar lokasi rak.
                </p>
            </div>

            @if ($transfer->status === 'draft')
                <span class="w-fit rounded-full bg-yellow-100 px-3 py-1 text-sm font-medium text-yellow-700">
                    Draft
                </span>
            @elseif ($transfer->status === 'posted')
                <span class="w-fit rounded-full bg-green-100 px-3 py-1 text-sm font-medium text-green-700">
                    Posted
                </span>
            @else
                <span class="w-fit rounded-full bg-red-100 px-3 py-1 text-sm font-medium text-red-700">
                    {{ ucfirst($transfer->status) }}
                </span>
            @endif
        </div>

        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
            <div>
                <p class="text-sm text-gray-500">Nomor Transaksi</p>
                <p class="mt-1 font-semibold text-gray-800">
                    {{ $transfer->transaction_number }}
                </p>
            </div>

            <div>
                <p class="text-sm text-gray-500">Tanggal Transfer</p>
                <p class="mt-1 font-medium text-gray-800">
                    {{ \Carbon\Carbon::parse($transfer->transaction_date)->format('d-m-Y') }}
                </p>
            </div>

            <div>
                <p class="text-sm text-gray-500">Petugas</p>
                <p class="mt-1 font-medium text-gray-800">
                    {{ $transfer->creator->name ?? '-' }}
                </p>
            </div>

            <div>
                <p class="text-sm text-gray-500">Keterangan</p>
                <p class="mt-1 font-medium text-gray-800">
                    {{ $transfer->notes ?: '-' }}
                </p>
            </div>
        </div>
    </div>

    {{-- Detail Barang --}}
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-800">
                Detail Barang yang Dipindahkan
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                Daftar barang beserta batch dan lokasi rak asal serta tujuan.
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-600">
                            No.
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-600">
                            Produk
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-600">
                            Kode Batch
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-600">
                            Rak Asal
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-600">
                            Rak Tujuan
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-semibold uppercase text-gray-600">
                            Jumlah
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse ($transfer->details as $detail)
                        @foreach ($detail->batchDetails as $batchDetail)
                            <tr>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">
                                    {{ $loop->parent->iteration }}.{{ $loop->iteration }}
                                </td>

                                <td class="px-6 py-4">
                                    <p class="font-medium text-gray-800">
                                        {{ $detail->product->name ?? '-' }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        {{ $detail->product->code ?? '-' }}
                                    </p>
                                </td>

                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                                    {{ $batchDetail->sourceBatchStock->productBatch->batch_code ?? '-' }}
                                </td>

                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                                    {{ $batchDetail->sourceBatchStock->rackLocation->code ?? '-' }}
                                    -
                                    {{ $batchDetail->sourceBatchStock->rackLocation->name ?? '' }}
                                </td>

                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                                    {{ $batchDetail->destinationBatchStock->rackLocation->code ?? '-' }}
                                    -
                                    {{ $batchDetail->destinationBatchStock->rackLocation->name ?? '' }}
                                </td>

                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-semibold text-gray-800">
                                    {{ number_format((float) $batchDetail->quantity, 3, ',', '.') }}
                                    {{ $detail->product->baseUnit->symbol ?? '' }}
                                </td>
                            </tr>
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">
                                Belum ada detail barang.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Tombol Aksi --}}
    <div class="flex flex-col-reverse justify-between gap-3 sm:flex-row">
        <a href="{{ route('petugas.transfers.index') }}"
           class="rounded-lg border border-gray-300 px-5 py-2.5 text-center text-sm font-medium text-gray-700 hover:bg-gray-50">
            Kembali
        </a>

        @if ($transfer->status === 'draft')
            <form action="{{ route('petugas.transfers.post', $transfer->id) }}"
                  method="POST"
                  onsubmit="return confirm('Apakah Anda yakin ingin melakukan posting transfer ini? Stok pada rak asal dan tujuan akan diperbarui.');">
                @csrf

                <button type="submit"
                        class="w-full rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-blue-700 sm:w-auto">
                    Posting Transfer
                </button>
            </form>
        @endif
    </div>

</div>
@endsection