
@extends('layouts.app')

@section('title', 'Tambah Customer - SiStock')
@section('page-title', 'Tambah Customer')

@section('content')

<div class="max-w-3xl bg-white rounded-xl shadow-sm border border-gray-200 p-6">

    <h2 class="text-xl font-bold mb-6">Form Tambah Customer</h2>

    <form action="{{ route('petugas.customers.store') }}" method="POST">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

            <div>
                <label class="block text-sm font-medium mb-2">Nama Customer *</label>
                <input type="text" name="name"
                    value="{{ old('name') }}"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2"
                    placeholder="Masukkan nama customer">
                @error('name')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Nomor Telepon</label>
                <input type="text" name="phone"
                    value="{{ old('phone') }}"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2"
                    placeholder="Nomor telepon">
                @error('phone')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium mb-2">Email</label>
                <input type="email" name="email"
                    value="{{ old('email') }}"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2"
                    placeholder="customer@email.com">
                @error('email')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium mb-2">Alamat</label>
                <textarea name="address" rows="3"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2"
                    placeholder="Alamat customer">{{ old('address') }}</textarea>
                @error('address')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium mb-2">Status</label>
                <select name="is_active"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2">
                    <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>Aktif</option>
                    <option value="0" {{ old('is_active') === '0' ? 'selected' : '' }}>Tidak Aktif</option>
                </select>
                @error('is_active')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

        </div>

        <div class="flex gap-3 mt-7">
            <button type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg">
                Simpan
            </button>

            <a href="{{ route('petugas.customers.index') }}"
                class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-5 py-2 rounded-lg">
                Batal
            </a>
        </div>
    </form>
</div>

@endsection
