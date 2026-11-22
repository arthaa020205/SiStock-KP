@extends('layouts.app')

@section('title', 'Tambah Konversi Satuan - SiStock')
@section('page-title', 'Tambah Konversi Satuan')

@section('content')

<div class="max-w-3xl">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Tambah Konversi Satuan</h2>
        <p class="text-gray-500 mt-1">
            Tentukan satuan tambahan, faktor konversi, dan harga jual produk.
        </p>
    </div>

    <form action="{{ route('petugas.unit-conversions.store') }}" method="POST"
        class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-5">
        @csrf

        {{-- Produk --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Produk
            </label>

            <select name="product_id"
                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                required>
                <option value="">-- Pilih Produk --</option>

                @foreach($products as $product)
                    <option value="{{ $product->id }}"
                        {{ old('product_id') == $product->id ? 'selected' : '' }}>
                        {{ $product->code }} - {{ $product->name }}
                        (Dasar: {{ $product->baseUnit->symbol ?? '-' }})
                    </option>
                @endforeach
            </select>

            @error('product_id')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror

            @if($products->isEmpty())
                <p class="text-orange-600 text-sm mt-1">
                    Belum ada produk aktif. Tambahkan produk terlebih dahulu.
                </p>
            @endif
        </div>

        {{-- Satuan Konversi --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Satuan Konversi
            </label>

            <select name="unit_id"
                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                required>
                <option value="">-- Pilih Satuan --</option>

                @foreach($units as $unit)
                    <option value="{{ $unit->id }}"
                        {{ old('unit_id') == $unit->id ? 'selected' : '' }}>
                        {{ $unit->name }} ({{ $unit->symbol }})
                    </option>
                @endforeach
            </select>

            @error('unit_id')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Faktor Konversi --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Faktor Konversi
            </label>

            <input type="number"
                name="conversion_factor"
                value="{{ old('conversion_factor') }}"
                min="0.001"
                step="0.001"
                placeholder="Contoh: 1000"
                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                required>

            <p class="text-gray-500 text-xs mt-1">
                Masukkan jumlah satuan dasar untuk setiap 1 satuan konversi.
            </p>

            @error('conversion_factor')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Harga Jual --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Harga Jual Satuan Konversi
            </label>

            <div class="relative">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500">
                    Rp
                </span>

                <input type="number"
                    name="selling_price"
                    value="{{ old('selling_price') }}"
                    min="0"
                    step="0.01"
                    placeholder="Contoh: 150000"
                    class="w-full border border-gray-300 rounded-lg pl-11 pr-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>

            <p class="text-gray-500 text-xs mt-1">
                Harga jual untuk setiap 1 satuan konversi.
                Kosongkan jika harga belum ditentukan.
            </p>

            @error('selling_price')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Informasi --}}
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 space-y-2">
            <p class="text-sm font-semibold text-blue-800">
                Informasi Konversi dan Harga
            </p>

            <p class="text-sm text-blue-800">
                Faktor konversi digunakan untuk menghitung jumlah stok dalam satuan dasar.
                Harga jual digunakan untuk menentukan harga transaksi penjualan.
            </p>

            <p class="text-sm text-blue-800">
                Contoh: Jika satuan dasar produk adalah Pcs, faktor konversi 1.000,
                dan harga jual Rp1.500.000, maka 1 satuan konversi berisi 1.000 Pcs
                dengan harga jual Rp1.500.000.
            </p>
        </div>

        {{-- Tombol --}}
        <div class="flex gap-3 pt-2">
            <button type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg text-sm">
                Simpan
            </button>

            <a href="{{ route('petugas.unit-conversions.index') }}"
                class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-5 py-2.5 rounded-lg text-sm">
                Kembali
            </a>
        </div>
    </form>
</div>

@endsection