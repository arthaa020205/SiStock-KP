@extends('layouts.app')

@section('title', 'Transfer Lokasi Rak - SiStock')
@section('page-title', 'Transfer Lokasi Rak')

@section('content')
<div class="space-y-6">

    @if(session('success'))
        <div class="rounded-lg bg-green-100 p-4 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="rounded-lg bg-red-100 p-4 text-sm text-red-700">
            <ul class="list-inside list-disc">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <h2 class="text-lg font-semibold text-gray-800">
                Data Transfer Lokasi Rak
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                Kelola perpindahan stok antar rak di dalam gudang.
            </p>
        </div>

        <a href="{{ route('petugas.transfers.create') }}"
           class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
            + Transfer Baru
        </a>
    </div>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="px-6 py-4 font-semibold">No.</th>
                        <th class="px-6 py-4 font-semibold">Nomor Transaksi</th>
                        <th class="px-6 py-4 font-semibold">Tanggal</th>
                        <th class="px-6 py-4 font-semibold">Petugas</th>
                        <th class="px-6 py-4 font-semibold">Status</th>
                        <th class="px-6 py-4 text-center font-semibold">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">
                    @forelse($transfers as $index => $transfer)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-gray-600">
                                {{ $transfers->firstItem() + $index }}
                            </td>

                            <td class="px-6 py-4">
                                <span class="font-medium text-gray-800">
                                    {{ $transfer->transaction_number }}
                                </span>
                            </td>

                            <td class="px-6 py-4 text-gray-600">
                                {{ $transfer->transaction_date?->format('d-m-Y') ?? '-' }}
                            </td>

                            <td class="px-6 py-4 text-gray-600">
                                {{ $transfer->creator?->name ?? '-' }}
                            </td>

                            <td class="px-6 py-4">
                                @if($transfer->status === 'posted')
                                    <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">
                                        Posted
                                    </span>
                                @elseif($transfer->status === 'draft')
                                    <span class="rounded-full bg-yellow-100 px-3 py-1 text-xs font-semibold text-yellow-700">
                                        Draft
                                    </span>
                                @else
                                    <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">
                                        {{ ucfirst($transfer->status) }}
                                    </span>
                                @endif
                            </td>

                            <td class="px-6 py-4 text-center">
                                <a href="{{ route('petugas.transfers.show', $transfer->id) }}"
                                   class="inline-flex rounded-lg bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-700 hover:bg-blue-100">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-gray-500">
                                Belum ada transaksi transfer lokasi rak.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transfers->hasPages())
            <div class="border-t border-gray-200 px-6 py-4">
                {{ $transfers->links() }}
            </div>
        @endif
    </div>

</div>
@endsection