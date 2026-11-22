@extends('layouts.app')

@section('content')
<div class="p-6">

    {{-- Header --}}
    <div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">
                Kelola Pengguna
            </h1>
            <p class="mt-1 text-sm text-gray-500">
                Kelola akun dan hak akses pengguna SiStock.
            </p>
        </div>

        <a href="{{ route('admin.users.create') }}"
           class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-blue-700">
            + Tambah Pengguna
        </a>
    </div>

    {{-- Filter --}}
    <div class="mb-6 rounded-lg bg-white p-5 shadow-sm">
        <form action="{{ route('admin.users.index') }}" method="GET"
              class="grid grid-cols-1 gap-4 md:grid-cols-4">

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">
                    Cari Pengguna
                </label>
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Nama atau email..."
                    class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">
                    Role
                </label>
                <select name="role"
                        class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Semua Role</option>
                    <option value="Admin" @selected(request('role') === 'Admin')>
                        Admin
                    </option>
                    <option value="Petugas Gudang" @selected(request('role') === 'Petugas Gudang')>
                        Petugas Gudang
                    </option>
                </select>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">
                    Status Akun
                </label>
                <select name="status"
                        class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Semua Status</option>
                    <option value="active" @selected(request('status') === 'active')>
                        Aktif
                    </option>
                    <option value="inactive" @selected(request('status') === 'inactive')>
                        Nonaktif
                    </option>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit"
                        class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                    Cari
                </button>

                <a href="{{ route('admin.users.index') }}"
                   class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">
                    Reset
                </a>
            </div>
        </form>
    </div>

    {{-- Tabel Pengguna --}}
    <div class="overflow-hidden rounded-lg bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">
                            No
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">
                            Nama Pengguna
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">
                            Email
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">
                            Role
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">
                            Status
                        </th>
                        <th class="px-6 py-3 text-center text-xs font-semibold uppercase text-gray-500">
                            Aksi
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse ($users as $user)
                        <tr class="hover:bg-gray-50">
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">
                                {{ $users->firstItem() + $loop->index }}
                            </td>

                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-800">
                                {{ $user->name }}
                            </td>

                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">
                                {{ $user->email }}
                            </td>

                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">
                                @forelse ($user->roles as $role)
                                    <span class="rounded-full bg-blue-100 px-2.5 py-1 text-xs font-medium text-blue-700">
                                        {{ $role->name }}
                                    </span>
                                @empty
                                    <span class="text-gray-400">Belum memiliki role</span>
                                @endforelse
                            </td>

                            <td class="whitespace-nowrap px-6 py-4">
                                @if ($user->is_active)
                                    <span class="rounded-full bg-green-100 px-2.5 py-1 text-xs font-medium text-green-700">
                                        Aktif
                                    </span>
                                @else
                                    <span class="rounded-full bg-red-100 px-2.5 py-1 text-xs font-medium text-red-700">
                                        Nonaktif
                                    </span>
                                @endif
                            </td>

                            <td class="whitespace-nowrap px-6 py-4 text-center text-sm">
                                <div class="flex items-center justify-center gap-2">

                                    {{-- Edit --}}
                                    <a href="{{ route('admin.users.edit', $user->id) }}"
                                    class="inline-flex items-center rounded-lg bg-blue-50 px-3 py-1.5 text-xs font-medium text-blue-700 hover:bg-blue-100">
                                        Edit
                                    </a>

                                    {{-- Reset Password --}}
                                    <a href="{{ route('admin.users.reset-password', $user->id) }}"
                                    class="inline-flex items-center rounded-lg bg-amber-50 px-3 py-1.5 text-xs font-medium text-amber-700 hover:bg-amber-100">
                                        Reset Password
                                    </a>

                                    {{-- Aktifkan / Nonaktifkan --}}
                                    <form action="{{ route('admin.users.toggle-status', $user->id) }}"
                                        method="POST"
                                        onsubmit="return confirm('Apakah Anda yakin ingin {{ $user->is_active ? 'menonaktifkan' : 'mengaktifkan' }} akun ini?')">
                                        @csrf
                                        @method('PATCH')

                                        @if ($user->is_active)
                                            <button type="submit"
                                                    class="inline-flex items-center rounded-lg bg-red-50 px-3 py-1.5 text-xs font-medium text-red-700 hover:bg-red-100">
                                                Nonaktifkan
                                            </button>
                                        @else
                                            <button type="submit"
                                                    class="inline-flex items-center rounded-lg bg-green-50 px-3 py-1.5 text-xs font-medium text-green-700 hover:bg-green-100">
                                                Aktifkan
                                            </button>
                                        @endif
                                    </form>

                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">
                                Tidak ada data pengguna.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="border-t border-gray-200 px-6 py-4">
            {{ $users->links() }}
        </div>
    </div>

</div>
@endsection