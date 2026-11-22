@extends('layouts.app')

@section('title', 'Tambah Produk - SiStock')
@section('page-title', 'Tambah Produk')

@section('content')

<div class="max-w-3xl">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Tambah Produk</h2>
        <p class="text-gray-500 mt-1">Masukkan informasi produk baru.</p>
    </div>

    <form action="{{ route('petugas.products.store') }}" method="POST"
        class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-5">
        @csrf

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Kode Produk</label>
            <input type="text" name="code" value="{{ old('code') }}"
                placeholder="Contoh: PL001"
                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                required>
            @error('code')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Nama Produk</label>
            <input type="text" name="name" value="{{ old('name') }}"
                placeholder="Contoh: Mika 500 ml"
                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                required>
            @error('name')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Kategori</label>
            <select name="category_id"
                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                required>
                <option value="">-- Pilih Kategori --</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}"
                        {{ old('category_id') == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
            @error('category_id')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror

            @if($categories->isEmpty())
                <p class="text-orange-600 text-sm mt-1">
                    Belum ada kategori aktif. Tambahkan kategori terlebih dahulu.
                </p>
            @endif
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Satuan Dasar</label>
            <select name="base_unit_id"
                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                required>
                <option value="">-- Pilih Satuan --</option>
                @foreach($units as $unit)
                    <option value="{{ $unit->id }}"
                        {{ old('base_unit_id') == $unit->id ? 'selected' : '' }}>
                        {{ $unit->name }} ({{ $unit->symbol }})
                    </option>
                @endforeach
            </select>
            @error('base_unit_id')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror

            @if($units->isEmpty())
                <p class="text-orange-600 text-sm mt-1">
                    Belum ada satuan. Tambahkan satuan terlebih dahulu.
                </p>
            @endif
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Stok Minimum</label>
            <input type="number" name="minimum_stock"
                value="{{ old('minimum_stock', 0) }}"
                min="0" step="0.001"
                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                required>
            <p class="text-gray-500 text-xs mt-1">
                Batas minimum stok menggunakan satuan dasar produk.
            </p>
            @error('minimum_stock')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- HARGA JUAL --}}
        <div class="border-t border-gray-200 pt-5">
            <h3 class="text-lg font-semibold text-gray-800 mb-1">Harga Jual Produk</h3>
            <p class="text-sm text-gray-500 mb-4">
                Tentukan harga jual berdasarkan satuan penjualan.
            </p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Harga Jual per Pcs (Rp)
                    </label>
                    <input type="number" name="price_per_pcs"
                        value="{{ old('price_per_pcs', 0) }}"
                        min="0" step="0.01"
                        placeholder="Contoh: 1500"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        required>
                    @error('price_per_pcs')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Harga Jual per Pack (Rp)
                    </label>
                    <input type="number" name="price_per_pack"
                        value="{{ old('price_per_pack', 0) }}"
                        min="0" step="0.01"
                        placeholder="Contoh: 15000"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        required>
                    @error('price_per_pack')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
            <textarea name="description" rows="3"
                placeholder="Deskripsi produk (opsional)"
                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none">{{ old('description') }}</textarea>
            @error('description')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Status Produk</label>
            <select name="is_active"
                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                required>
                <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>Aktif</option>
                <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>Tidak Aktif</option>
            </select>
            @error('is_active')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex flex-wrap gap-3 pt-2">
            <button type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg text-sm">
                Simpan
            </button>

            <a href="{{ route('petugas.products.index') }}"
                class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-5 py-2.5 rounded-lg text-sm">
                Kembali
            </a>
        </div>
    </form>
</div>

@endsection