@extends('layouts.app')

@section('title', 'Dashboard Admin - SiStock')
@section('page-title', 'Dashboard Admin')

@section('content')

<div class="space-y-6">

    {{-- Header Dashboard --}}
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">
                Selamat Datang di SiStock
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Berikut ringkasan kondisi persediaan dan aktivitas gudang.
            </p>
        </div>

        <div class="text-sm text-gray-500">
            {{ now()->translatedFormat('l, d F Y') }}
        </div>
    </div>

    {{-- Ringkasan Persediaan --}}
    <div>
        <h3 class="mb-3 text-base font-semibold text-gray-800">
            Ringkasan Persediaan
        </h3>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

            {{-- Total Produk --}}
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-medium text-gray-500">
                            Total Produk Aktif
                        </p>

                        <h3 class="mt-2 text-3xl font-bold text-gray-800">
                            {{ number_format($totalProducts, 0, ',', '.') }}
                        </h3>
                    </div>

                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-blue-600">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                </div>

                <p class="mt-3 text-xs text-gray-400">
                    Produk yang terdaftar dan aktif.
                </p>
            </div>

            {{-- Batch Aktif --}}
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-medium text-gray-500">
                            Batch Aktif
                        </p>

                        <h3 class="mt-2 text-3xl font-bold text-gray-800">
                            {{ number_format($totalActiveBatches, 0, ',', '.') }}
                        </h3>
                    </div>

                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-green-100 text-green-600">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 7.5L12 3l8 4.5M4 7.5v9L12 21m-8-13.5l8 4.5m8-4.5v9L12 21m8-13.5L12 12m0 0v9"/>
                        </svg>
                    </div>
                </div>

                <p class="mt-3 text-xs text-gray-400">
                    Batch yang masih memiliki persediaan.
                </p>
            </div>

            {{-- Stok Menipis --}}
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-medium text-gray-500">
                            Stok Menipis
                        </p>

                        <h3 class="mt-2 text-3xl font-bold text-amber-600">
                            {{ number_format($lowStockProducts->count(), 0, ',', '.') }}
                        </h3>
                    </div>

                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-600">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v4m0 4h.01M10.3 3.86L1.82 18a2 2 0 001.7 3h16.96a2 2 0 001.7-3L13.7 3.86a2 2 0 00-3.4 0z"/>
                        </svg>
                    </div>
                </div>

                <p class="mt-3 text-xs text-gray-400">
                    Produk dengan stok di bawah batas minimum.
                </p>
            </div>

            {{-- Stok Habis --}}
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-medium text-gray-500">
                            Stok Habis
                        </p>

                        <h3 class="mt-2 text-3xl font-bold text-red-600">
                            {{ number_format($outOfStockProducts->count(), 0, ',', '.') }}
                        </h3>
                    </div>

                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-red-100 text-red-600">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </div>
                </div>

                <p class="mt-3 text-xs text-gray-400">
                    Produk yang tidak memiliki persediaan.
                </p>
            </div>

        </div>
    </div>

    {{-- Daftar Stok Menipis dan Stok Habis --}}
    <div>
        <h3 class="mb-3 text-base font-semibold text-gray-800">
            Monitoring Kondisi Stok
        </h3>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">

            {{-- Stok Menipis --}}
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

                <div class="flex items-center justify-between gap-3 border-b border-gray-200 px-5 py-4">
                    <div>
                        <h3 class="font-semibold text-gray-800">
                            Produk dengan Stok Menipis
                        </h3>

                        <p class="mt-1 text-xs text-gray-500">
                            Lima produk dengan stok terendah.
                        </p>
                    </div>

                    <span class="shrink-0 rounded-lg bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">
                        {{ $lowStockProducts->count() }} Produk
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                            <tr>
                                <th class="whitespace-nowrap px-5 py-3">Produk</th>
                                <th class="whitespace-nowrap px-5 py-3">Stok</th>
                                <th class="whitespace-nowrap px-5 py-3">Minimum</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100">
                            @forelse ($lowStockProducts->take(5) as $product)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-5 py-3">
                                        <p class="font-medium text-gray-800">
                                            {{ $product->name }}
                                        </p>

                                        <p class="text-xs text-gray-500">
                                            {{ $product->code }}
                                        </p>
                                    </td>

                                    <td class="whitespace-nowrap px-5 py-3 font-semibold text-amber-600">
                                        {{ number_format((float) $product->total_stock, 3, ',', '.') }}
                                        {{ $product->baseUnit->name ?? '' }}
                                    </td>

                                    <td class="whitespace-nowrap px-5 py-3 text-gray-600">
                                        {{ number_format((float) $product->minimum_stock, 3, ',', '.') }}
                                        {{ $product->baseUnit->name ?? '' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-5 py-10 text-center">
                                        <svg class="mx-auto h-9 w-9 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.291 9 11.622C17.176 19.291 21 14.591 21 9c0-1.064-.138-2.095-.382-3.076z"/>
                                        </svg>

                                        <p class="mt-3 text-sm font-medium text-gray-700">
                                            Tidak ada produk dengan stok menipis.
                                        </p>

                                        <p class="mt-1 text-xs text-gray-400">
                                            Stok seluruh produk berada di atas batas minimum.
                                        </p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>

            {{-- Stok Habis --}}
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

                <div class="flex items-center justify-between gap-3 border-b border-gray-200 px-5 py-4">
                    <div>
                        <h3 class="font-semibold text-gray-800">
                            Produk dengan Stok Habis
                        </h3>

                        <p class="mt-1 text-xs text-gray-500">
                            Produk yang tidak memiliki persediaan.
                        </p>
                    </div>

                    <span class="shrink-0 rounded-lg bg-red-100 px-3 py-1 text-xs font-semibold text-red-700">
                        {{ $outOfStockProducts->count() }} Produk
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                            <tr>
                                <th class="whitespace-nowrap px-5 py-3">Produk</th>
                                <th class="whitespace-nowrap px-5 py-3">Stok</th>
                                <th class="whitespace-nowrap px-5 py-3">Minimum</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100">
                            @forelse ($outOfStockProducts->take(5) as $product)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-5 py-3">
                                        <p class="font-medium text-gray-800">
                                            {{ $product->name }}
                                        </p>

                                        <p class="text-xs text-gray-500">
                                            {{ $product->code }}
                                        </p>
                                    </td>

                                    <td class="whitespace-nowrap px-5 py-3 font-semibold text-red-600">
                                        0 {{ $product->baseUnit->name ?? '' }}
                                    </td>

                                    <td class="whitespace-nowrap px-5 py-3 text-gray-600">
                                        {{ number_format((float) $product->minimum_stock, 3, ',', '.') }}
                                        {{ $product->baseUnit->name ?? '' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-5 py-10 text-center">
                                        <svg class="mx-auto h-9 w-9 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.291 9 11.622C17.176 19.291 21 14.591 21 9c0-1.064-.138-2.095-.382-3.076z"/>
                                        </svg>

                                        <p class="mt-3 text-sm font-medium text-gray-700">
                                            Tidak ada produk dengan stok habis.
                                        </p>

                                        <p class="mt-1 text-xs text-gray-400">
                                            Seluruh produk masih memiliki persediaan.
                                        </p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>

        </div>
    </div>

    {{-- Riwayat Pergerakan Stok --}}
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

        <div class="border-b border-gray-200 px-5 py-4">
            <h3 class="font-semibold text-gray-800">
                Riwayat Pergerakan Stok Terbaru
            </h3>

            <p class="mt-1 text-xs text-gray-500">
                10 aktivitas pergerakan stok terakhir di gudang.
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">

                <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                    <tr>
                        <th class="whitespace-nowrap px-5 py-3">Tanggal</th>
                        <th class="whitespace-nowrap px-5 py-3">Produk</th>
                        <th class="whitespace-nowrap px-5 py-3">Kode Batch</th>
                        <th class="whitespace-nowrap px-5 py-3">Lokasi Rak</th>
                        <th class="whitespace-nowrap px-5 py-3">Jenis</th>
                        <th class="whitespace-nowrap px-5 py-3">Jumlah</th>
                        <th class="whitespace-nowrap px-5 py-3">Petugas</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">

                    @forelse ($latestMovements as $movement)

                        @php
                            $movementLabels = [
                                'in' => 'Barang Masuk',
                                'out' => 'Barang Keluar',
                                'transfer_in' => 'Transfer Masuk',
                                'transfer_out' => 'Transfer Keluar',
                                'adjustment_in' => 'Penyesuaian Masuk',
                                'adjustment_out' => 'Penyesuaian Keluar',
                            ];

                            $increaseTypes = ['in', 'transfer_in', 'adjustment_in'];
                            $isIncrease = in_array($movement->movement_type, $increaseTypes);
                        @endphp

                        <tr class="transition hover:bg-gray-50">

                            <td class="whitespace-nowrap px-5 py-3 text-gray-600">
                                {{ $movement->created_at->format('d/m/Y H:i') }}
                            </td>

                            <td class="min-w-[180px] px-5 py-3">
                                <p class="font-medium text-gray-800">
                                    {{ $movement->batchStock->productBatch->product->name ?? '-' }}
                                </p>

                                <p class="text-xs text-gray-500">
                                    {{ $movement->batchStock->productBatch->product->code ?? '-' }}
                                </p>
                            </td>

                            <td class="whitespace-nowrap px-5 py-3 text-gray-600">
                                {{ $movement->batchStock->productBatch->batch_code ?? '-' }}
                            </td>

                            <td class="whitespace-nowrap px-5 py-3 text-gray-600">
                                {{ $movement->batchStock->rackLocation->code ?? '-' }}
                            </td>

                            <td class="whitespace-nowrap px-5 py-3">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium
                                    {{ $isIncrease ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                    {{ $movementLabels[$movement->movement_type] ?? ucfirst($movement->movement_type) }}
                                </span>
                            </td>

                            <td class="whitespace-nowrap px-5 py-3 font-semibold
                                {{ $isIncrease ? 'text-green-600' : 'text-red-600' }}">
                                {{ $isIncrease ? '+' : '-' }}{{ number_format((float) $movement->quantity, 3, ',', '.') }}
                            </td>

                            <td class="whitespace-nowrap px-5 py-3 text-gray-600">
                                {{ $movement->creator->name ?? '-' }}
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="7" class="px-5 py-10 text-center">
                                <p class="text-sm font-medium text-gray-700">
                                    Belum ada riwayat pergerakan stok.
                                </p>

                                <p class="mt-1 text-xs text-gray-400">
                                    Aktivitas barang masuk dan keluar akan ditampilkan di sini.
                                </p>
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>
        </div>

    </div>

</div>

@endsection