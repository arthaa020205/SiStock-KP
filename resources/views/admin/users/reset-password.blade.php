@extends('layouts.app')

@section('content')
<div class="p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Reset Password</h1>
        <p class="mt-1 text-sm text-gray-500">
            Atur ulang password untuk akun pengguna.
        </p>
    </div>

    <div class="max-w-2xl rounded-lg bg-white p-6 shadow">
        <div class="mb-6 rounded-lg bg-gray-50 p-4">
            <p class="text-sm text-gray-500">Akun pengguna</p>
            <p class="font-semibold text-gray-800">{{ $user->name }}</p>
            <p class="text-sm text-gray-600">{{ $user->email }}</p>
        </div>

        <form action="{{ route('admin.users.update-password', $user->id) }}"
              method="POST">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label for="password"
                       class="mb-2 block text-sm font-medium text-gray-700">
                    Password Baru
                </label>

                <input
                    type="password"
                    name="password"
                    id="password"
                    autocomplete="new-password"
                    required
                    class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:outline-none"
                >

                @error('password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label for="password_confirmation"
                       class="mb-2 block text-sm font-medium text-gray-700">
                    Konfirmasi Password Baru
                </label>

                <input
                    type="password"
                    name="password_confirmation"
                    id="password_confirmation"
                    autocomplete="new-password"
                    required
                    class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:outline-none"
                >
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('admin.users.index') }}"
                   class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Kembali
                </a>

                <button
                    type="submit"
                    class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                    Simpan Password
                </button>
            </div>
        </form>

        <p class="mt-4 text-xs text-gray-500">
            Password baru tidak akan ditampilkan atau disimpan dalam Activity Log.
        </p>
    </div>
</div>
@endsection