@extends('layouts.app')

@section('title', 'Dashboard Petugas - SiStock')
@section('page-title', 'Dashboard Petugas')

@section('content')

<div class="space-y-6">

    {{-- Header Dashboard --}}
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">
                Selamat Datang di SiStock
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                Dashboard monitoring persediaan dan aktivitas gudang.
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
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">
                            Total Produk Aktif
                        </p>
                        <h3 class="mt-2 text-3xl font-bold text-gray-800">
                            {{ number_format($stockSummary->total_products ?? 0, 0, ',', '.') }}
                        </h3>
                    </div>

                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-100 text-blue-600">
                        <i class="fas fa-boxes text-xl"></i>
                    </div>
                </div>
                <p class="mt-3 text-xs text-gray-400">
                    Produk yang terdaftar dan aktif
                </p>
            </div>

            {{-- Stok Menipis --}}
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">
                            Stok Menipis
                        </p>
                        <h3 class="mt-2 text-3xl font-bold text-amber-600">
                            {{ number_format($stockSummary->low_stock ?? 0, 0, ',', '.') }}
                        </h3>
                    </div>

                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-100 text-amber-600">
                        <i class="fas fa-exclamation-triangle text-xl"></i>
                    </div>
                </div>
                <p class="mt-3 text-xs text-gray-400">
                    Stok berada di bawah batas minimum
                </p>
            </div>

            {{-- Stok Habis --}}
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">
                            Stok Habis
                        </p>
                        <h3 class="mt-2 text-3xl font-bold text-red-600">
                            {{ number_format($stockSummary->out_of_stock ?? 0, 0, ',', '.') }}
                        </h3>
                    </div>

                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-red-100 text-red-600">
                        <i class="fas fa-box-open text-xl"></i>
                    </div>
                </div>
                <p class="mt-3 text-xs text-gray-400">
                    Produk dengan jumlah stok nol
                </p>
            </div>

            {{-- Batch Kedaluwarsa --}}
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">
                            Perhatian Kedaluwarsa
                        </p>
                        <h3 class="mt-2 text-3xl font-bold text-orange-600">
                            {{ number_format($expiringBatchCount ?? 0, 0, ',', '.') }}
                        </h3>
                    </div>

                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-orange-100 text-orange-600">
                        <i class="fas fa-calendar-times text-xl"></i>
                    </div>
                </div>
                <p class="mt-3 text-xs text-gray-400">
                    Batch kedaluwarsa atau ≤ 30 hari
                </p>
            </div>

        </div>
    </div>

    {{-- Aktivitas Stok Hari Ini --}}
    <div>
        <h3 class="mb-3 text-base font-semibold text-gray-800">
            Aktivitas Stok Hari Ini
        </h3>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-green-100 text-green-600">
                        <i class="fas fa-arrow-down text-xl"></i>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">
                            Akumulasi Stok Masuk
                        </p>
                        <h3 class="mt-1 text-2xl font-bold text-green-600">
                            {{ number_format((float) ($todayMovements->total_in ?? 0), 2, ',', '.') }}
                        </h3>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-red-100 text-red-600">
                        <i class="fas fa-arrow-up text-xl"></i>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">
                            Akumulasi Stok Keluar
                        </p>
                        <h3 class="mt-1 text-2xl font-bold text-red-600">
                            {{ number_format((float) ($todayMovements->total_out ?? 0), 2, ',', '.') }}
                        </h3>
                    </div>
                </div>
            </div>

        </div>

        <p class="mt-2 text-xs text-gray-400">
            Angka pergerakan merupakan akumulasi kuantitas dari berbagai satuan produk,
            bukan jumlah transaksi.
        </p>
    </div>

    {{-- Produk dan Monitoring Kedaluwarsa --}}
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">

        {{-- Produk Stok Menipis --}}
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

            <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4">
                <div>
                    <h3 class="font-semibold text-gray-800">
                        Produk Membutuhkan Perhatian
                    </h3>
                    <p class="mt-1 text-xs text-gray-500">
                        Lima produk dengan stok paling rendah
                    </p>
                </div>

                <span class="rounded-lg bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">
                    {{ $lowStockProducts->count() }} Produk
                </span>
            </div>

            @if ($lowStockProducts->isEmpty())
                <div class="px-5 py-10 text-center">
                    <i class="fas fa-check-circle text-3xl text-green-500"></i>
                    <p class="mt-3 text-sm font-medium text-gray-700">
                        Tidak ada produk yang membutuhkan perhatian.
                    </p>
                    <p class="mt-1 text-xs text-gray-400">
                        Seluruh stok produk berada di atas batas minimum.
                    </p>
                </div>
            @else
                <div class="divide-y divide-gray-100">

                    @foreach ($lowStockProducts as $product)
                        <div class="flex items-center justify-between gap-3 px-5 py-4">

                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-gray-800">
                                    {{ $product->name }}
                                </p>
                                <p class="mt-1 text-xs text-gray-500">
                                    {{ $product->code }}
                                </p>
                                <p class="mt-1 text-xs text-gray-400">
                                    Minimum:
                                    {{ number_format((float) $product->minimum_stock, 2, ',', '.') }}
                                </p>
                            </div>

                            <div class="shrink-0 text-right">
                                <p class="text-sm font-bold {{ (float) $product->total_stock <= 0 ? 'text-red-600' : 'text-amber-600' }}">
                                    {{ number_format((float) $product->total_stock, 2, ',', '.') }}
                                </p>

                                @if ((float) $product->total_stock <= 0)
                                    <span class="mt-1 inline-block rounded-full bg-red-100 px-2 py-1 text-xs font-medium text-red-700">
                                        Habis
                                    </span>
                                @else
                                    <span class="mt-1 inline-block rounded-full bg-amber-100 px-2 py-1 text-xs font-medium text-amber-700">
                                        Menipis
                                    </span>
                                @endif
                            </div>

                        </div>
                    @endforeach

                </div>
            @endif

            <div class="border-t border-gray-200 px-5 py-3">
                <a href="{{ route('petugas.stocks.index') }}"
                   class="text-sm font-medium text-blue-600 hover:text-blue-800">
                    Lihat seluruh stok
                    <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>

        </div>

        {{-- Monitoring Kedaluwarsa --}}
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

            <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4">
                <div>
                    <h3 class="font-semibold text-gray-800">
                        Monitoring Kedaluwarsa
                    </h3>
                    <p class="mt-1 text-xs text-gray-500">
                        Batch kedaluwarsa dan yang akan kedaluwarsa
                    </p>
                </div>

                <span class="rounded-lg bg-orange-100 px-3 py-1 text-xs font-semibold text-orange-700">
                    {{ $expiringBatchCount }} Batch
                </span>
            </div>

            @if ($expiringBatches->isEmpty())
                <div class="px-5 py-10 text-center">
                    <i class="fas fa-calendar-check text-3xl text-green-500"></i>
                    <p class="mt-3 text-sm font-medium text-gray-700">
                        Tidak ada batch yang mendekati kedaluwarsa.
                    </p>
                    <p class="mt-1 text-xs text-gray-400">
                        Tidak ditemukan batch dengan stok tersisa dalam periode 30 hari.
                    </p>
                </div>
            @else
                <div class="divide-y divide-gray-100">

                    @foreach ($expiringBatches as $batch)
                        @php
                            $expiryDate = \Carbon\Carbon::parse($batch->expiry_date)->startOfDay();
                            $daysLeft = (int) now()->startOfDay()->diffInDays($expiryDate, false);
                        @endphp

                        <div class="flex items-center justify-between gap-3 px-5 py-4">

                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-gray-800">
                                    {{ $batch->product_name }}
                                </p>

                                <p class="mt-1 text-xs text-gray-500">
                                    {{ $batch->product_code }}
                                    &middot;
                                    Batch {{ $batch->batch_code }}
                                </p>

                                <p class="mt-1 text-xs text-gray-400">
                                    ED: {{ $expiryDate->format('d/m/Y') }}
                                </p>
                            </div>

                            <div class="shrink-0 text-right">
                                <p class="text-sm font-semibold text-gray-700">
                                    {{ number_format((float) $batch->total_quantity, 2, ',', '.') }}
                                </p>

                                @if ($daysLeft < 0)
                                    <span class="mt-1 inline-block rounded-full bg-red-100 px-2 py-1 text-xs font-medium text-red-700">
                                        Kedaluwarsa
                                    </span>
                                @elseif ($daysLeft <= 7)
                                    <span class="mt-1 inline-block rounded-full bg-red-100 px-2 py-1 text-xs font-medium text-red-700">
                                        {{ $daysLeft }} hari lagi
                                    </span>
                                @else
                                    <span class="mt-1 inline-block rounded-full bg-orange-100 px-2 py-1 text-xs font-medium text-orange-700">
                                        {{ $daysLeft }} hari lagi
                                    </span>
                                @endif
                            </div>

                        </div>
                    @endforeach

                </div>
            @endif

        </div>

    </div>

    {{-- Ringkasan Stock Opname --}}
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

        <div class="flex flex-col gap-3 border-b border-gray-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="font-semibold text-gray-800">
                    Ringkasan Stock Opname
                </h3>
                <p class="mt-1 text-xs text-gray-500">
                    Status kegiatan pemeriksaan persediaan gudang
                </p>
            </div>

            <a href="{{ route('petugas.stock-opnames.index') }}"
               class="text-sm font-medium text-blue-600 hover:text-blue-800">
                Kelola Stock Opname
                <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>

        <div class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-3">

            <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                <p class="text-sm text-gray-500">
                    Draft
                </p>
                <h3 class="mt-2 text-2xl font-bold text-gray-700">
                    {{ number_format($stockOpnameSummary['draft'] ?? 0, 0, ',', '.') }}
                </h3>
                <p class="mt-1 text-xs text-gray-400">
                    Belum diajukan
                </p>
            </div>

            <div class="rounded-lg border border-amber-200 bg-amber-50 p-4">
                <p class="text-sm text-amber-700">
                    Menunggu Persetujuan
                </p>
                <h3 class="mt-2 text-2xl font-bold text-amber-700">
                    {{ number_format($stockOpnameSummary['submitted'] ?? 0, 0, ',', '.') }}
                </h3>
                <p class="mt-1 text-xs text-amber-600">
                    Menunggu pemeriksaan Admin
                </p>
            </div>

            <div class="rounded-lg border border-green-200 bg-green-50 p-4">
                <p class="text-sm text-green-700">
                    Selesai
                </p>
                <h3 class="mt-2 text-2xl font-bold text-green-700">
                    {{ number_format($stockOpnameSummary['completed'] ?? 0, 0, ',', '.') }}
                </h3>
                <p class="mt-1 text-xs text-green-600">
                    Stock Opname telah selesai
                </p>
            </div>

        </div>
    </div>

    {{-- Akses Cepat --}}
    <div>
        <h3 class="mb-3 text-base font-semibold text-gray-800">
            Akses Cepat
        </h3>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

            <a href="{{ route('petugas.stocks.index') }}"
               class="group flex items-center gap-4 rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition hover:border-blue-300 hover:shadow-md">

                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-blue-600 transition group-hover:bg-blue-600 group-hover:text-white">
                    <i class="fas fa-boxes text-xl"></i>
                </div>

                <div>
                    <h4 class="font-semibold text-gray-800">
                        Data Stok Barang
                    </h4>
                    <p class="mt-1 text-sm text-gray-500">
                        Lihat persediaan, batch, dan lokasi rak.
                    </p>
                </div>

                <i class="fas fa-chevron-right ml-auto text-gray-400"></i>
            </a>

            <a href="{{ route('petugas.stock-opnames.index') }}"
               class="group flex items-center gap-4 rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition hover:border-green-300 hover:shadow-md">

                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-green-100 text-green-600 transition group-hover:bg-green-600 group-hover:text-white">
                    <i class="fas fa-clipboard-check text-xl"></i>
                </div>

                <div>
                    <h4 class="font-semibold text-gray-800">
                        Stock Opname
                    </h4>
                    <p class="mt-1 text-sm text-gray-500">
                        Lakukan dan pantau pemeriksaan stok.
                    </p>
                </div>

                <i class="fas fa-chevron-right ml-auto text-gray-400"></i>
            </a>

        </div>
    </div>

</div>

@endsection