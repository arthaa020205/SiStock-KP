@extends('layouts.app')

@section('title', 'Tambah Satuan')

@section('content')
<div class="max-w-2xl space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Tambah Satuan</h1>
        <p class="text-slate-500">Masukkan informasi satuan baru.</p>
    </div>

    <form action="{{ route('petugas.units.store') }}" method="POST"
        class="bg-white p-6 rounded-xl shadow-sm space-y-5">
        @csrf

        <div>
            <label class="block mb-2 font-medium">Nama Satuan</label>
            <input type="text" name="name" value="{{ old('name') }}"
                placeholder="Contoh: Pieces"
                class="w-full border border-slate-300 rounded-lg px-4 py-2"
                required>

            @error('name')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block mb-2 font-medium">Simbol Satuan</label>
            <input type="text" name="symbol" value="{{ old('symbol') }}"
                placeholder="Contoh: Pcs"
                class="w-full border border-slate-300 rounded-lg px-4 py-2"
                required>

            @error('symbol')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex gap-3">
            <button type="submit"
                class="px-5 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                Simpan
            </button>

            <a href="{{ route('petugas.units.index') }}"
                class="px-5 py-2 bg-slate-200 text-slate-700 rounded-lg">
                Kembali
            </a>
        </div>
    </form>
</div>
@endsection