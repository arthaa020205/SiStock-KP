@extends('layouts.app')

@section('title', 'Stok Barang - SiStock')
@section('page-title', 'Stok Barang')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h2 class="text-xl font-bold text-gray-800">
                Persediaan Barang
            </h2>
            <p class="text-sm text-gray-500 mt-1">
                Pantau jumlah stok, lokasi penyimpanan, dan masa kedaluwarsa produk.
            </p>
        </div>

        <div class="text-sm text-gray-500">
            <span class="font-medium text-gray-700">Terakhir diperbarui:</span>
            {{ now()->format('d M Y, H:i') }}
        </div>
    </div>

    {{-- Ringkasan Stok --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4">

        {{-- Total Produk --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Total Produk</p>
                    <p class="text-2xl font-bold text-gray-800 mt-2">
                        {{ number_format($summary->total_products ?? 0, 0, ',', '.') }}
                    </p>
                    <p class="text-xs text-gray-400 mt-1">Produk aktif</p>
                </div>

                <div class="w-11 h-11 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                              d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Total Stok --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Total Persediaan</p>
                    <p class="text-2xl font-bold text-gray-800 mt-2">
                        {{ number_format((float) ($summary->total_stock ?? 0), 3, ',', '.') }}
                    </p>
                    <p class="text-xs text-gray-400 mt-1">Akumulasi seluruh stok</p>
                </div>

                <div class="w-11 h-11 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                              d="M4 7h16M4 12h16M4 17h16"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Stok Aman --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Stok Aman</p>
                    <p class="text-2xl font-bold text-green-600 mt-2">
                        {{ number_format(
                            max(
                                0,
                                ($summary->total_products ?? 0)
                                - ($summary->low_stock ?? 0)
                                - ($summary->out_of_stock ?? 0)
                            ),
                            0,
                            ',',
                            '.'
                        ) }}
                    </p>
                    <p class="text-xs text-gray-400 mt-1">Di atas stok minimum</p>
                </div>

                <div class="w-11 h-11 rounded-lg bg-green-50 flex items-center justify-center text-green-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                              d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Stok Menipis --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Stok Menipis</p>
                    <p class="text-2xl font-bold text-yellow-600 mt-2">
                        {{ number_format($summary->low_stock ?? 0, 0, ',', '.') }}
                    </p>
                    <p class="text-xs text-gray-400 mt-1">Mencapai batas minimum</p>
                </div>

                <div class="w-11 h-11 rounded-lg bg-yellow-50 flex items-center justify-center text-yellow-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                              d="M12 9v4m0 4h.01M10.3 3.9L2.6 17.2A2 2 0 004.3 20h15.4a2 2 0 001.7-2.8L13.7 3.9a2 2 0 00-3.4 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Stok Habis --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Stok Habis</p>
                    <p class="text-2xl font-bold text-red-600 mt-2">
                        {{ number_format($summary->out_of_stock ?? 0, 0, ',', '.') }}
                    </p>
                    <p class="text-xs text-gray-400 mt-1">Tidak tersedia</p>
                </div>

                <div class="w-11 h-11 rounded-lg bg-red-50 flex items-center justify-center text-red-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                              d="M6 6l12 12M18 6L6 18"/>
                    </svg>
                </div>
            </div>
        </div>

    </div>

    {{-- Informasi Kedaluwarsa --}}
    <div class="bg-orange-50 border border-orange-200 rounded-xl p-4 flex flex-col sm:flex-row sm:items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-orange-100 text-orange-600 flex items-center justify-center shrink-0">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                      d="M12 8v4l3 2m6-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>

        <div>
            <p class="font-semibold text-orange-800">
                Perhatian Masa Kedaluwarsa
            </p>
            <p class="text-sm text-orange-700 mt-1">
                Terdapat
                <span class="font-bold">
                    {{ number_format($expiringBatches ?? 0, 0, ',', '.') }} batch
                </span>
                yang sudah kedaluwarsa atau akan kedaluwarsa dalam 30 hari ke depan.
                Periksa detail batch untuk mengetahui produk dan lokasi penyimpanannya.
            </p>
        </div>
    </div>

    {{-- Filter --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">

        <div class="mb-4">
            <h3 class="font-semibold text-gray-800">
                Pencarian dan Filter
            </h3>
            <p class="text-xs text-gray-500 mt-1">
                Gunakan pencarian atau kategori untuk menemukan produk.
            </p>
        </div>

        <form action="{{ route('petugas.stocks.index') }}"
              method="GET"
              class="grid grid-cols-1 md:grid-cols-3 gap-4">

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Cari Produk
                </label>

                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Kode, nama, atau kode batch..."
                    class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500"
                >
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Kategori
                </label>

                <select
                    name="category_id"
                    class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500"
                >
                    <option value="">Semua Kategori</option>

                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}"
                            {{ request('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button
                    type="submit"
                    class="px-5 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition"
                >
                    Cari Produk
                </button>

                <a
                    href="{{ route('petugas.stocks.index') }}"
                    class="px-5 py-2.5 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition"
                >
                    Reset
                </a>
            </div>

        </form>

        @if (request()->filled('search') || request()->filled('category_id'))
            <div class="mt-4 pt-3 border-t border-gray-100 flex flex-wrap items-center gap-2 text-xs text-gray-500">
                <span>Filter aktif:</span>

                @if (request()->filled('search'))
                    <span class="px-2.5 py-1 bg-blue-50 text-blue-700 rounded-full">
                        Pencarian: {{ request('search') }}
                    </span>
                @endif

                @if (request()->filled('category_id'))
                    <span class="px-2.5 py-1 bg-indigo-50 text-indigo-700 rounded-full">
                        Kategori:
                        {{ $categories->firstWhere('id', request('category_id'))?->name ?? 'Tidak diketahui' }}
                    </span>
                @endif
            </div>
        @endif

    </div>

    {{-- Daftar Stok --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">

        <div class="p-5 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h3 class="font-semibold text-gray-800">
                    Daftar Persediaan Barang
                </h3>
                <p class="text-xs text-gray-500 mt-1">
                    Klik tombol detail pada produk untuk melihat batch dan lokasi rak.
                </p>
            </div>

            <span class="text-xs text-gray-500">
                Menampilkan {{ $products->firstItem() ?? 0 }}–{{ $products->lastItem() ?? 0 }}
                dari {{ $products->total() }} produk
            </span>
        </div>

        <div class="overflow-x-auto">

            <table class="w-full text-sm text-left text-gray-600">

                <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                    <tr>
                        <th class="px-5 py-4 whitespace-nowrap">No.</th>
                        <th class="px-5 py-4 whitespace-nowrap">Kode Produk</th>
                        <th class="px-5 py-4 min-w-48">Nama Produk</th>
                        <th class="px-5 py-4 whitespace-nowrap">Kategori</th>
                        <th class="px-5 py-4 whitespace-nowrap text-right">Total Stok</th>
                        <th class="px-5 py-4 whitespace-nowrap text-right">Stok Minimum</th>
                        <th class="px-5 py-4 whitespace-nowrap text-center">Status</th>
                        <th class="px-5 py-4 whitespace-nowrap text-center">Detail</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">

                    @forelse ($products as $product)

                        @php
                            $totalStock = $product->batches
                                ->flatMap(fn ($batch) => $batch->batchStocks)
                                ->sum('quantity');

                            if ($totalStock <= 0) {
                                $stockStatus = 'Habis';
                            } elseif ($totalStock <= $product->minimum_stock) {
                                $stockStatus = 'Menipis';
                            } else {
                                $stockStatus = 'Aman';
                            }

                            $detailId = 'batch-detail-' . $product->id;

                            $batchCount = $product->batches
                                ->filter(fn ($batch) => $batch->batchStocks->isNotEmpty())
                                ->count();
                        @endphp

                        <tr class="hover:bg-blue-50/40 transition">

                            <td class="px-5 py-4 text-gray-400">
                                {{ $products->firstItem() + $loop->index }}
                            </td>

                            <td class="px-5 py-4 font-medium text-gray-800 whitespace-nowrap">
                                {{ $product->code }}
                            </td>

                            <td class="px-5 py-4">
                                <p class="font-semibold text-gray-800">
                                    {{ $product->name }}
                                </p>
                                <p class="text-xs text-gray-400 mt-1">
                                    {{ $batchCount }} batch tersedia
                                </p>
                            </td>

                            <td class="px-5 py-4">
                                {{ $product->category->name ?? '-' }}
                            </td>

                            <td class="px-5 py-4 text-right whitespace-nowrap">
                                <span class="font-bold text-gray-800">
                                    {{ number_format((float) $totalStock, 3, ',', '.') }}
                                </span>
                                <span class="text-xs text-gray-500">
                                    {{ $product->baseUnit->symbol ?? '' }}
                                </span>
                            </td>

                            <td class="px-5 py-4 text-right whitespace-nowrap">
                                {{ number_format((float) $product->minimum_stock, 3, ',', '.') }}
                                <span class="text-xs text-gray-500">
                                    {{ $product->baseUnit->symbol ?? '' }}
                                </span>
                            </td>

                            <td class="px-5 py-4 text-center">
                                @if ($stockStatus === 'Habis')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                        Habis
                                    </span>
                                @elseif ($stockStatus === 'Menipis')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-yellow-500"></span>
                                        Menipis
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                                        Aman
                                    </span>
                                @endif
                            </td>

                            <td class="px-5 py-4 text-center">
                                <button
                                    type="button"
                                    onclick="toggleBatchDetail('{{ $detailId }}')"
                                    class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-medium text-blue-700 bg-blue-50 hover:bg-blue-100 rounded-lg transition"
                                >
                                    <i class="fas fa-boxes"></i>
                                    Lihat Batch
                                </button>
                            </td>

                        </tr>

                        {{-- Detail Batch --}}
                        <tr>
                            <td colspan="8" class="p-0">
                                <div
                                    id="{{ $detailId }}"
                                    class="hidden bg-gray-50 border-t border-gray-100 px-5 py-5"
                                >

                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-4">
                                        <div>
                                            <h4 class="font-semibold text-gray-800 text-sm">
                                                Detail Batch dan Lokasi Penyimpanan
                                            </h4>
                                            <p class="text-xs text-gray-500 mt-1">
                                                {{ $product->code }} — {{ $product->name }}
                                            </p>
                                        </div>

                                        <span class="text-xs text-gray-500">
                                            {{ $batchCount }} batch dengan stok tersedia
                                        </span>
                                    </div>

                                    @if ($batchCount > 0)

                                        <div class="overflow-x-auto bg-white rounded-lg border border-gray-200">
                                            <table class="w-full text-xs text-left">

                                                <thead class="bg-gray-100 text-gray-600 uppercase">
                                                    <tr>
                                                        <th class="px-4 py-3">Kode Batch</th>
                                                        <th class="px-4 py-3">Tanggal Kedaluwarsa</th>
                                                        <th class="px-4 py-3">Lokasi Rak</th>
                                                        <th class="px-4 py-3 text-right">Sisa Stok</th>
                                                        <th class="px-4 py-3 text-center">Status ED</th>
                                                    </tr>
                                                </thead>

                                                <tbody class="divide-y divide-gray-100">

                                                    @foreach ($product->batches as $batch)
                                                        @foreach ($batch->batchStocks as $batchStock)

                                                            @php
                                                                $expiryStatus = 'Tidak diketahui';
                                                                $expiryClass = 'bg-gray-100 text-gray-600';

                                                                if ($batch->expiry_date) {
                                                                    $today = now()->startOfDay();
                                                                    $expiryDate = $batch->expiry_date->copy()->startOfDay();
                                                                    $daysRemaining = $today->diffInDays($expiryDate, false);

                                                                    if ($daysRemaining < 0) {
                                                                        $expiryStatus = 'Kedaluwarsa';
                                                                        $expiryClass = 'bg-red-100 text-red-700';
                                                                    } elseif ($daysRemaining <= 7) {
                                                                        $expiryStatus = '≤ 7 Hari';
                                                                        $expiryClass = 'bg-red-100 text-red-700';
                                                                    } elseif ($daysRemaining <= 30) {
                                                                        $expiryStatus = '≤ 30 Hari';
                                                                        $expiryClass = 'bg-yellow-100 text-yellow-700';
                                                                    } else {
                                                                        $expiryStatus = 'Aman';
                                                                        $expiryClass = 'bg-green-100 text-green-700';
                                                                    }
                                                                }
                                                            @endphp

                                                            <tr class="hover:bg-gray-50">

                                                                <td class="px-4 py-3 font-medium text-gray-800 whitespace-nowrap">
                                                                    {{ $batch->batch_code }}
                                                                </td>

                                                                <td class="px-4 py-3 whitespace-nowrap">
                                                                    {{ $batch->expiry_date?->format('d-m-Y') ?? '-' }}
                                                                </td>

                                                                <td class="px-4 py-3">
                                                                    <span class="font-medium text-gray-700">
                                                                        {{ $batchStock->rackLocation->code ?? '-' }}
                                                                    </span>
                                                                    <span class="text-gray-400">
                                                                        — {{ $batchStock->rackLocation->name ?? 'Lokasi tidak diketahui' }}
                                                                    </span>
                                                                </td>

                                                                <td class="px-4 py-3 text-right font-semibold text-gray-800 whitespace-nowrap">
                                                                    {{ number_format((float) $batchStock->quantity, 3, ',', '.') }}
                                                                    {{ $product->baseUnit->symbol ?? '' }}
                                                                </td>

                                                                <td class="px-4 py-3 text-center whitespace-nowrap">
                                                                    <span class="px-2.5 py-1 rounded-full font-semibold {{ $expiryClass }}">
                                                                        {{ $expiryStatus }}
                                                                    </span>
                                                                </td>

                                                            </tr>

                                                        @endforeach
                                                    @endforeach

                                                </tbody>

                                            </table>
                                        </div>

                                    @else

                                        <div class="bg-white border border-dashed border-gray-300 rounded-lg p-6 text-center">
                                            <p class="text-sm font-medium text-gray-600">
                                                Belum ada stok tersedia.
                                            </p>
                                            <p class="text-xs text-gray-400 mt-1">
                                                Produk ini belum memiliki batch dengan jumlah stok lebih dari nol.
                                            </p>
                                        </div>

                                    @endif

                                </div>
                            </td>
                        </tr>

                    @empty

                        <tr>
                            <td colspan="8" class="px-5 py-14 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center text-gray-400 mb-3">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                  d="M10 10l4 4m0-4l-4 4m10-2a8 8 0 11-16 0 8 8 0 0116 0z"/>
                                        </svg>
                                    </div>

                                    <p class="font-semibold text-gray-700">
                                        Data produk tidak ditemukan
                                    </p>
                                    <p class="text-sm text-gray-400 mt-1">
                                        Coba ubah kata pencarian atau filter kategori.
                                    </p>

                                    @if (request()->filled('search') || request()->filled('category_id'))
                                        <a href="{{ route('petugas.stocks.index') }}"
                                           class="mt-3 text-sm text-blue-600 hover:text-blue-700 font-medium">
                                            Hapus semua filter
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        {{-- Pagination --}}
        @if ($products->hasPages())
            <div class="px-5 py-4 border-t border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <p class="text-xs text-gray-500">
                    Halaman {{ $products->currentPage() }}
                    dari {{ $products->lastPage() }}
                </p>

                {{ $products->links() }}
            </div>
        @endif

    </div>

    {{-- Keterangan Status --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h3 class="font-semibold text-gray-800 text-sm mb-3">
            Keterangan Status Persediaan
        </h3>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">

            <div class="flex items-start gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-green-500 mt-0.5 shrink-0"></span>
                <div>
                    <p class="font-semibold text-gray-700">Aman</p>
                    <p class="text-gray-500 mt-1">
                        Jumlah stok berada di atas batas stok minimum.
                    </p>
                </div>
            </div>

            <div class="flex items-start gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-yellow-500 mt-0.5 shrink-0"></span>
                <div>
                    <p class="font-semibold text-gray-700">Menipis</p>
                    <p class="text-gray-500 mt-1">
                        Jumlah stok sama dengan atau di bawah batas minimum, tetapi masih tersedia.
                    </p>
                </div>
            </div>

            <div class="flex items-start gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-red-500 mt-0.5 shrink-0"></span>
                <div>
                    <p class="font-semibold text-gray-700">Habis</p>
                    <p class="text-gray-500 mt-1">
                        Tidak terdapat stok tersedia untuk produk tersebut.
                    </p>
                </div>
            </div>

        </div>
    </div>

</div>

<script>
    function toggleBatchDetail(id) {
        const element = document.getElementById(id);

        if (element) {
            element.classList.toggle('hidden');
        }
    }
</script>
@endsection
