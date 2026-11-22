@extends('layouts.app')

@section('content')
<div class="p-6">

    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">
            Tambah Pengguna
        </h1>
        <p class="mt-1 text-sm text-gray-500">
            Tambahkan akun pengguna baru ke dalam sistem SiStock.
        </p>
    </div>

    {{-- Form --}}
    <div class="max-w-3xl rounded-lg bg-white p-6 shadow-sm">

        <form action="{{ route('admin.users.store') }}" method="POST">
            @csrf

            {{-- Nama --}}
            <div class="mb-5">
                <label for="name" class="mb-2 block text-sm font-medium text-gray-700">
                    Nama Lengkap <span class="text-red-500">*</span>
                </label>

                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name') }}"
                    required
                    placeholder="Masukkan nama lengkap"
                    class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">

                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Email --}}
            <div class="mb-5">
                <label for="email" class="mb-2 block text-sm font-medium text-gray-700">
                    Email <span class="text-red-500">*</span>
                </label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    placeholder="contoh@email.com"
                    class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">

                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Role --}}
            <div class="mb-5">
                <label for="role" class="mb-2 block text-sm font-medium text-gray-700">
                    Role <span class="text-red-500">*</span>
                </label>

                <select
                    id="role"
                    name="role"
                    required
                    class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">

                    <option value="">Pilih Role</option>

                    @foreach ($roles as $role)
                        <option value="{{ $role->name }}"
                            @selected(old('role') === $role->name)>
                            {{ $role->name }}
                        </option>
                    @endforeach
                </select>

                @error('role')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Password --}}
            <div class="mb-5">
                <label for="password" class="mb-2 block text-sm font-medium text-gray-700">
                    Password <span class="text-red-500">*</span>
                </label>

                <input
                    type="password"
                    id="password"
                    name="password"
                    required
                    placeholder="Minimal 8 karakter"
                    autocomplete="new-password"
                    class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">

                @error('password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Konfirmasi Password --}}
            <div class="mb-6">
                <label for="password_confirmation" class="mb-2 block text-sm font-medium text-gray-700">
                    Konfirmasi Password <span class="text-red-500">*</span>
                </label>

                <input
                    type="password"
                    id="password_confirmation"
                    name="password_confirmation"
                    required
                    placeholder="Ulangi password"
                    autocomplete="new-password"
                    class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
            </div>

            {{-- Tombol --}}
            <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-5">
                <a href="{{ route('admin.users.index') }}"
                   class="rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-600 hover:bg-gray-50">
                    Batal
                </a>

                <button type="submit"
                        class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-blue-700">
                    Simpan Pengguna
                </button>
            </div>

        </form>
    </div>
</div>
@endsection