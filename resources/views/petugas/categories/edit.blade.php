
@extends('layouts.app')

@section('title', 'Edit Kategori - SiStock')
@section('page-title', 'Edit Kategori')

@section('content')

<div class="max-w-3xl">

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">

        <h2 class="text-xl font-bold text-gray-800 mb-6">
            Form Edit Kategori
        </h2>

        <form action="{{ route('petugas.categories.update', $category->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-5">
                <label class="block text-sm font-medium mb-2">
                    Nama Kategori
                </label>

                <input type="text" name="name"
                    value="{{ old('name', $category->name) }}"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">

                @error('name')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-5">
                <label class="block text-sm font-medium mb-2">
                    Deskripsi
                </label>

                <textarea name="description" rows="4"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">{{ old('description', $category->description) }}</textarea>

                @error('description')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium mb-2">
                    Status
                </label>

                <select name="is_active"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2">
                    <option value="1" {{ old('is_active', (string) $category->is_active) === '1' ? 'selected' : '' }}>
                        Aktif
                    </option>

                    <option value="0" {{ old('is_active', (string) $category->is_active) === '0' ? 'selected' : '' }}>
                        Tidak Aktif
                    </option>
                </select>

                @error('is_active')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex gap-3">
                <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg">
                    Perbarui
                </button>

                <a href="{{ route('petugas.categories.index') }}"
                    class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-5 py-2 rounded-lg">
                    Batal
                </a>
            </div>

        </form>

    </div>

</div>

@endsection
