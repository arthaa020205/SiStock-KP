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

    {{-- Header --}}
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

    {{-- Filter Stock Opname --}}
    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">

        <div class="mb-4">
            <h3 class="text-sm font-semibold text-gray-800">
                Filter Stock Opname
            </h3>
            <p class="mt-1 text-xs text-gray-500">
                Pilih periode dan status untuk menyaring data Stock Opname.
            </p>
        </div>

        <form action="{{ route('petugas.stock-opnames.index') }}"
              method="GET"
              class="space-y-4">

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">

                {{-- Periode --}}
                <div>
                    <label for="periode"
                           class="mb-1.5 block text-sm font-medium text-gray-700">
                        Periode
                    </label>

                    <select name="periode"
                            id="periode"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-blue-500">

                        <option value="semua" {{ $periode === 'semua' ? 'selected' : '' }}>
                            Semua Periode
                        </option>

                        <option value="hari_ini" {{ $periode === 'hari_ini' ? 'selected' : '' }}>
                            Hari Ini
                        </option>

                        <option value="minggu_ini" {{ $periode === 'minggu_ini' ? 'selected' : '' }}>
                            Minggu Ini
                        </option>

                        <option value="bulan_ini" {{ $periode === 'bulan_ini' ? 'selected' : '' }}>
                            Bulan Ini
                        </option>

                        <option value="tahun_ini" {{ $periode === 'tahun_ini' ? 'selected' : '' }}>
                            Tahun Ini
                        </option>

                        <option value="rentang" {{ $periode === 'rentang' ? 'selected' : '' }}>
                            Rentang Tanggal
                        </option>
                    </select>
                </div>

                {{-- Tanggal Mulai --}}
                <div id="tanggalMulaiWrapper">
                    <label for="tanggal_mulai"
                           class="mb-1.5 block text-sm font-medium text-gray-700">
                        Tanggal Mulai
                    </label>

                    <input type="date"
                           name="tanggal_mulai"
                           id="tanggal_mulai"
                           value="{{ $tanggalMulai }}"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                {{-- Tanggal Akhir --}}
                <div id="tanggalAkhirWrapper">
                    <label for="tanggal_akhir"
                           class="mb-1.5 block text-sm font-medium text-gray-700">
                        Tanggal Akhir
                    </label>

                    <input type="date"
                           name="tanggal_akhir"
                           id="tanggal_akhir"
                           value="{{ $tanggalAkhir }}"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                {{-- Status --}}
                <div>
                    <label for="status"
                           class="mb-1.5 block text-sm font-medium text-gray-700">
                        Status
                    </label>

                    <select name="status"
                            id="status"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-blue-500">

                        <option value="">Semua Status</option>

                        <option value="draft" {{ $status === 'draft' ? 'selected' : '' }}>
                            Draft
                        </option>

                        <option value="submitted" {{ $status === 'submitted' ? 'selected' : '' }}>
                            Diajukan
                        </option>

                        <option value="approved" {{ $status === 'approved' ? 'selected' : '' }}>
                            Disetujui
                        </option>

                        <option value="rejected" {{ $status === 'rejected' ? 'selected' : '' }}>
                            Ditolak
                        </option>

                        <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>
                            Selesai
                        </option>
                    </select>
                </div>

            </div>

            {{-- Tombol Filter --}}
            <div class="flex flex-wrap items-center gap-2 border-t border-gray-100 pt-4">

                <button type="submit"
                        class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-blue-700">
                    Terapkan Filter
                </button>

                <a href="{{ route('petugas.stock-opnames.index') }}"
                   class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
                    Reset
                </a>

            </div>
        </form>
    </div>

    {{-- Informasi Hasil --}}
    <div class="flex flex-col justify-between gap-2 sm:flex-row sm:items-center">
        <p class="text-sm text-gray-500">
            Menampilkan
            <span class="font-semibold text-gray-800">
                {{ $opnames->firstItem() ?? 0 }}–{{ $opnames->lastItem() ?? 0 }}
            </span>
            dari
            <span class="font-semibold text-gray-800">
                {{ $opnames->total() }}
            </span>
            data Stock Opname.
        </p>
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
                        <tr class="transition hover:bg-gray-50">

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
                                   class="inline-flex items-center rounded-lg bg-blue-50 px-3 py-2 text-sm font-medium text-blue-600 transition hover:bg-blue-100">
                                    Detail
                                </a>
                            </td>

                        </tr>

                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center">
                                <p class="text-sm font-medium text-gray-600">
                                    Tidak ada data Stock Opname yang sesuai dengan filter.
                                </p>

                                <p class="mt-1 text-xs text-gray-400">
                                    Coba ubah periode atau status yang dipilih.
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

{{-- Script Filter --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const periode = document.getElementById('periode');

        const tanggalMulaiWrapper = document.getElementById('tanggalMulaiWrapper');
        const tanggalAkhirWrapper = document.getElementById('tanggalAkhirWrapper');

        const tanggalMulai = document.getElementById('tanggal_mulai');
        const tanggalAkhir = document.getElementById('tanggal_akhir');

        function toggleTanggal() {
            const isRentang = periode.value === 'rentang';

            tanggalMulaiWrapper.classList.toggle('hidden', !isRentang);
            tanggalAkhirWrapper.classList.toggle('hidden', !isRentang);

            tanggalMulai.disabled = !isRentang;
            tanggalAkhir.disabled = !isRentang;
        }

        periode.addEventListener('change', toggleTanggal);

        toggleTanggal();
    });
</script>
@endsection