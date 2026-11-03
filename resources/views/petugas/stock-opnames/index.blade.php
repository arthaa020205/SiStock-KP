@extends('layouts.app')

@section('title', 'Stock Opname - SiStock')
@section('page-title', 'Stock Opname')

@section('content')
<div class="space-y-6">

    {{-- Notifikasi --}}
    @if (session('success'))
        <div class="rounded-lg bg-green-100 px-4 py-3 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="rounded-lg bg-red-100 px-4 py-3 text-sm text-red-700">
            <ul class="list-inside list-disc">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Header dan Tombol --}}
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <h2 class="text-lg font-semibold text-gray-800">
                Daftar Stock Opname
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                Kelola dan pantau kegiatan pemeriksaan stok barang di gudang.
            </p>
        </div>

        <a href="{{ route('petugas.stock-opnames.create') }}"
           class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-blue-700">
            + Buat Stock Opname
        </a>
    </div>

    {{-- Tabel Stock Opname --}}
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-600">
                            No.
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-600">
                            Nomor Stock Opname
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-600">
                            Tanggal
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-600">
                            Petugas
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-600">
                            Status
                        </th>
                        <th class="px-6 py-3 text-center text-xs font-semibold uppercase text-gray-600">
                            Aksi
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse ($opnames as $opname)
                        <tr class="hover:bg-gray-50">
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">
                                {{ $opnames->firstItem() + $loop->index }}
                            </td>

                            <td class="whitespace-nowrap px-6 py-4 text-sm font-semibold text-gray-800">
                                {{ $opname->opname_number }}
                            </td>

                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">
                                {{ \Carbon\Carbon::parse($opname->opname_date)->format('d-m-Y') }}
                            </td>

                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">
                                {{ $opname->creator->name ?? '-' }}
                            </td>

                            <td class="whitespace-nowrap px-6 py-4">
                                @if ($opname->status === 'draft')
                                    <span class="rounded-full bg-yellow-100 px-3 py-1 text-xs font-medium text-yellow-700">
                                        Draft
                                    </span>
                                @elseif ($opname->status === 'submitted')
                                    <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-medium text-blue-700">
                                        Diajukan
                                    </span>
                                @elseif ($opname->status === 'approved')
                                    <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-700">
                                        Disetujui
                                    </span>
                                @elseif ($opname->status === 'rejected')
                                    <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-medium text-red-700">
                                        Ditolak
                                    </span>
                                @elseif ($opname->status === 'completed')
                                    <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700">
                                        Selesai
                                    </span>
                                @else
                                    <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700">
                                        {{ ucfirst($opname->status) }}
                                    </span>
                                @endif
                            </td>

                            <td class="whitespace-nowrap px-6 py-4 text-center">
                                <a href="{{ route('petugas.stock-opnames.show', $opname->id) }}"
                                   class="inline-flex items-center rounded-lg bg-blue-50 px-3 py-2 text-sm font-medium text-blue-600 hover:bg-blue-100">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center">
                                <p class="text-sm font-medium text-gray-600">
                                    Belum ada transaksi Stock Opname.
                                </p>
                                <p class="mt-1 text-xs text-gray-400">
                                    Klik tombol Buat Stock Opname untuk memulai.
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($opnames->hasPages())
            <div class="border-t border-gray-200 px-6 py-4">
                {{ $opnames->links() }}
            </div>
        @endif
    </div>

</div>
@endsection