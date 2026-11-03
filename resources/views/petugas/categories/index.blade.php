@extends('layouts.app')

@section('title', 'Data Kategori - SiStock')
@section('page-title', 'Data Kategori')

@section('content')

<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">

    <div>
        <h2 class="text-2xl font-bold text-gray-800">
            Data Kategori
        </h2>

        <p class="text-gray-500 mt-1">
            Kelola kategori barang yang tersedia di gudang.
        </p>
    </div>

    <a href="{{ route('petugas.categories.create') }}"
        class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg text-sm font-medium">
        + Tambah Kategori
    </a>

</div>

@if(session('success'))
    <div class="mb-5 p-4 rounded-lg bg-green-100 text-green-700">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="mb-5 p-4 rounded-lg bg-red-100 text-red-700">
        {{ session('error') }}
    </div>
@endif

<div class="bg-white rounded-xl shadow-sm border border-gray-200">

    <div class="p-5 border-b border-gray-200">
        <h3 class="font-semibold text-gray-800">
            Daftar Kategori Barang
        </h3>
    </div>

    <div class="overflow-x-auto">

        <table class="w-full text-sm text-left">

            <thead class="bg-gray-50 text-gray-600">
                <tr>
                    <th class="px-6 py-4">No.</th>
                    <th class="px-6 py-4">Nama Kategori</th>
                    <th class="px-6 py-4">Deskripsi</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4 text-center">Aksi</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100">

                @forelse($categories as $category)

                    <tr>
                        <td class="px-6 py-4">
                            {{ $loop->iteration }}
                        </td>

                        <td class="px-6 py-4 font-medium text-gray-800">
                            {{ $category->name }}
                        </td>

                        <td class="px-6 py-4 text-gray-600">
                            {{ $category->description ?? '-' }}
                        </td>

                        <td class="px-6 py-4">
                            @if($category->is_active)
                                <span class="px-3 py-1 rounded-full bg-green-100 text-green-700 text-xs">
                                    Aktif
                                </span>
                            @else
                                <span class="px-3 py-1 rounded-full bg-red-100 text-red-700 text-xs">
                                    Tidak Aktif
                                </span>
                            @endif
                        </td>

                        <td class="px-6 py-4">
                            <div class="flex justify-center items-center gap-2">

                                <a href="{{ route('petugas.categories.edit', $category->id) }}"
                                    class="bg-yellow-100 hover:bg-yellow-200 text-yellow-700 px-3 py-2 rounded-lg text-xs">
                                    Edit
                                </a>

                                <form action="{{ route('petugas.categories.destroy', $category->id) }}"
                                    method="POST"
                                    onsubmit="return confirm('Yakin ingin menghapus kategori ini?')">

                                    @csrf
                                    @method('DELETE')

                                    <button type="submit"
                                        class="bg-red-100 hover:bg-red-200 text-red-700 px-3 py-2 rounded-lg text-xs">
                                        Hapus
                                    </button>

                                </form>

                            </div>
                        </td>
                    </tr>

                @empty

                    <tr>
                        <td colspan="5"
                            class="px-6 py-10 text-center text-gray-500">
                            Belum ada data kategori.
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</div>

@endsection
