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

    @php
        $totalDetails = $stockOpname->details->count();

        $totalDifferences = $stockOpname->details
            ->filter(fn ($detail) => (float) $detail->difference_quantity != 0)
            ->count();

        $totalPending = $stockOpname->details
            ->flatMap(fn ($detail) => $detail->adjustmentRequests)
            ->where('status', 'pending')
            ->count();

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

    {{-- Informasi Stock Opname --}}
    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">

        <div class="mb-5 flex flex-col justify-between gap-3 sm:flex-row sm:items-center">

            <div>
                <h2 class="text-lg font-semibold text-gray-800">
                    Informasi Stock Opname
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Informasi pemeriksaan stok yang diajukan Petugas Gudang.
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

    </div>

    {{-- Ringkasan --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-gray-500">Total Barang Diperiksa</p>
            <p class="mt-2 text-2xl font-bold text-gray-800">
                {{ $totalDetails }}
            </p>
        </div>

        <div class="rounded-xl border border-red-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-gray-500">Barang Memiliki Selisih</p>
            <p class="mt-2 text-2xl font-bold text-red-600">
                {{ $totalDifferences }}
            </p>
        </div>

        <div class="rounded-xl border border-yellow-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-gray-500">Menunggu Persetujuan</p>
            <p class="mt-2 text-2xl font-bold text-yellow-600">
                {{ $totalPending }}
            </p>
        </div>

    </div>

    {{-- Hasil Penghitungan --}}
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

        <div class="border-b border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-800">
                Hasil Penghitungan Stok
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Perbandingan stok sistem dengan hasil penghitungan fisik Petugas Gudang.
            </p>
        </div>

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

                        @php
                            $difference = (float) $detail->difference_quantity;

                            $differenceClass = $difference > 0
                                ? 'text-green-600'
                                : ($difference < 0 ? 'text-red-600' : 'text-gray-700');

                            $requests = $detail->adjustmentRequests;
                        @endphp

                        <tr class="hover:bg-gray-50">

                            <td class="px-5 py-4 text-sm text-gray-600">
                                {{ $loop->iteration }}
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

                            <td class="whitespace-nowrap px-5 py-4 text-right text-sm text-gray-700">
                                {{ number_format((float) $detail->system_quantity, 3, ',', '.') }}
                                {{ $detail->batchStock->productBatch->product->baseUnit->symbol ?? '' }}
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-right text-sm font-medium text-gray-800">
                                {{ number_format((float) $detail->physical_quantity, 3, ',', '.') }}
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-right text-sm font-semibold {{ $differenceClass }}">
                                {{ $difference > 0 ? '+' : '' }}{{ number_format($difference, 3, ',', '.') }}
                            </td>

                            <td class="px-5 py-4 text-sm text-gray-700">
                                {{ $detail->notes ?: '-' }}
                            </td>

                        </tr>

                        {{-- Informasi Permintaan Penyesuaian --}}
                        @foreach ($requests as $adjustmentRequest)

                            <tr class="bg-gray-50">

                                <td colspan="8" class="px-5 py-3">

                                    <div class="flex flex-col justify-between gap-2 sm:flex-row sm:items-center">

                                        <div>
                                            <p class="text-sm font-medium text-gray-800">
                                                Permintaan Penyesuaian Stok
                                            </p>

                                            <p class="mt-1 text-xs text-gray-500">
                                                {{ $adjustmentRequest->request_reason }}
                                            </p>

                                            @if ($adjustmentRequest->approval_notes)
                                                <p class="mt-1 text-xs text-gray-500">
                                                    Catatan Admin:
                                                    {{ $adjustmentRequest->approval_notes }}
                                                </p>
                                            @endif
                                        </div>

                                        @php
                                            $requestClasses = [
                                                'pending' => 'bg-yellow-100 text-yellow-700',
                                                'approved' => 'bg-green-100 text-green-700',
                                                'rejected' => 'bg-red-100 text-red-700',
                                            ];

                                            $requestLabels = [
                                                'pending' => 'Menunggu Persetujuan',
                                                'approved' => 'Disetujui',
                                                'rejected' => 'Ditolak',
                                            ];
                                        @endphp

                                        <div class="flex flex-col items-start gap-3 sm:items-end">

                                            {{-- Status Permintaan --}}
                                            <span class="h-fit w-fit rounded-full px-3 py-1 text-xs font-medium {{ $requestClasses[$adjustmentRequest->status] ?? 'bg-gray-100 text-gray-700' }}">
                                                {{ $requestLabels[$adjustmentRequest->status] ?? ucfirst($adjustmentRequest->status) }}
                                            </span>

                                            {{-- Tombol Persetujuan Admin --}}
                                            @if ($adjustmentRequest->status === 'pending' && $stockOpname->status === 'submitted')

                                                <div class="flex flex-wrap gap-2">

                                                    {{-- Form Setujui --}}
                                                    <form
                                                        action="{{ route('admin.stock-adjustments.approve', $adjustmentRequest) }}"
                                                        method="POST"
                                                        onsubmit="return confirm('Apakah Anda yakin ingin menyetujui penyesuaian stok ini?')"
                                                    >
                                                        @csrf

                                                        <button
                                                            type="submit"
                                                            class="rounded-lg bg-green-600 px-4 py-2 text-xs font-medium text-white hover:bg-green-700"
                                                        >
                                                            Setujui
                                                        </button>
                                                    </form>

                                                    {{-- Form Tolak --}}
                                                    <form
                                                        action="{{ route('admin.stock-adjustments.reject', $adjustmentRequest) }}"
                                                        method="POST"
                                                        onsubmit="return confirm('Apakah Anda yakin ingin menolak penyesuaian stok ini?')"
                                                        class="flex flex-col gap-2"
                                                    >
                                                        @csrf

                                                        <textarea
                                                            name="approval_notes"
                                                            rows="2"
                                                            required
                                                            maxlength="1000"
                                                            placeholder="Alasan penolakan..."
                                                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-xs focus:border-red-500 focus:outline-none"
                                                        ></textarea>

                                                        <button
                                                            type="submit"
                                                            class="rounded-lg bg-red-600 px-4 py-2 text-xs font-medium text-white hover:bg-red-700"
                                                        >
                                                            Tolak
                                                        </button>
                                                    </form>

                                                </div>

                                            @endif

                                        </div>

                                    </div>

                                </td>

                            </tr>

                        @endforeach

                    @empty

                        <tr>
                            <td colspan="8" class="px-6 py-10 text-center text-sm text-gray-500">
                                Belum ada detail penghitungan stok.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        <div class="border-t border-gray-200 p-6">

            <a href="{{ route('admin.stock-opnames.index') }}"
               class="inline-flex rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50">
                Kembali
            </a>

        </div>

    </div>

</div>
@endsection