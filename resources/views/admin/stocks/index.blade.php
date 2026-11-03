@extends('layouts.app')

@section('title', 'Monitoring Stok Barang - SiStock')
@section('page-title', 'Monitoring Stok Barang')

@section('content')

<div class="space-y-6">

    {{-- HEADER --}}
    <div>
        <h2 class="text-xl font-semibold text-gray-800">
            Monitoring Stok Barang
        </h2>
        <p class="mt-1 text-sm text-gray-500">
            Pantau kondisi persediaan, ketersediaan stok, dan tanggal kedaluwarsa produk.
        </p>
    </div>


    {{-- RINGKASAN STOK --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

        {{-- Total Produk --}}
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-gray-500">
                    Total Produk
                </p>
                <div class="rounded-lg bg-blue-100 p-3 text-blue-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            stroke-width="2"
                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
            </div>

            <div class="mt-4">
                <h3 class="text-3xl font-bold text-gray-800">
                    {{ number_format($totalProducts, 0, ',', '.') }}
                </h3>
                <p class="mt-1 text-xs text-gray-500">
                    Produk aktif dalam sistem
                </p>
            </div>
        </div>


        {{-- Stok Aman --}}
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-gray-500">
                    Stok Aman
                </p>
                <div class="rounded-lg bg-green-100 p-3 text-green-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            stroke-width="2"
                            d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
            </div>

            <div class="mt-4">
                <h3 class="text-3xl font-bold text-green-600">
                    {{ number_format($safeProducts, 0, ',', '.') }}
                </h3>
                <p class="mt-1 text-xs text-gray-500">
                    Persediaan di atas stok minimum
                </p>
            </div>
        </div>


        {{-- Stok Menipis --}}
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-gray-500">
                    Stok Menipis
                </p>
                <div class="rounded-lg bg-yellow-100 p-3 text-yellow-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 9v4m0 4h.01M10.3 3.9L1.8 18.6A1.5 1.5 0 003.1 21h17.8a1.5 1.5 0 001.3-2.4L13.7 3.9a2 2 0 00-3.4 0z"/>
                    </svg>
                </div>
            </div>

            <div class="mt-4">
                <h3 class="text-3xl font-bold text-yellow-600">
                    {{ number_format($lowStockProducts, 0, ',', '.') }}
                </h3>
                <p class="mt-1 text-xs text-gray-500">
                    Stok sama dengan atau di bawah minimum
                </p>
            </div>
        </div>


        {{-- Stok Habis --}}
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-gray-500">
                    Stok Habis
                </p>
                <div class="rounded-lg bg-red-100 p-3 text-red-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </div>
            </div>

            <div class="mt-4">
                <h3 class="text-3xl font-bold text-red-600">
                    {{ number_format($outOfStockProducts, 0, ',', '.') }}
                </h3>
                <p class="mt-1 text-xs text-gray-500">
                    Produk tanpa persediaan
                </p>
            </div>
        </div>

    </div>


    {{-- GRAFIK DAN INFORMASI KEDALUWARSA --}}
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">

        {{-- DISTRIBUSI STOK --}}
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">

            <div>
                <h3 class="font-semibold text-gray-800">
                    Distribusi Kondisi Stok
                </h3>
                <p class="mt-1 text-xs text-gray-500">
                    Perbandingan jumlah produk berdasarkan status persediaan.
                </p>
            </div>

            @php
                $chartTotal = array_sum($stockChart['data']);

                $safePercentage = $chartTotal > 0
                    ? ($safeProducts / $chartTotal) * 100
                    : 0;

                $lowPercentage = $chartTotal > 0
                    ? ($lowStockProducts / $chartTotal) * 100
                    : 0;

                $outPercentage = $chartTotal > 0
                    ? ($outOfStockProducts / $chartTotal) * 100
                    : 0;
            @endphp

            <div class="mt-6">

                {{-- Progress Bar --}}
                <div class="flex h-5 w-full overflow-hidden rounded-full bg-gray-100">

                    @if ($safePercentage > 0)
                        <div
                            class="bg-green-500"
                            style="width: {{ $safePercentage }}%">
                        </div>
                    @endif

                    @if ($lowPercentage > 0)
                        <div
                            class="bg-yellow-400"
                            style="width: {{ $lowPercentage }}%">
                        </div>
                    @endif

                    @if ($outPercentage > 0)
                        <div
                            class="bg-red-500"
                            style="width: {{ $outPercentage }}%">
                        </div>
                    @endif

                </div>


                {{-- Legenda --}}
                <div class="mt-6 space-y-4">

                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="h-3 w-3 rounded-full bg-green-500"></span>
                            <span class="text-sm text-gray-600">
                                Stok Aman
                            </span>
                        </div>

                        <div class="text-right">
                            <span class="font-semibold text-gray-800">
                                {{ $safeProducts }}
                            </span>
                            <span class="ml-1 text-xs text-gray-400">
                                ({{ number_format($safePercentage, 1) }}%)
                            </span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="h-3 w-3 rounded-full bg-yellow-400"></span>
                            <span class="text-sm text-gray-600">
                                Stok Menipis
                            </span>
                        </div>

                        <div class="text-right">
                            <span class="font-semibold text-gray-800">
                                {{ $lowStockProducts }}
                            </span>
                            <span class="ml-1 text-xs text-gray-400">
                                ({{ number_format($lowPercentage, 1) }}%)
                            </span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="h-3 w-3 rounded-full bg-red-500"></span>
                            <span class="text-sm text-gray-600">
                                Stok Habis
                            </span>
                        </div>

                        <div class="text-right">
                            <span class="font-semibold text-gray-800">
                                {{ $outOfStockProducts }}
                            </span>
                            <span class="ml-1 text-xs text-gray-400">
                                ({{ number_format($outPercentage, 1) }}%)
                            </span>
                        </div>
                    </div>

                </div>

            </div>

        </div>


        {{-- KESEHATAN INVENTORY DAN FEEDBACK --}}
        <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">

            {{-- KESEHATAN INVENTORY --}}
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">

                <h3 class="font-semibold text-gray-800">
                    Kesehatan Inventory
                </h3>

                <p class="mt-1 text-xs text-gray-500">
                    Indikator kondisi persediaan berdasarkan jumlah produk dengan stok aman.
                </p>

                <div class="mt-6 flex items-center gap-5">

                    <div class="relative flex h-28 w-28 shrink-0 items-center justify-center rounded-full"
                        style="background: conic-gradient(
                            {{ $inventoryHealthColor === 'green' ? '#16a34a' : ($inventoryHealthColor === 'yellow' ? '#eab308' : '#dc2626') }}
                            {{ $inventoryHealth }}%,
                            #e5e7eb {{ $inventoryHealth }}%
                        );">

                        <div class="flex h-20 w-20 items-center justify-center rounded-full bg-white">
                            <span class="text-xl font-bold text-gray-800">
                                {{ number_format($inventoryHealth, 1) }}%
                            </span>
                        </div>

                    </div>

                    <div>
                        <span class="inline-flex rounded-full px-3 py-1 text-sm font-semibold
                            {{ $inventoryHealthColor === 'green' ? 'bg-green-100 text-green-700' : ($inventoryHealthColor === 'yellow' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                            {{ $inventoryHealthStatus }}
                        </span>

                        <p class="mt-2 text-sm text-gray-600">
                            {{ $safeProducts }} dari {{ $totalProducts }} produk memiliki stok aman.
                        </p>

                        <p class="mt-1 text-xs text-gray-400">
                            Berdasarkan perbandingan stok dengan batas minimum.
                        </p>
                    </div>

                </div>

                <div class="mt-6 grid grid-cols-3 gap-3 border-t border-gray-100 pt-4">

                    <div>
                        <p class="text-xs text-gray-500">Aman</p>
                        <p class="mt-1 font-bold text-green-600">
                            {{ $safeProducts }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs text-gray-500">Menipis</p>
                        <p class="mt-1 font-bold text-yellow-600">
                            {{ $lowStockProducts }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs text-gray-500">Habis</p>
                        <p class="mt-1 font-bold text-red-600">
                            {{ $outOfStockProducts }}
                        </p>
                    </div>

                </div>

            </div>


            {{-- FEEDBACK OTOMATIS --}}
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">

                <h3 class="font-semibold text-gray-800">
                    Feedback dan Rekomendasi
                </h3>

                <p class="mt-1 text-xs text-gray-500">
                    Saran tindakan berdasarkan kondisi persediaan gudang.
                </p>

                <div class="mt-5 space-y-3">

                    {{-- STOK HABIS --}}
                    @if ($outOfStockProducts > 0)

                        <div class="rounded-lg border border-red-200 bg-red-50 p-4">

                            <div class="flex gap-3">
                                <span class="text-xl">🚨</span>

                                <div>
                                    <p class="text-sm font-semibold text-red-800">
                                        Pengadaan Ulang Diperlukan
                                    </p>

                                    <p class="mt-1 text-xs leading-relaxed text-red-700">
                                        Terdapat {{ $outOfStockProducts }} produk yang stoknya habis.
                                        Disarankan segera melakukan pengadaan ulang untuk memenuhi kebutuhan gudang.
                                    </p>
                                </div>
                            </div>

                        </div>

                    @endif


                    {{-- STOK MENIPIS --}}
                    @if ($lowStockProducts > 0)

                        <div class="rounded-lg border border-yellow-200 bg-yellow-50 p-4">

                            <div class="flex gap-3">
                                <span class="text-xl">⚠️</span>

                                <div>
                                    <p class="text-sm font-semibold text-yellow-800">
                                        Stok Mendekati Batas Minimum
                                    </p>

                                    <p class="mt-1 text-xs leading-relaxed text-yellow-700">
                                        Terdapat {{ $lowStockProducts }} produk dengan stok menipis.
                                        Disarankan merencanakan pengadaan sebelum stok habis.
                                    </p>
                                </div>
                            </div>

                        </div>

                    @endif


                    {{-- BATCH KEDALUWARSA --}}
                    @if ($totalExpiredBatches > 0)

                        <div class="rounded-lg border border-red-200 bg-red-50 p-4">

                            <div class="flex gap-3">
                                <span class="text-xl">⛔</span>

                                <div>
                                    <p class="text-sm font-semibold text-red-800">
                                        Batch Sudah Kedaluwarsa
                                    </p>

                                    <p class="mt-1 text-xs leading-relaxed text-red-700">
                                        Terdapat {{ $totalExpiredBatches }} batch yang sudah melewati tanggal kedaluwarsa.
                                        Segera pisahkan dari stok yang layak digunakan dan lakukan pemeriksaan.
                                    </p>
                                </div>
                            </div>

                        </div>

                    @endif


                    {{-- BATCH SEGERA KEDALUWARSA --}}
                    @if ($totalExpiringBatches > 0)

                        <div class="rounded-lg border border-orange-200 bg-orange-50 p-4">

                            <div class="flex gap-3">
                                <span class="text-xl">📅</span>

                                <div>
                                    <p class="text-sm font-semibold text-orange-800">
                                        Perhatian Tanggal Kedaluwarsa
                                    </p>

                                    <p class="mt-1 text-xs leading-relaxed text-orange-700">
                                        Terdapat {{ $totalExpiringBatches }} batch yang akan kedaluwarsa dalam 30 hari.
                                        Prioritaskan penggunaan atau distribusi batch tersebut jika masih layak digunakan.
                                    </p>
                                </div>
                            </div>

                        </div>

                    @endif


                    {{-- KONDISI AMAN --}}
                    @if (
                        $outOfStockProducts == 0 &&
                        $lowStockProducts == 0 &&
                        $totalExpiredBatches == 0 &&
                        $totalExpiringBatches == 0
                    )

                        <div class="rounded-lg border border-green-200 bg-green-50 p-4">

                            <div class="flex gap-3">
                                <span class="text-xl">✅</span>

                                <div>
                                    <p class="text-sm font-semibold text-green-800">
                                        Persediaan dalam Kondisi Baik
                                    </p>

                                    <p class="mt-1 text-xs leading-relaxed text-green-700">
                                        Tidak ditemukan produk dengan stok menipis atau habis,
                                        serta tidak ada batch yang kedaluwarsa maupun mendekati kedaluwarsa.
                                        Pertahankan pemantauan stok secara berkala.
                                    </p>
                                </div>
                            </div>

                        </div>

                    @endif

                </div>

            </div>

        </div>

        {{-- MONITORING KEDALUWARSA --}}
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">

            {{-- HEADER --}}
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-semibold text-gray-800">
                        Monitoring Kedaluwarsa
                    </h3>
                    <p class="mt-1 text-xs text-gray-500">
                        Pemantauan batch berdasarkan tanggal kedaluwarsa.
                    </p>
                </div>

                <div class="rounded-lg bg-orange-100 p-3 text-orange-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>

            {{-- RINGKASAN --}}
            <div class="mt-5 grid grid-cols-2 gap-3">

                <div class="rounded-lg border border-red-100 bg-red-50 p-4">
                    <p class="text-xs font-medium text-red-700">
                        Sudah Kedaluwarsa
                    </p>

                    <p class="mt-2 text-2xl font-bold text-red-600">
                        {{ number_format($totalExpiredBatches, 0, ',', '.') }}
                    </p>

                    <p class="mt-1 text-xs text-red-600">
                        Batch melewati tanggal ED
                    </p>
                </div>

                <div class="rounded-lg border border-orange-100 bg-orange-50 p-4">
                    <p class="text-xs font-medium text-orange-700">
                        Akan Kedaluwarsa
                    </p>

                    <p class="mt-2 text-2xl font-bold text-orange-600">
                        {{ number_format($totalExpiringBatches, 0, ',', '.') }}
                    </p>

                    <p class="mt-1 text-xs text-orange-600">
                        Dalam 30 hari ke depan
                    </p>
                </div>

            </div>

            {{-- DAFTAR BATCH KEDALUWARSA --}}
            <div class="mt-6">

                <div class="flex items-center justify-between">
                    <h4 class="text-sm font-semibold text-red-700">
                        Batch Sudah Kedaluwarsa
                    </h4>

                    <span class="rounded-full bg-red-100 px-2.5 py-1 text-xs font-semibold text-red-700">
                        {{ $totalExpiredBatches }} Batch
                    </span>
                </div>

                <div class="mt-3 max-h-48 space-y-3 overflow-y-auto">

                    @forelse ($expiredBatches as $batch)

                        <div class="flex items-center justify-between gap-3 rounded-lg border border-red-100 bg-red-50/50 p-3">

                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium text-gray-800">
                                    {{ $batch->product_name }}
                                </p>

                                <p class="mt-1 text-xs text-gray-500">
                                    {{ $batch->product_code }}
                                    · Batch {{ $batch->batch_code }}
                                </p>
                            </div>

                            <div class="shrink-0 text-right">
                                <p class="text-sm font-semibold text-red-600">
                                    {{ \Carbon\Carbon::parse($batch->expiry_date)->format('d/m/Y') }}
                                </p>

                                <p class="mt-1 text-xs text-gray-500">
                                    Stok:
                                    {{ number_format((float) $batch->total_quantity, 3, ',', '.') }}
                                </p>
                            </div>

                        </div>

                    @empty

                        <div class="rounded-lg bg-green-50 p-3 text-center">
                            <p class="text-sm text-green-700">
                                Tidak ada batch yang sudah kedaluwarsa.
                            </p>
                        </div>

                    @endforelse

                </div>

            </div>

            {{-- DAFTAR BATCH AKAN KEDALUWARSA --}}
            <div class="mt-6 border-t border-gray-100 pt-5">

                <div class="flex items-center justify-between">
                    <h4 class="text-sm font-semibold text-orange-700">
                        Batch Akan Kedaluwarsa
                    </h4>

                    <span class="rounded-full bg-orange-100 px-2.5 py-1 text-xs font-semibold text-orange-700">
                        {{ $totalExpiringBatches }} Batch
                    </span>
                </div>

                <div class="mt-3 max-h-48 space-y-3 overflow-y-auto">

                    @forelse ($expiringBatches as $batch)

                        <div class="flex items-center justify-between gap-3 rounded-lg border border-orange-100 bg-orange-50/50 p-3">

                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium text-gray-800">
                                    {{ $batch->product_name }}
                                </p>

                                <p class="mt-1 text-xs text-gray-500">
                                    {{ $batch->product_code }}
                                    · Batch {{ $batch->batch_code }}
                                </p>
                            </div>

                            <div class="shrink-0 text-right">
                                <p class="text-sm font-semibold text-orange-600">
                                    {{ \Carbon\Carbon::parse($batch->expiry_date)->format('d/m/Y') }}
                                </p>

                                <p class="mt-1 text-xs text-gray-500">
                                    Stok:
                                    {{ number_format((float) $batch->total_quantity, 3, ',', '.') }}
                                </p>
                            </div>

                        </div>

                    @empty

                        <div class="rounded-lg bg-green-50 p-3 text-center">
                            <p class="text-sm text-green-700">
                                Tidak ada batch yang akan kedaluwarsa dalam 30 hari.
                            </p>
                        </div>

                    @endforelse

                </div>

            </div>

        </div>


    {{-- FILTER MONITORING --}}
    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">

        <div class="mb-4">
            <h3 class="font-semibold text-gray-800">
                Filter Persediaan
            </h3>
            <p class="mt-1 text-xs text-gray-500">
                Cari produk atau tampilkan persediaan berdasarkan kategori.
            </p>
        </div>

        <form action="{{ route('admin.stocks.index') }}"
              method="GET"
              class="grid grid-cols-1 gap-4 md:grid-cols-3">

            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">
                    Cari Produk
                </label>

                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Kode, nama, atau batch..."
                    class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                >
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">
                    Kategori
                </label>

                <select
                    name="category_id"
                    class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                >
                    <option value="">Semua Kategori</option>

                    @foreach ($categories as $category)
                        <option
                            value="{{ $category->id }}"
                            @selected(request('category_id') == $category->id)
                        >
                            {{ $category->name }}
                        </option>
                    @endforeach

                </select>
            </div>

            <div class="flex items-end gap-2">

                <button
                    type="submit"
                    class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-blue-700"
                >
                    Cari
                </button>

                <a
                    href="{{ route('admin.stocks.index') }}"
                    class="rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50"
                >
                    Reset
                </a>

            </div>

        </form>

    </div>


    {{-- TABEL MONITORING --}}
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

        <div class="border-b border-gray-100 p-5">
            <h3 class="font-semibold text-gray-800">
                Daftar Monitoring Persediaan
            </h3>

            <p class="mt-1 text-xs text-gray-500">
                Informasi persediaan produk beserta detail batch dan lokasi rak.
            </p>
        </div>

        <div class="overflow-x-auto">

            <table class="w-full text-left text-sm text-gray-600">

                <thead class="bg-gray-50 text-gray-700">
                    <tr>
                        <th class="px-5 py-4">No.</th>
                        <th class="px-5 py-4">Kode Produk</th>
                        <th class="px-5 py-4">Nama Produk</th>
                        <th class="px-5 py-4">Kategori</th>
                        <th class="px-5 py-4">Total Stok</th>
                        <th class="px-5 py-4">Stok Minimum</th>
                        <th class="px-5 py-4">Status</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">

                    @forelse ($products as $product)

                        @php
                            $totalStock = (float) $product->total_stock;

                            if ($totalStock <= 0) {
                                $stockStatus = 'Habis';
                            } elseif ($totalStock <= $product->minimum_stock) {
                                $stockStatus = 'Menipis';
                            } else {
                                $stockStatus = 'Aman';
                            }

                            $unitSymbol = $product->baseUnit->symbol ?? '';
                        @endphp

                        {{-- DATA PRODUK --}}
                        <tr class="hover:bg-gray-50">

                            <td class="px-5 py-4">
                                {{ $products->firstItem() + $loop->index }}
                            </td>

                            <td class="px-5 py-4 font-medium text-gray-800">
                                {{ $product->code }}
                            </td>

                            <td class="px-5 py-4 font-medium text-gray-800">
                                {{ $product->name }}
                            </td>

                            <td class="px-5 py-4">
                                {{ $product->category->name ?? '-' }}
                            </td>

                            <td class="px-5 py-4 font-semibold text-gray-800 whitespace-nowrap">
                                {{ number_format($totalStock, 3, ',', '.') }}
                                {{ $unitSymbol }}
                            </td>

                            <td class="px-5 py-4 whitespace-nowrap">
                                {{ number_format((float) $product->minimum_stock, 3, ',', '.') }}
                                {{ $unitSymbol }}
                            </td>

                            <td class="px-5 py-4">

                                @if ($stockStatus === 'Habis')

                                    <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-medium text-red-700">
                                        Habis
                                    </span>

                                @elseif ($stockStatus === 'Menipis')

                                    <span class="rounded-full bg-yellow-100 px-3 py-1 text-xs font-medium text-yellow-700">
                                        Menipis
                                    </span>

                                @else

                                    <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-700">
                                        Aman
                                    </span>

                                @endif

                            </td>

                        </tr>


                        {{-- DETAIL BATCH --}}
                        <tr class="bg-gray-50">

                            <td colspan="7" class="px-5 py-4">

                                <p class="mb-3 text-xs font-semibold text-gray-600">
                                    DETAIL BATCH DAN LOKASI RAK
                                </p>

                                @php
                                    $hasBatchStock = $product->batches
                                        ->contains(fn ($batch) => $batch->batchStocks->isNotEmpty());
                                @endphp

                                @if ($hasBatchStock)

                                    <div class="overflow-x-auto">

                                        <table class="w-full text-left text-xs">

                                            <thead class="text-gray-500">
                                                <tr>
                                                    <th class="py-2 pr-4">Kode Batch</th>
                                                    <th class="py-2 pr-4">Kedaluwarsa</th>
                                                    <th class="py-2 pr-4">Lokasi Rak</th>
                                                    <th class="py-2 pr-4">Jumlah Stok</th>
                                                </tr>
                                            </thead>

                                            <tbody>

                                                @foreach ($product->batches as $batch)

                                                    @foreach ($batch->batchStocks as $batchStock)

                                                        <tr class="border-t border-gray-200">

                                                            <td class="py-2 pr-4">
                                                                {{ $batch->batch_code }}
                                                            </td>

                                                            <td class="py-2 pr-4">
                                                                {{ $batch->expiry_date?->format('d-m-Y') ?? '-' }}
                                                            </td>

                                                            <td class="py-2 pr-4">
                                                                {{ $batchStock->rackLocation->code ?? '-' }}
                                                                -
                                                                {{ $batchStock->rackLocation->name ?? '-' }}
                                                            </td>

                                                            <td class="py-2 pr-4 font-medium text-gray-700 whitespace-nowrap">
                                                                {{ number_format((float) $batchStock->quantity, 3, ',', '.') }}
                                                                {{ $unitSymbol }}
                                                            </td>

                                                        </tr>

                                                    @endforeach

                                                @endforeach

                                            </tbody>

                                        </table>

                                    </div>

                                @else

                                    <p class="text-xs text-gray-400">
                                        Belum ada stok tersedia untuk produk ini.
                                    </p>

                                @endif

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="7" class="px-5 py-10 text-center text-gray-400">
                                Data produk tidak ditemukan.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        {{-- PAGINATION --}}
        @if ($products->hasPages())
            <div class="border-t border-gray-100 px-5 py-4">
                {{ $products->links() }}
            </div>
        @endif

    </div>

</div>

@endsection