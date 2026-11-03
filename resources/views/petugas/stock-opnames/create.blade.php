@extends('layouts.app')

@section('title', 'Buat Stock Opname - SiStock')
@section('page-title', 'Buat Stock Opname')

@section('content')
<div class="mx-auto max-w-3xl space-y-6">

    {{-- Notifikasi Error --}}
    @if ($errors->any())
        <div class="rounded-lg bg-red-100 px-4 py-3 text-sm text-red-700">
            <ul class="list-inside list-disc">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Informasi --}}
    <div class="rounded-xl border border-blue-200 bg-blue-50 p-5">
        <h3 class="font-semibold text-blue-800">
            Informasi Stock Opname
        </h3>
        <p class="mt-2 text-sm leading-relaxed text-blue-700">
            Stock Opname digunakan untuk membandingkan jumlah stok yang
            tercatat dalam sistem dengan jumlah stok fisik di gudang.
            Setelah transaksi dibuat, sistem akan mengambil data stok
            berdasarkan batch dan lokasi rak sebagai acuan pemeriksaan.
        </p>
    </div>

    {{-- Form --}}
    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-gray-800">
                Form Pembuatan Stock Opname
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                Lengkapi informasi berikut untuk memulai Stock Opname.
            </p>
        </div>

        <form action="{{ route('petugas.stock-opnames.store') }}"
              method="POST"
              class="space-y-5">

            @csrf

            {{-- Tanggal --}}
            <div>
                <label for="opname_date"
                       class="mb-2 block text-sm font-medium text-gray-700">
                    Tanggal Stock Opname
                    <span class="text-red-500">*</span>
                </label>

                <input type="date"
                       name="opname_date"
                       id="opname_date"
                       value="{{ old('opname_date', now()->format('Y-m-d')) }}"
                       required
                       class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-700 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
            </div>

            {{-- Keterangan --}}
            <div>
                <label for="notes"
                       class="mb-2 block text-sm font-medium text-gray-700">
                    Keterangan
                </label>

                <textarea name="notes"
                          id="notes"
                          rows="4"
                          placeholder="Masukkan keterangan Stock Opname (opsional)"
                          class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm text-gray-700 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">{{ old('notes') }}</textarea>
            </div>

            {{-- Informasi Status --}}
            <div class="rounded-lg bg-yellow-50 p-4">
                <p class="text-sm font-medium text-yellow-800">
                    Status awal: Draft
                </p>
                <p class="mt-1 text-xs leading-relaxed text-yellow-700">
                    Pembuatan Stock Opname tidak langsung mengubah jumlah
                    stok barang. Penyesuaian stok akan diproses melalui
                    mekanisme pengajuan dan persetujuan.
                </p>
            </div>

            {{-- Tombol --}}
            <div class="flex flex-col-reverse justify-end gap-3 border-t border-gray-100 pt-5 sm:flex-row">
                <a href="{{ route('petugas.stock-opnames.index') }}"
                   class="rounded-lg border border-gray-300 px-5 py-2.5 text-center text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Batal
                </a>

                <button type="submit"
                        class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-blue-700">
                    Simpan Stock Opname
                </button>
            </div>

        </form>
    </div>

</div>
@endsection