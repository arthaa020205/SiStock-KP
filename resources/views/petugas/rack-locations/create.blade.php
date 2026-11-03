@extends('layouts.app')

@section('title', 'Tambah Lokasi Rak - SiStock')
@section('page-title', 'Tambah Lokasi Rak')

@section('content')

<div class="max-w-3xl">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Tambah Lokasi Rak</h2>
        <p class="text-gray-500 mt-1">Masukkan informasi lokasi penyimpanan baru.</p>
    </div>

    <form action="{{ route('petugas.rack-locations.store') }}" method="POST"
        class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-5">
        @csrf

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Kode Rak
            </label>

            <input type="text" name="code"
                value="{{ old('code') }}"
                placeholder="Contoh: RAK-A01"
                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                required>

            @error('code')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Nama Rak
            </label>

            <input type="text" name="name"
                value="{{ old('name') }}"
                placeholder="Contoh: Rak Penyimpanan A01"
                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                required>

            @error('name')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Keterangan
            </label>

            <textarea name="description" rows="3"
                placeholder="Keterangan lokasi rak (opsional)"
                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none">{{ old('description') }}</textarea>

            @error('description')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Status
            </label>

            <select name="is_active"
                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                required>
                <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>
                    Aktif
                </option>
                <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>
                    Tidak Aktif
                </option>
            </select>

            @error('is_active')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg text-sm">
                Simpan
            </button>

            <a href="{{ route('petugas.rack-locations.index') }}"
                class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-5 py-2.5 rounded-lg text-sm">
                Kembali
            </a>
        </div>
    </form>
</div>

@endsection