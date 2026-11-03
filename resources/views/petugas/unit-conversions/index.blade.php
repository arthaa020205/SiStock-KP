@extends('layouts.app')

@section('title', 'Konversi Satuan - SiStock')
@section('page-title', 'Konversi Satuan')

@section('content')

<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">Konversi Satuan Produk</h2>
        <p class="text-gray-500 mt-1">Kelola faktor konversi satuan untuk setiap produk.</p>
    </div>

    <a href="{{ route('petugas.unit-conversions.create') }}"
        class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg text-sm">
        + Tambah Konversi
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
        <h3 class="font-semibold text-gray-800">Daftar Konversi Satuan</h3>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 text-gray-600">
                <tr>
                    <th class="px-5 py-4">No.</th>
                    <th class="px-5 py-4">Kode Produk</th>
                    <th class="px-5 py-4">Nama Produk</th>
                    <th class="px-5 py-4">Satuan Dasar</th>
                    <th class="px-5 py-4">Satuan Konversi</th>
                    <th class="px-5 py-4">Faktor Konversi</th>
                    <th class="px-5 py-4 text-center">Aksi</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100">
                @forelse($conversions as $conversion)
                    <tr>
                        <td class="px-5 py-4">{{ $loop->iteration }}</td>

                        <td class="px-5 py-4 font-medium">
                            {{ $conversion->product->code ?? '-' }}
                        </td>

                        <td class="px-5 py-4">
                            {{ $conversion->product->name ?? '-' }}
                        </td>

                        <td class="px-5 py-4">
                            {{ $conversion->product->baseUnit->symbol ?? '-' }}
                        </td>

                        <td class="px-5 py-4">
                            {{ $conversion->unit->name ?? '-' }}
                            ({{ $conversion->unit->symbol ?? '-' }})
                        </td>

                        <td class="px-5 py-4">
                            1 {{ $conversion->unit->symbol ?? '' }}
                            = {{ rtrim(rtrim(number_format((float) $conversion->conversion_factor, 3, ',', '.'), '0'), ',') }}
                            {{ $conversion->product->baseUnit->symbol ?? '' }}
                        </td>

                        <td class="px-5 py-4">
                            <div class="flex justify-center gap-2">
                                <a href="{{ route('petugas.unit-conversions.edit', $conversion->id) }}"
                                    class="bg-yellow-100 hover:bg-yellow-200 text-yellow-700 px-3 py-2 rounded-lg text-xs">
                                    Edit
                                </a>

                                <form action="{{ route('petugas.unit-conversions.destroy', $conversion->id) }}"
                                    method="POST"
                                    onsubmit="return confirm('Yakin ingin menghapus konversi satuan ini?')">
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
                        <td colspan="7" class="px-5 py-10 text-center text-gray-500">
                            Belum ada data konversi satuan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection