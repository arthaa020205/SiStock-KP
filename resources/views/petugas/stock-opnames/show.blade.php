@extends('layouts.app')

@section('title', 'Detail Stock Opname - SiStock')
@section('page-title', 'Detail Stock Opname')

@section('content')

@php
    $totalDetails = $stockOpname->details->count();

    $countedDetails = $stockOpname->details
        ->where('is_counted', true)
        ->count();

    $uncountedDetails = $totalDetails - $countedDetails;

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

        {{-- Tombol Cetak --}}
        <div class="mt-6 flex flex-wrap gap-3 border-t border-gray-100 pt-5">

            <button type="button"
                    onclick="window.print()"
                    class="rounded-lg bg-gray-800 px-5 py-2.5 text-sm font-medium text-white hover:bg-gray-900">
                Cetak Form Stock Opname
            </button>

            <a href="{{ route('petugas.stock-opnames.index') }}"
               class="rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50">
                Kembali
            </a>

        </div>

    </div>

    {{-- Ringkasan Penghitungan --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-gray-500">Total Produk</p>
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

        <div class="flex flex-col justify-between gap-3 border-b border-gray-200 p-6 sm:flex-row sm:items-center">

            <div>
                <h2 class="text-lg font-semibold text-gray-800">
                    Hasil Perhitungan Stok
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Hitung total fisik setiap produk, tanpa memisahkan batch.
                </p>
            </div>

            @if ($stockOpname->status === 'draft')
                <span class="w-fit rounded-full bg-yellow-100 px-3 py-1 text-xs font-medium text-yellow-700">
                    Mode Pengisian Draft
                </span>
            @endif

        </div>

        @if ($stockOpname->status === 'draft')

            <div class="border-b border-blue-100 bg-blue-50 px-6 py-4 text-sm text-blue-700">
                <strong>Petunjuk:</strong>
                Isi stok fisik hanya untuk produk yang sudah selesai dihitung.
                Produk yang belum dihitung boleh dikosongkan. Hasil penghitungan
                dapat disimpan secara bertahap dan dilanjutkan kembali.
            </div>

        @endif

        <form id="stockOpnameForm"
              action="{{ route('petugas.stock-opnames.details.update', $stockOpname->id) }}"
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
                                Status
                            </th>

                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-600">
                                Catatan
                            </th>

                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100">

                        @forelse ($stockOpname->details as $detail)

                            @php
                                // Mendukung data Stock Opname lama maupun baru.
                                $product = $detail->product
                                    ?? $detail->batchStock?->productBatch?->product;

                                $unit = $product?->baseUnit?->symbol ?? '';

                                $physicalValue = old(
                                    'items.' . $loop->index . '.physical_quantity',
                                    $detail->is_counted ? $detail->physical_quantity : ''
                                );

                                $notesValue = old(
                                    'items.' . $loop->index . '.notes',
                                    $detail->notes
                                );
                            @endphp

                            <tr class="product-row"
                                data-counted="{{ $detail->is_counted ? '1' : '0' }}">

                                <td class="whitespace-nowrap px-5 py-4 text-sm text-gray-600">

                                    {{ $loop->iteration }}

                                    <input type="hidden"
                                           class="detail-id"
                                           name="items[{{ $loop->index }}][id]"
                                           value="{{ $detail->id }}">

                                </td>

                                <td class="px-5 py-4">

                                    <p class="text-sm font-semibold text-gray-800">
                                        {{ $product->name ?? 'Produk tidak ditemukan' }}
                                    </p>

                                    <p class="mt-1 text-xs text-gray-500">
                                        {{ $product->code ?? '-' }}
                                    </p>

                                    @if (!$detail->product_id)
                                        <p class="mt-1 text-xs text-orange-600">
                                            Data Stock Opname format lama
                                        </p>
                                    @endif

                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-right">

                                    <span class="text-sm font-semibold text-gray-800">
                                        {{ number_format((float) $detail->system_quantity, 3, ',', '.') }}
                                    </span>

                                    <span class="text-xs text-gray-500">
                                        {{ $unit }}
                                    </span>

                                </td>

                                <td class="px-5 py-4">

                                    @if ($stockOpname->status === 'draft' && $detail->product_id)

                                        <input type="number"
                                               class="physical-input w-32 rounded-lg border border-gray-300 px-3 py-2 text-right text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                                               name="items[{{ $loop->index }}][physical_quantity]"
                                               value="{{ $physicalValue }}"
                                               min="0"
                                               step="0.001"
                                               placeholder="Jumlah"
                                               data-system="{{ $detail->system_quantity }}"
                                               data-index="{{ $loop->index }}"
                                               data-original="{{ $detail->is_counted ? $detail->physical_quantity : '' }}">

                                    @else

                                        <span class="text-sm font-medium text-gray-800">
                                            {{ number_format((float) $detail->physical_quantity, 3, ',', '.') }}
                                        </span>

                                        <span class="text-xs text-gray-500">
                                            {{ $unit }}
                                        </span>

                                    @endif

                                </td>

                                <td class="px-5 py-4 text-right">

                                    <span id="difference-{{ $loop->index }}"
                                          class="difference-value text-sm font-semibold text-gray-700">

                                        {{ $detail->is_counted
                                            ? number_format((float) $detail->difference_quantity, 3, ',', '.')
                                            : '-' }}

                                    </span>

                                </td>

                                <td class="px-5 py-4">

                                    @if ($detail->is_counted)

                                        <span class="inline-flex rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-700">
                                            Sudah dihitung
                                        </span>

                                    @else

                                        <span class="inline-flex rounded-full bg-yellow-100 px-3 py-1 text-xs font-medium text-yellow-700">
                                            Belum dihitung
                                        </span>

                                    @endif

                                </td>

                                <td class="px-5 py-4">

                                    @if ($stockOpname->status === 'draft' && $detail->product_id)

                                        <input type="text"
                                               class="notes-input w-40 rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                                               name="items[{{ $loop->index }}][notes]"
                                               value="{{ $notesValue }}"
                                               placeholder="Catatan..."
                                               data-original="{{ $detail->notes ?? '' }}">

                                    @else

                                        <span class="text-sm text-gray-700">
                                            {{ $detail->notes ?: '-' }}
                                        </span>

                                    @endif

                                </td>

                            </tr>

                            {{-- Informasi Permintaan Penyesuaian --}}
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

                                    <td colspan="7" class="px-5 py-4">

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
                                <td colspan="7" class="px-6 py-10 text-center text-sm text-gray-500">
                                    Tidak ada produk untuk diperiksa.
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
                            id="saveButton"
                            class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-blue-700">
                        Simpan Hasil Perhitungan
                    </button>

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

                    Masih ada <strong>{{ $uncountedDetails }} produk</strong>
                    yang belum dihitung.

                    Silakan lengkapi seluruh hasil penghitungan terlebih dahulu.

                </div>

            @else

                <div class="mt-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                    Seluruh produk sudah dihitung. Stock Opname siap diajukan kepada Admin.
                </div>

                <form action="{{ route('petugas.stock-opnames.submit', $stockOpname->id) }}"
                    method="POST"
                    class="mt-5"
                    onsubmit="return confirm('Yakin ingin mengirim Stock Opname untuk persetujuan Admin? Setelah disubmit, hasil penghitungan tidak dapat diubah.')">

                    @csrf

                    <button type="submit"
                            class="px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold">
                        Submit Stock Opname
                    </button>

                </form>

            @endif

        </div>

    @endif

</div>

{{-- Form Khusus Cetak --}}
<div id="printArea" class="hidden">

    <div class="mb-6 text-center">

        <h1 class="text-xl font-bold">
            FORMULIR STOCK OPNAME
        </h1>

        <p class="mt-1 text-sm">
            SiStock - Sistem Informasi Inventory
        </p>

    </div>

    <div class="mb-6 grid grid-cols-2 gap-4 text-sm">

        <div>
            <p><strong>Nomor:</strong> {{ $stockOpname->opname_number }}</p>
            <p><strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($stockOpname->opname_date)->format('d-m-Y') }}</p>
        </div>

        <div>
            <p><strong>Petugas:</strong> {{ $stockOpname->creator->name ?? '-' }}</p>
            <p><strong>Keterangan:</strong> {{ $stockOpname->notes ?: '-' }}</p>
        </div>

    </div>

    <table class="w-full border-collapse text-sm">

        <thead>
            <tr>
                <th class="border border-black p-2">No.</th>
                <th class="border border-black p-2 text-left">Kode Produk</th>
                <th class="border border-black p-2 text-left">Nama Produk</th>
                <th class="border border-black p-2 text-right">Stok Sistem</th>
                <th class="border border-black p-2">Stok Fisik</th>
                <th class="border border-black p-2">Catatan</th>
            </tr>
        </thead>

        <tbody>

            @foreach ($stockOpname->details as $detail)

                @php
                    $printProduct = $detail->product
                        ?? $detail->batchStock?->productBatch?->product;
                @endphp

                <tr>
                    <td class="border border-black p-2 text-center">
                        {{ $loop->iteration }}
                    </td>

                    <td class="border border-black p-2">
                        {{ $printProduct->code ?? '-' }}
                    </td>

                    <td class="border border-black p-2">
                        {{ $printProduct->name ?? '-' }}
                    </td>

                    <td class="border border-black p-2 text-right">
                        {{ number_format((float) $detail->system_quantity, 3, ',', '.') }}
                    </td>

                    <td class="border border-black p-2">
                        &nbsp;
                    </td>

                    <td class="border border-black p-2">
                        &nbsp;
                    </td>
                </tr>

            @endforeach

        </tbody>

    </table>

    <div class="mt-12 grid grid-cols-2 gap-10 text-center text-sm">

        <div>
            <p>Petugas Penghitung</p>
            <br><br><br>
            <p>(........................................)</p>
        </div>

        <div>
            <p>Supervisor / Pemeriksa</p>
            <br><br><br>
            <p>(........................................)</p>
        </div>

    </div>

