@extends('layouts.app')

@section('title', 'Stok Barang - SiStock')
@section('page-title', 'Stok Barang')

@section('content')
<div class="space-y-6">

    <div>
        <h2 class="text-xl font-semibold text-gray-800">
            Data Stok Barang
        </h2>

        <p class="text-sm text-gray-500 mt-1">
            Pantau jumlah persediaan berdasarkan produk, batch, dan lokasi rak.
        </p>
    </div>

    {{-- Ringkasan --}}
    @php
        $totalProducts = $products->total();
        $lowStockCount = 0;
        $outOfStockCount = 0;

        foreach ($products as $product) {
            $totalStock = $product->batches
                ->flatMap(fn ($batch) => $batch->batchStocks)
                ->sum('quantity');

            if ($totalStock <= 0) {
                $outOfStockCount++;
            } elseif ($totalStock <= $product->minimum_stock) {
                $lowStockCount++;
            }
        }
    @endphp

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

        <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
            <p class="text-sm text-gray-500">Produk pada halaman ini</p>
            <p class="text-2xl font-bold text-gray-800 mt-2">
                {{ $totalProducts }}
            </p>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
            <p class="text-sm text-gray-500">Stok Menipis pada halaman ini</p>
            <p class="text-2xl font-bold text-yellow-600 mt-2">
                {{ $lowStockCount }}
            </p>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
            <p class="text-sm text-gray-500">Stok Habis pada halaman ini</p>
            <p class="text-2xl font-bold text-red-600 mt-2">
                {{ $outOfStockCount }}
            </p>
        </div>

    </div>

    {{-- Filter --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">

        <form action="{{ route('petugas.stocks.index') }}"
              method="GET"
              class="grid grid-cols-1 md:grid-cols-3 gap-4">

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Cari Produk
                </label>

                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Kode, nama, atau batch..."
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Kategori
                </label>

                <select name="category_id"
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">

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

                <button type="submit"
                        class="px-5 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                    Cari
                </button>

                <a href="{{ route('petugas.stocks.index') }}"
                   class="px-5 py-2.5 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50">
                    Reset
                </a>

            </div>

        </form>

    </div>

    {{-- Tabel Stok --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">

        <div class="p-5 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800">
                Daftar Persediaan
            </h3>
        </div>

        <div class="overflow-x-auto">

            <table class="w-full text-sm text-left text-gray-600">

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
                        @endphp

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

                            <td class="px-5 py-4 font-semibold text-gray-800">
                                {{ number_format((float) $totalStock, 3, ',', '.') }}
                                {{ $product->baseUnit->symbol ?? '' }}
                            </td>

                            <td class="px-5 py-4">
                                {{ number_format((float) $product->minimum_stock, 3, ',', '.') }}
                                {{ $product->baseUnit->symbol ?? '' }}
                            </td>

                            <td class="px-5 py-4">
                                @if ($stockStatus === 'Habis')
                                    <span class="px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700">
                                        Habis
                                    </span>
                                @elseif ($stockStatus === 'Menipis')
                                    <span class="px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700">
                                        Menipis
                                    </span>
                                @else
                                    <span class="px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                        Aman
                                    </span>
                                @endif
                            </td>

                        </tr>

                        {{-- Detail Batch --}}
                        <tr class="bg-gray-50">
                            <td colspan="7" class="px-5 py-4">

                                <p class="text-xs font-semibold text-gray-600 mb-3">
                                    DETAIL BATCH DAN LOKASI RAK
                                </p>

                                @php
                                    $hasBatchStock = $product->batches
                                        ->contains(fn ($batch) => $batch->batchStocks->isNotEmpty());
                                @endphp

                                @if ($hasBatchStock)

                                    <div class="overflow-x-auto">
                                        <table class="w-full text-xs text-left">

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

                                                            <td class="py-2 pr-4 font-medium text-gray-700">
                                                                {{ number_format((float) $batchStock->quantity, 3, ',', '.') }}
                                                                {{ $product->baseUnit->symbol ?? '' }}
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

        @if ($products->hasPages())
            <div class="px-5 py-4 border-t border-gray-100">
                {{ $products->links() }}
            </div>
        @endif

    </div>

</div>
@endsection