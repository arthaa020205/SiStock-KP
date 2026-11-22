@extends('layouts.app')

@section('title', 'Monitoring Stok Barang - SiStock')
@section('page-title', 'Monitoring Stok Barang')

@section('content')

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

    $healthColor = match ($inventoryHealthColor) {
        'green' => '#16a34a',
        'yellow' => '#eab308',
        default => '#dc2626',
    };

    $healthBadge = match ($inventoryHealthColor) {
        'green' => 'bg-green-100 text-green-700',
        'yellow' => 'bg-yellow-100 text-yellow-700',
        default => 'bg-red-100 text-red-700',
    };
@endphp

<div class="space-y-6">

    {{-- HEADER --}}
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-800 sm:text-2xl">
                Monitoring Stok Barang
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Pantau kondisi persediaan, ketersediaan stok, dan tanggal kedaluwarsa produk.
            </p>
        </div>

        <div class="text-xs text-gray-500">
            Pembaruan: {{ now()->translatedFormat('d F Y, H:i') }}
        </div>
    </div>


    {{-- RINGKASAN STOK --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

        {{-- TOTAL PRODUK --}}
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-gray-500">
                    Total Produk
                </p>

                <div class="rounded-lg bg-blue-100 p-3 text-blue-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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


        {{-- STOK AMAN --}}
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-gray-500">
                    Stok Aman
                </p>

                <div class="rounded-lg bg-green-100 p-3 text-green-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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


        {{-- STOK MENIPIS --}}
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-gray-500">
                    Stok Menipis
                </p>

                <div class="rounded-lg bg-yellow-100 p-3 text-yellow-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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


        {{-- STOK HABIS --}}
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-gray-500">
                    Stok Habis
                </p>

                <div class="rounded-lg bg-red-100 p-3 text-red-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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


    {{-- DISTRIBUSI DAN KESEHATAN INVENTORY --}}
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">

        {{-- DISTRIBUSI KONDISI STOK --}}
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">

            <div>
                <h3 class="font-semibold text-gray-800">
                    Distribusi Kondisi Stok
                </h3>

                <p class="mt-1 text-xs text-gray-500">
                    Perbandingan jumlah produk berdasarkan status persediaan.
                </p>
            </div>

            {{-- PROGRESS BAR --}}
            <div class="mt-6">
                @if ($totalProducts > 0)

                    <div class="flex h-5 w-full overflow-hidden rounded-full bg-gray-100">
                        @if ($safePercentage > 0)
                            <div class="bg-green-500"
                                style="width: {{ $safePercentage }}%">
                            </div>
                        @endif

                        @if ($lowPercentage > 0)
                            <div class="bg-yellow-400"
                                style="width: {{ $lowPercentage }}%">
                            </div>
                        @endif

                        @if ($outPercentage > 0)
                            <div class="bg-red-500"
                                style="width: {{ $outPercentage }}%">
                            </div>
                        @endif
                    </div>

                @else

                    <div class="flex h-5 w-full items-center justify-center rounded-full bg-gray-100">
                        <span class="text-xs text-gray-400">
                            Belum ada data produk
                        </span>
                    </div>

                @endif
            </div>

            {{-- LEGENDA --}}
            <div class="mt-6 space-y-4">

                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <span class="h-3 w-3 shrink-0 rounded-full bg-green-500"></span>
                        <span class="text-sm text-gray-600">Stok Aman</span>
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

                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <span class="h-3 w-3 shrink-0 rounded-full bg-yellow-400"></span>
                        <span class="text-sm text-gray-600">Stok Menipis</span>
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

                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <span class="h-3 w-3 shrink-0 rounded-full bg-red-500"></span>
                        <span class="text-sm text-gray-600">Stok Habis</span>
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


        {{-- KESEHATAN INVENTORY --}}
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">

            <div>
                <h3 class="font-semibold text-gray-800">
                    Kesehatan Inventory
                </h3>

                <p class="mt-1 text-xs text-gray-500">
                    Indikator kondisi persediaan berdasarkan jumlah produk dengan stok aman.
                </p>
            </div>

            <div class="mt-6 flex flex-col gap-5 sm:flex-row sm:items-center">

                {{-- INDIKATOR LINGKARAN --}}
                <div class="relative flex h-28 w-28 shrink-0 items-center justify-center self-center rounded-full"
                    style="background: conic-gradient(
                        {{ $healthColor }} {{ $inventoryHealth }}%,
                        #e5e7eb {{ $inventoryHealth }}%
                    );">

                    <div class="flex h-20 w-20 items-center justify-center rounded-full bg-white">
                        <span class="text-xl font-bold text-gray-800">
                            {{ number_format($inventoryHealth, 1) }}%
                        </span>
                    </div>

                </div>

                <div class="min-w-0">
                    <span class="inline-flex rounded-full px-3 py-1 text-sm font-semibold {{ $healthBadge }}">
                        {{ $inventoryHealthStatus }}
                    </span>

                    <p class="mt-2 text-sm text-gray-600">
                        @if ($totalProducts > 0)
                            {{ $safeProducts }} dari {{ $totalProducts }} produk memiliki stok aman.
                        @else
                            Belum terdapat produk aktif untuk dianalisis.
                        @endif
                    </p>

                    <p class="mt-1 text-xs text-gray-400">
                        Berdasarkan perbandingan stok dengan batas minimum.
                    </p>
                </div>

            </div>

            {{-- RINGKASAN STATUS --}}
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

    </div>


    {{-- FEEDBACK DAN REKOMENDASI --}}
    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">

        <div>
            <h3 class="font-semibold text-gray-800">
                Feedback dan Rekomendasi
            </h3>

            <p class="mt-1 text-xs text-gray-500">
                Saran tindakan berdasarkan kondisi persediaan gudang.
            </p>
        </div>

        <div class="mt-5 grid grid-cols-1 gap-3 lg:grid-cols-2">

            {{-- BELUM ADA DATA --}}
            @if ($totalProducts == 0)

                <div class="rounded-lg border border-yellow-200 bg-yellow-50 p-4">
                    <div class="flex gap-3">
                        <span class="text-xl">ℹ️</span>

                        <div>
                            <p class="text-sm font-semibold text-yellow-800">
                                Belum Ada Data Inventory
                            </p>

                            <p class="mt-1 text-xs leading-relaxed text-yellow-700">
                                Belum terdapat produk aktif yang dapat dimonitor.
                                Tambahkan produk terlebih dahulu untuk memulai pemantauan inventory.
                            </p>
                        </div>
                    </div>
                </div>

            @else

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
                                    Disarankan segera melakukan pengadaan ulang.
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
                                    Rencanakan pengadaan sebelum stok habis.
                                </p>
                            </div>
                        </div>
                    </div>

                @endif

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
                                Pisahkan dan lakukan pemeriksaan sebelum digunakan atau didistribusikan.
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
                                Prioritaskan pemeriksaan dan distribusi batch tersebut jika masih layak digunakan.
                            </p>
                        </div>
                    </div>
                </div>

            @endif


            {{-- KONDISI AMAN --}}
            @if (
                $totalProducts > 0 &&
                $outOfStockProducts == 0 &&
                $lowStockProducts == 0 &&
                $totalExpiredBatches == 0 &&
                $totalExpiringBatches == 0
            )

                <div class="rounded-lg border border-green-200 bg-green-50 p-4 lg:col-span-2">
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


    {{-- MONITORING KEDALUWARSA --}}
    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">

        <div class="flex items-center justify-between gap-3">
            <div>
                <h3 class="font-semibold text-gray-800">
                    Monitoring Kedaluwarsa
                </h3>

                <p class="mt-1 text-xs text-gray-500">
                    Pemantauan batch berdasarkan tanggal kedaluwarsa.
                </p>
            </div>

            <div class="rounded-lg bg-orange-100 p-3 text-orange-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>

        {{-- RINGKASAN --}}
        <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2">

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
                    Hari ini sampai 30 hari ke depan
                </p>
            </div>

        </div>


        {{-- BATCH SUDAH KEDALUWARSA --}}
        <div class="mt-6">

            <div class="flex flex-wrap items-center justify-between gap-2">
                <h4 class="text-sm font-semibold text-red-700">
                    Batch Sudah Kedaluwarsa
                </h4>

                <span class="rounded-full bg-red-100 px-2.5 py-1 text-xs font-semibold text-red-700">
                    {{ $totalExpiredBatches }} Batch
                </span>
            </div>

            <div class="mt-3 max-h-64 space-y-3 overflow-y-auto">

                @forelse ($expiredBatches as $batch)

                    <div class="flex flex-col gap-3 rounded-lg border border-red-100 bg-red-50/50 p-3 sm:flex-row sm:items-center sm:justify-between">

                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium text-gray-800">
                                {{ $batch->product_name }}
                            </p>

                            <p class="mt-1 text-xs text-gray-500">
                                {{ $batch->product_code }}
                                · Batch {{ $batch->batch_code }}
                            </p>
                        </div>

                        <div class="shrink-0 text-left sm:text-right">
                            <p class="text-sm font-semibold text-red-600">
                                {{ \Carbon\Carbon::parse($batch->expiry_date)->format('d/m/Y') }}
                            </p>

                            <p class="mt-1 text-xs text-gray-500">
                                Stok: {{ number_format((float) $batch->total_quantity, 3, ',', '.') }}
                            </p>
                        </div>

                    </div>

                @empty

                    <div class="rounded-lg bg-green-50 p-4 text-center">
                        <p class="text-sm text-green-700">
                            Tidak ada batch yang sudah kedaluwarsa.
                        </p>
                    </div>

                @endforelse

            </div>

        </div>


        {{-- BATCH AKAN KEDALUWARSA --}}
        <div class="mt-6 border-t border-gray-100 pt-5">

            <div class="flex flex-wrap items-center justify-between gap-2">
                <h4 class="text-sm font-semibold text-orange-700">
                    Batch Akan Kedaluwarsa
                </h4>

                <span class="rounded-full bg-orange-100 px-2.5 py-1 text-xs font-semibold text-orange-700">
                    {{ $totalExpiringBatches }} Batch
                </span>
            </div>

            <div class="mt-3 max-h-64 space-y-3 overflow-y-auto">

                @forelse ($expiringBatches as $batch)

                    <div class="flex flex-col gap-3 rounded-lg border border-orange-100 bg-orange-50/50 p-3 sm:flex-row sm:items-center sm:justify-between">

                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium text-gray-800">
                                {{ $batch->product_name }}
                            </p>

                            <p class="mt-1 text-xs text-gray-500">
                                {{ $batch->product_code }}
                                · Batch {{ $batch->batch_code }}
                            </p>
                        </div>

                        <div class="shrink-0 text-left sm:text-right">
                            <p class="text-sm font-semibold text-orange-600">
                                {{ \Carbon\Carbon::parse($batch->expiry_date)->format('d/m/Y') }}
                            </p>

                            <p class="mt-1 text-xs text-gray-500">
                                Stok: {{ number_format((float) $batch->total_quantity, 3, ',', '.') }}
                            </p>
                        </div>

                    </div>

                @empty

                    <div class="rounded-lg bg-green-50 p-4 text-center">
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
                    class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500"
                >
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">
                    Kategori
                </label>

                <select
                    name="category_id"
                    class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500"
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

            <div class="flex flex-col items-stretch gap-2 sm:flex-row sm:items-end">

                <button
                    type="submit"
                    class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-blue-700"
                >
                    Cari
                </button>

                <a
                    href="{{ route('admin.stocks.index') }}"
                    class="rounded-lg border border-gray-300 px-5 py-2.5 text-center text-sm font-medium text-gray-700 transition hover:bg-gray-50"
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

            <table class="w-full min-w-[850px] text-left text-sm text-gray-600">

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
                            $minimumStock = (float) $product->minimum_stock;

                            if ($totalStock <= 0) {
                                $stockStatus = 'Habis';
                                $statusClass = 'bg-red-100 text-red-700';
                            } elseif ($totalStock <= $minimumStock) {
                                $stockStatus = 'Menipis';
                                $statusClass = 'bg-yellow-100 text-yellow-700';
                            } else {
                                $stockStatus = 'Aman';
                                $statusClass = 'bg-green-100 text-green-700';
                            }

                            $unitSymbol = $product->baseUnit->symbol ?? '-';

                            $hasBatchStock = $product->batches->contains(
                                fn ($batch) => $batch->batchStocks->isNotEmpty()
                            );
                        @endphp

                        {{-- DATA PRODUK --}}
                        <tr class="transition hover:bg-gray-50">

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

                            <td class="whitespace-nowrap px-5 py-4 font-semibold text-gray-800">
                                {{ number_format($totalStock, 3, ',', '.') }}
                                {{ $unitSymbol }}
                            </td>

                            <td class="whitespace-nowrap px-5 py-4">
                                {{ number_format($minimumStock, 3, ',', '.') }}
                                {{ $unitSymbol }}
                            </td>

                            <td class="px-5 py-4">
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass }}">
                                    {{ $stockStatus }}
                                </span>
                            </td>

                        </tr>


                        {{-- DETAIL BATCH --}}
                        <tr class="bg-gray-50">

                            <td colspan="7" class="px-5 py-4">

                                <p class="mb-3 text-xs font-semibold text-gray-600">
                                    DETAIL BATCH DAN LOKASI RAK
                                </p>

                                @if ($hasBatchStock)

                                    <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white">

                                        <table class="w-full min-w-[600px] text-left text-xs">

                                            <thead class="bg-gray-50 text-gray-500">
                                                <tr>
                                                    <th class="px-4 py-3">Kode Batch</th>
                                                    <th class="px-4 py-3">Kedaluwarsa</th>
                                                    <th class="px-4 py-3">Lokasi Rak</th>
                                                    <th class="px-4 py-3">Jumlah Stok</th>
                                                </tr>
                                            </thead>

                                            <tbody class="divide-y divide-gray-100">

                                                @foreach ($product->batches as $batch)

                                                    @foreach ($batch->batchStocks as $batchStock)

                                                        <tr>

                                                            <td class="px-4 py-3 font-medium text-gray-700">
                                                                {{ $batch->batch_code }}
                                                            </td>

                                                            <td class="px-4 py-3">
                                                                @if ($batch->expiry_date)
                                                                    {{ $batch->expiry_date->format('d-m-Y') }}
                                                                @else
                                                                    -
                                                                @endif
                                                            </td>

                                                            <td class="px-4 py-3">
                                                                {{ $batchStock->rackLocation->code ?? '-' }}
                                                                -
                                                                {{ $batchStock->rackLocation->name ?? '-' }}
                                                            </td>

                                                            <td class="whitespace-nowrap px-4 py-3 font-medium text-gray-700">
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

                                    <div class="rounded-lg border border-dashed border-gray-300 bg-white p-4 text-center">
                                        <p class="text-xs text-gray-400">
                                            Belum ada stok tersedia untuk produk ini.
                                        </p>
                                    </div>

                                @endif

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="7" class="px-5 py-10 text-center">

                                <div class="flex flex-col items-center">
                                    <svg class="h-10 w-10 text-gray-300" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            stroke-width="1.5"
                                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                    </svg>

                                    <p class="mt-3 text-sm font-medium text-gray-600">
                                        Data produk tidak ditemukan.
                                    </p>

                                    <p class="mt-1 text-xs text-gray-400">
                                        Coba ubah kata kunci atau filter kategori.
                                    </p>
                                </div>

                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        {{-- PAGINATION --}}
        @if ($products->hasPages())
            <div class="flex flex-col gap-3 border-t border-gray-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">

                <p class="text-xs text-gray-500">
                    Menampilkan {{ $products->firstItem() }}–{{ $products->lastItem() }}
                    dari {{ $products->total() }} produk.
                </p>

                <div>
                    {{ $products->links() }}
                </div>

            </div>
        @endif

    </div>

</div>

@endsection