</div>

@endsection

<style>
    /* Tampilan khusus cetak */
    @media print {

        @page {
            size: A4 portrait;
            margin: 12mm;
        }

        html,
        body {
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            background: white !important;
        }

        /* Sembunyikan seluruh halaman aplikasi */
        body * {
            visibility: hidden !important;
        }

        /* Tampilkan hanya formulir cetak */
        #printArea,
        #printArea * {
            visibility: visible !important;
        }

        #printArea {
            display: block !important;
            position: absolute !important;
            left: 0 !important;
            top: 0 !important;
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            background: white !important;
            color: black !important;
            font-family: Arial, sans-serif !important;
            font-size: 11px !important;
        }

        #printArea h1 {
            font-size: 18px !important;
            margin-bottom: 5px !important;
        }

        #printArea table {
            width: 100% !important;
            border-collapse: collapse !important;
            page-break-inside: auto;
        }

        #printArea thead {
            display: table-header-group;
        }

        #printArea tr {
            page-break-inside: avoid;
        }

        #printArea th,
        #printArea td {
            border: 1px solid black !important;
            padding: 7px !important;
            color: black !important;
        }

        #printArea th {
            font-weight: bold !important;
            text-align: center;
        }

        #printArea .signature-area {
            page-break-inside: avoid;
        }
    }
