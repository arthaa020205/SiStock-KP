
@extends('layouts.app')

@section('title', 'Petugas Gudang - SiStock')

@section('page-title', 'Halaman Utama Petugas')

@section('content')

    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">
            Selamat Datang di SiStock
        </h2>

        <p class="text-gray-500 mt-1">
            Anda masuk sebagai Petugas Gudang.
        </p>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="font-semibold text-lg">
            Area Kerja Petugas Gudang
        </h3>

        <p class="text-gray-600 mt-2">
            Halaman ini akan digunakan untuk mengelola data barang,
            penerimaan barang, pengeluaran barang, dan stock opname.
        </p>
    </div>

@endsection
