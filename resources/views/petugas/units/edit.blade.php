@extends('layouts.app')

@section('title', 'Edit Satuan')

@section('content')
<div class="max-w-2xl space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Edit Satuan</h1>
        <p class="text-slate-500">Perbarui informasi satuan.</p>
    </div>

    <form action="{{ route('petugas.units.update', $unit) }}" method="POST"
        class="bg-white p-6 rounded-xl shadow-sm space-y-5">
        @csrf
        @method('PUT')

        <div>
            <label class="block mb-2 font-medium">Nama Satuan</label>
            <input type="text" name="name"
                value="{{ old('name', $unit->name) }}"
                class="w-full border border-slate-300 rounded-lg px-4 py-2"
                required>

            @error('name')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block mb-2 font-medium">Simbol Satuan</label>
            <input type="text" name="symbol"
                value="{{ old('symbol', $unit->symbol) }}"
                class="w-full border border-slate-300 rounded-lg px-4 py-2"
                required>

            @error('symbol')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex gap-3">
            <button type="submit"
                class="px-5 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                Perbarui
            </button>

            <a href="{{ route('petugas.units.index') }}"
                class="px-5 py-2 bg-slate-200 text-slate-700 rounded-lg">
                Kembali
            </a>
        </div>
    </form>
</div>
@endsection