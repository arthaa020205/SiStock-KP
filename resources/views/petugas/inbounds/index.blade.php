@extends('layouts.app')

@section('title', 'Barang Masuk - SiStock')
@section('page-title', 'Barang Masuk')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-semibold text-gray-800">Data Barang Masuk</h2>
            <p class="text-sm text-gray-500 mt-1">
                Kelola transaksi penerimaan barang dari supplier.
            </p>
        </div>

        <a href="{{ route('petugas.inbounds.create') }}"
           class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
            + Tambah Barang Masuk
        </a>
    </div>

    @if (session('success'))
        <div class="p-4 rounded-lg bg-green-50 text-green-700 text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="p-4 rounded-lg bg-red-50 text-red-700 text-sm">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-600">
                <thead class="bg-gray-50 text-gray-700">
                    <tr>
                        <th class="px-6 py-4 font-semibold">No.</th>
                        <th class="px-6 py-4 font-semibold">Nomor Transaksi</th>
                        <th class="px-6 py-4 font-semibold">Tanggal</th>
                        <th class="px-6 py-4 font-semibold">Supplier</th>
                        <th class="px-6 py-4 font-semibold">Petugas</th>
                        <th class="px-6 py-4 font-semibold">Status</th>
                        <th class="px-6 py-4 font-semibold text-center">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">
                    @forelse ($inbounds as $inbound)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                {{ $inbounds->firstItem() + $loop->index }}
                            </td>

                            <td class="px-6 py-4 font-medium text-gray-800">
                                {{ $inbound->transaction_number }}
                            </td>

                            <td class="px-6 py-4">
                                {{ $inbound->transaction_date->format('d-m-Y H:i') }}
                            </td>

                            <td class="px-6 py-4">
                                {{ $inbound->supplier->name ?? '-' }}
                            </td>

                            <td class="px-6 py-4">
                                {{ $inbound->creator->name ?? '-' }}
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-2">

                                    <a href="{{ route('petugas.inbounds.show', $inbound->id) }}"
                                    class="px-3 py-2 bg-blue-100 text-blue-700 text-xs font-medium rounded-lg hover:bg-blue-200">
                                        Detail
                                    </a>

                                    @if ($inbound->status === 'draft')

                                        <a href="{{ route('petugas.inbounds.edit', $inbound->id) }}"
                                        class="px-3 py-2 bg-yellow-100 text-yellow-700 text-xs font-medium rounded-lg hover:bg-yellow-200">
                                            Edit
                                        </a>

                                        <form action="{{ route('petugas.inbounds.destroy', $inbound->id) }}"
                                            method="POST"
                                            onsubmit="return confirm('Yakin ingin menghapus draft barang masuk ini?')">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit"
                                                    class="px-3 py-2 bg-red-100 text-red-700 text-xs font-medium rounded-lg hover:bg-red-200">
                                                Hapus
                                            </button>
                                        </form>

                                        <form action="{{ route('petugas.inbounds.post', $inbound->id) }}"
                                            method="POST"
                                            onsubmit="return confirm('Posting transaksi ini? Stok akan langsung diperbarui.')">
                                            @csrf

                                            <button type="submit"
                                                    class="px-3 py-2 bg-green-600 text-white text-xs font-medium rounded-lg hover:bg-green-700">
                                                Posting
                                            </button>
                                        </form>

                                    @else
                                        <span class="text-gray-400 text-xs">
                                            {{ ucfirst($inbound->status) }}
                                        </span>
                                    @endif

                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-10 text-center text-gray-400">
                                Belum ada transaksi barang masuk.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($inbounds->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $inbounds->links() }}
            </div>
        @endif
    </div>

</div>
@endsection