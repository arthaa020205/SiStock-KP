@extends('layouts.app')

@section('content')
<div class="p-6">

    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">
            Edit Pengguna
        </h1>
        <p class="mt-1 text-sm text-gray-500">
            Perbarui informasi dan hak akses pengguna SiStock.
        </p>
    </div>

    {{-- Notifikasi --}}
    @if (session('error'))
        <div class="mb-5 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700">
            {{ session('error') }}
        </div>
    @endif

    {{-- Form --}}
    <div class="max-w-3xl rounded-lg bg-white p-6 shadow-sm">

        <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
            @csrf
            @method('PUT')

            {{-- Nama --}}
            <div class="mb-5">
                <label for="name" class="mb-2 block text-sm font-medium text-gray-700">
                    Nama Lengkap <span class="text-red-500">*</span>
                </label>

                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name', $user->name) }}"
                    required
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
                    value="{{ old('email', $user->email) }}"
                    required
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
                    @disabled(auth()->id() === $user->id)
                    class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500 disabled:bg-gray-100 disabled:text-gray-500">

                    @foreach ($roles as $role)
                        <option
                            value="{{ $role->name }}"
                            @selected(old('role', $currentRole) === $role->name)>
                            {{ $role->name }}
                        </option>
                    @endforeach
                </select>

                @if (auth()->id() === $user->id)
                    <p class="mt-1 text-xs text-gray-500">
                        Role akun sendiri tidak dapat diubah.
                    </p>
                @endif

                @error('role')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Informasi Password --}}
            <div class="mb-6 rounded-lg border border-blue-100 bg-blue-50 p-4">
                <p class="text-sm font-medium text-blue-800">
                    Informasi Password
                </p>
                <p class="mt-1 text-xs text-blue-700">
                    Password tidak diubah melalui halaman ini.
                    Gunakan fitur Reset Password untuk mengganti password pengguna.
                </p>
            </div>

            {{-- Tombol --}}
            <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-5">
                <a href="{{ route('admin.users.index') }}"
                   class="rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-600 hover:bg-gray-50">
                    Batal
                </a>

                <button type="submit"
                        class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-blue-700">
                    Simpan Perubahan
                </button>
            </div>

        </form>
    </div>
</div>
@endsection