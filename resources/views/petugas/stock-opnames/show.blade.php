@extends('layouts.app')

@section('title', 'Detail Stock Opname - SiStock')
@section('page-title', 'Detail Stock Opname')

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

    {{-- Perhitungan Status Penghitungan --}}
    @php
        $totalDetails = $stockOpname->details->count();

        $countedDetails = $stockOpname->details
            ->where('is_counted', true)
            ->count();

        $uncountedDetails = $totalDetails - $countedDetails;
    @endphp

    {{-- Informasi Stock Opname --}}
    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <div class="mb-5 flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
            <div>
                <h2 class="text-lg font-semibold text-gray-800">
                    Informasi Stock Opname
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Informasi transaksi pemeriksaan stok barang.
                </p>
            </div>

            @php
                $statusClasses = [
                    'draft' => 'bg-yellow-100 text-yellow-700',
                    'submitted' => 'bg-blue-100 text-blue-700',
                    'approved' => 'bg-green-100 text-green-700',
                    'rejected' => 'bg-red-100 text-red-700',
                    'completed' => 'bg-gray-100 text-gray-700',
                ];

                $statusLabels = [
                    'draft' => 'Draft',
                    'submitted' => 'Diajukan',
                    'approved' => 'Disetujui',
                    'rejected' => 'Ditolak',
                    'completed' => 'Selesai',
                ];
            @endphp

            <span class="h-fit w-fit rounded-full px-3 py-1 text-sm font-medium {{ $statusClasses[$stockOpname->status] ?? 'bg-gray-100 text-gray-700' }}">
                {{ $statusLabels[$stockOpname->status] ?? ucfirst($stockOpname->status) }}
            </span>
        </div>

        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
            <div>
                <p class="text-sm text-gray-500">Nomor Stock Opname</p>
                <p class="mt-1 font-semibold text-gray-800">
                    {{ $stockOpname->opname_number }}
                </p>
            </div>

            <div>
                <p class="text-sm text-gray-500">Tanggal Stock Opname</p>
                <p class="mt-1 font-medium text-gray-800">
                    {{ \Carbon\Carbon::parse($stockOpname->opname_date)->format('d-m-Y') }}
                </p>
            </div>

            <div>
                <p class="text-sm text-gray-500">Petugas</p>
                <p class="mt-1 font-medium text-gray-800">
                    {{ $stockOpname->creator->name ?? '-' }}
                </p>
            </div>

            <div>
                <p class="text-sm text-gray-500">Keterangan</p>
                <p class="mt-1 font-medium text-gray-800">
                    {{ $stockOpname->notes ?: '-' }}
                </p>
            </div>
        </div>
    </div>

    {{-- Ringkasan Penghitungan --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-gray-500">Total Barang</p>
            <p class="mt-2 text-2xl font-bold text-gray-800">
                {{ $totalDetails }}
            </p>
        </div>

        <div class="rounded-xl border border-green-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-gray-500">Sudah Dihitung</p>
            <p class="mt-2 text-2xl font-bold text-green-600">
                {{ $countedDetails }}
            </p>
        </div>

        <div class="rounded-xl border border-yellow-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-gray-500">Belum Dihitung</p>
            <p class="mt-2 text-2xl font-bold text-yellow-600">
                {{ $uncountedDetails }}
            </p>
        </div>

    </div>

    {{-- Form Hasil Perhitungan --}}
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

        <div class="border-b border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-800">
                Hasil Perhitungan Stok
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                Masukkan jumlah fisik barang berdasarkan batch dan lokasi rak.
            </p>
        </div>

        <form action="{{ route('petugas.stock-opnames.details.update', $stockOpname->id) }}"
              method="POST">

            @csrf
            @method('PUT')

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-600">
                                No.
                            </th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-600">
                                Produk
                            </th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-600">
                                Batch
                            </th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-600">
                                Lokasi Rak
                            </th>
                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase text-gray-600">
                                Stok Sistem
                            </th>
                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase text-gray-600">
                                Stok Fisik
                            </th>
                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase text-gray-600">
                                Selisih
                            </th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-600">
                                Catatan
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100">
                        @forelse ($stockOpname->details as $detail)
                            <tr>
                                <td class="px-5 py-4 text-sm text-gray-600">
                                    {{ $loop->iteration }}

                                    <input type="hidden"
                                           name="items[{{ $loop->index }}][id]"
                                           value="{{ $detail->id }}">
                                </td>

                                <td class="px-5 py-4">
                                    <p class="text-sm font-medium text-gray-800">
                                        {{ $detail->batchStock->productBatch->product->name ?? '-' }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        {{ $detail->batchStock->productBatch->product->code ?? '-' }}
                                    </p>
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-sm text-gray-700">
                                    {{ $detail->batchStock->productBatch->batch_code ?? '-' }}
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-sm text-gray-700">
                                    {{ $detail->batchStock->rackLocation->code ?? '-' }}
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-right text-sm font-medium text-gray-800">
                                    {{ number_format((float) $detail->system_quantity, 3, ',', '.') }}
                                    {{ $detail->batchStock->productBatch->product->baseUnit->symbol ?? '' }}
                                </td>

                                <td class="px-5 py-4">
                                    @if ($stockOpname->status === 'draft')
                                        <input type="number"
                                               name="items[{{ $loop->index }}][physical_quantity]"
                                               value="{{ old('items.' . $loop->index . '.physical_quantity', $detail->is_counted ? $detail->physical_quantity : '') }}"
                                               min="0"
                                               step="0.001"
                                               required
                                               class="physical-input w-28 rounded-lg border border-gray-300 px-3 py-2 text-right text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                                               data-system="{{ $detail->system_quantity }}"
                                               data-index="{{ $loop->index }}">
                                    @else
                                        <span class="text-sm font-medium text-gray-800">
                                            {{ number_format((float) $detail->physical_quantity, 3, ',', '.') }}
                                        </span>
                                    @endif

                                    <div class="mt-1">
                                        @if ($detail->is_counted)
                                            <span class="text-xs font-medium text-green-600">
                                                Sudah dihitung
                                            </span>
                                        @else
                                            <span class="text-xs font-medium text-yellow-600">
                                                Belum dihitung
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                <td class="px-5 py-4 text-right">
                                    <span id="difference-{{ $loop->index }}"
                                          class="difference-value text-sm font-semibold text-gray-700">
                                        {{ number_format((float) $detail->difference_quantity, 3, ',', '.') }}
                                    </span>
                                </td>

                                <td class="px-5 py-4">
                                    @if ($stockOpname->status === 'draft')
                                        <input type="text"
                                               name="items[{{ $loop->index }}][notes]"
                                               value="{{ old('items.' . $loop->index . '.notes', $detail->notes) }}"
                                               placeholder="Catatan..."
                                               class="w-36 rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                                    @else
                                        <span class="text-sm text-gray-700">
                                            {{ $detail->notes ?: '-' }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                                                        {{-- Informasi Persetujuan Penyesuaian Stok --}}
                            @foreach ($detail->adjustmentRequests as $adjustmentRequest)

                                @php
                                    $requestStatusClasses = [
                                        'pending' => 'bg-yellow-100 text-yellow-700',
                                        'approved' => 'bg-green-100 text-green-700',
                                        'rejected' => 'bg-red-100 text-red-700',
                                    ];

                                    $requestStatusLabels = [
                                        'pending' => 'Menunggu Persetujuan',
                                        'approved' => 'Disetujui Admin',
                                        'rejected' => 'Ditolak Admin',
                                    ];
                                @endphp

                                <tr class="bg-gray-50">
                                    <td colspan="8" class="px-5 py-4">

                                        <div class="rounded-lg border border-gray-200 bg-white p-4">

                                            <div class="flex flex-col justify-between gap-2 sm:flex-row sm:items-center">

                                                <div>
                                                    <p class="text-sm font-semibold text-gray-800">
                                                        Permintaan Penyesuaian Stok
                                                    </p>

                                                    <p class="mt-1 text-xs text-gray-500">
                                                        {{ $adjustmentRequest->request_reason }}
                                                    </p>
                                                </div>

                                                <span class="w-fit rounded-full px-3 py-1 text-xs font-medium {{ $requestStatusClasses[$adjustmentRequest->status] ?? 'bg-gray-100 text-gray-700' }}">
                                                    {{ $requestStatusLabels[$adjustmentRequest->status] ?? ucfirst($adjustmentRequest->status) }}
                                                </span>

                                            </div>

                                            @if ($adjustmentRequest->status === 'rejected')
                                                <div class="mt-3 rounded-lg border border-red-200 bg-red-50 p-3">

                                                    <p class="text-sm font-semibold text-red-700">
                                                        Alasan Penolakan Admin
                                                    </p>

                                                    <p class="mt-1 text-sm text-red-700">
                                                        {{ $adjustmentRequest->approval_notes ?: 'Admin tidak memberikan alasan penolakan.' }}
                                                    </p>

                                                </div>
                                            @elseif ($adjustmentRequest->status === 'approved')
                                                <div class="mt-3 rounded-lg border border-green-200 bg-green-50 p-3">

                                                    <p class="text-sm font-semibold text-green-700">
                                                        Permintaan penyesuaian stok telah disetujui Admin.
                                                    </p>

                                                    @if ($adjustmentRequest->approval_notes)
                                                        <p class="mt-1 text-sm text-green-700">
                                                            Catatan Admin: {{ $adjustmentRequest->approval_notes }}
                                                        </p>
                                                    @endif

                                                </div>
                                            @endif

                                        </div>

                                    </td>
                                </tr>

                            @endforeach
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-10 text-center text-sm text-gray-500">
                                    Tidak ada data stok yang perlu diperiksa.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Tombol Simpan --}}
            @if ($stockOpname->status === 'draft' && $stockOpname->details->isNotEmpty())
                <div class="flex flex-col-reverse justify-between gap-3 border-t border-gray-200 p-6 sm:flex-row">
                    <a href="{{ route('petugas.stock-opnames.index') }}"
                       class="rounded-lg border border-gray-300 px-5 py-2.5 text-center text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Kembali
                    </a>

                    <button type="submit"
                            class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-blue-700">
                        Simpan Hasil Perhitungan
                    </button>
                </div>
            @else
                <div class="border-t border-gray-200 p-6">
                    <a href="{{ route('petugas.stock-opnames.index') }}"
                       class="inline-flex rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Kembali
                    </a>
                </div>
            @endif

        </form>
    </div>

    {{-- Submit Stock Opname --}}
    @if ($stockOpname->status === 'draft' && $stockOpname->details->isNotEmpty())
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">

            <h2 class="text-lg font-semibold text-gray-800">
                Submit Stock Opname
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Ajukan hasil penghitungan stok kepada Admin untuk pemeriksaan dan persetujuan penyesuaian.
            </p>

            @if ($uncountedDetails > 0)
                <div class="mt-4 rounded-lg border border-yellow-200 bg-yellow-50 px-4 py-3 text-sm text-yellow-700">
                    Masih ada <strong>{{ $uncountedDetails }} barang</strong> yang belum dihitung.
                    Silakan lengkapi dan simpan seluruh hasil penghitungan terlebih dahulu.
                </div>
            @else
                <div class="mt-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                    Seluruh barang sudah dihitung. Stock Opname siap diajukan kepada Admin.
                </div>

                <form action="{{ route('petugas.stock-opnames.submit', $stockOpname->id) }}"
                      method="POST"
                      class="mt-5"
                      onsubmit="return confirm('Yakin ingin submit Stock Opname ini? Setelah disubmit, hasil penghitungan tidak dapat diubah.')">

                    @csrf

                    <button type="submit"
                            class="rounded-lg bg-green-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-green-700">
                        Submit Stock Opname
                    </button>
                </form>
            @endif

        </div>
    @endif

</div>

{{-- JavaScript Perhitungan Selisih --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const inputs = document.querySelectorAll('.physical-input');

        function formatNumber(value) {
            return Number(value).toLocaleString('id-ID', {
                minimumFractionDigits: 3,
                maximumFractionDigits: 3
            });
        }

        function calculateDifference(input) {
            const systemQuantity = parseFloat(input.dataset.system) || 0;
            const physicalQuantity = parseFloat(input.value) || 0;

            const difference = physicalQuantity - systemQuantity;
            const index = input.dataset.index;

            const element = document.getElementById('difference-' + index);

            element.textContent = formatNumber(difference);

            element.classList.remove(
                'text-green-700',
                'text-red-700',
                'text-gray-700'
            );

            if (difference > 0) {
                element.classList.add('text-green-700');
            } else if (difference < 0) {
                element.classList.add('text-red-700');
            } else {
                element.classList.add('text-gray-700');
            }
        }

        inputs.forEach(function (input) {
            calculateDifference(input);

            input.addEventListener('input', function () {
                calculateDifference(input);
            });
        });
    });
</script>
@endsection