</style>

@push('scripts')
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

        const index = input.dataset.index;
        const element = document.getElementById('difference-' + index);

        if (input.value.trim() === '') {
            element.textContent = '-';
            element.className = 'difference-value text-sm font-semibold text-gray-700';
            return;
        }

        const systemQuantity = parseFloat(input.dataset.system) || 0;
        const physicalQuantity = parseFloat(input.value) || 0;

        const difference = physicalQuantity - systemQuantity;

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

        input.addEventListener('input', function () {
            calculateDifference(input);
        });

        calculateDifference(input);

    });

    /*
     * Simpan hanya produk yang baru dihitung atau diperbarui.
     * Produk yang belum dihitung dan dibiarkan kosong tidak dikirim.
     * Produk yang sudah dihitung tetapi tidak berubah juga tidak dikirim
     * agar batas pergerakan stoknya tidak bergeser tanpa penghitungan ulang.
     */
    const form = document.getElementById('stockOpnameForm');

    if (form) {

        form.addEventListener('submit', function (event) {

            const rows = form.querySelectorAll('.product-row');

            let changedCount = 0;

            rows.forEach(function (row) {

                const physicalInput = row.querySelector('.physical-input');
                const notesInput = row.querySelector('.notes-input');
                const detailId = row.querySelector('.detail-id');

                if (!physicalInput || !detailId) {
                    return;
                }

                const physicalValue = physicalInput.value.trim();

                const originalPhysical = physicalInput.dataset.original ?? '';

                const currentNotes = notesInput?.value ?? '';
                const originalNotes = notesInput?.dataset.original ?? '';

                const isNewCount = row.dataset.counted === '0'
                    && physicalValue !== '';

                const isPhysicalChanged = physicalValue !== originalPhysical;

                const isNotesChanged = currentNotes !== originalNotes;

                const shouldSubmit = isNewCount
                    || (row.dataset.counted === '1' && (
                        isPhysicalChanged || isNotesChanged
                    ));

                if (shouldSubmit && physicalValue !== '') {

                    changedCount++;

                } else {

                    detailId.disabled = true;
                    physicalInput.disabled = true;

                    if (notesInput) {
                        notesInput.disabled = true;
                    }

                }

            });

            if (changedCount === 0) {

                event.preventDefault();

                alert('Belum ada hasil penghitungan baru atau perubahan yang perlu disimpan.');

            }

        });

    }

});
</script>
@endpush