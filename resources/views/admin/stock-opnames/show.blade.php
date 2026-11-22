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
            ->filter(fn ($detail) => abs((float) $detail->difference_quantity) > 0.0005)
            ->count();

        $requests = $stockOpname->details
            ->flatMap(fn ($detail) => $detail->adjustmentRequests);

        $totalPending = $requests->where('status', 'pending')->count();
        $totalApproved = $requests->where('status', 'approved')->count();
        $totalRejected = $requests->where('status', 'rejected')->count();

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
                    Pemeriksaan dan penyesuaian stok berdasarkan total setiap produk.
                </p>
            </div>

            <span class="h-fit w-fit rounded-full px-3 py-1 text-sm font-medium {{ $statusClasses[$stockOpname->status] ?? 'bg-gray-100 text-gray-700' }}">
                {{ $statusLabels[$stockOpname->status] ?? ucfirst($stockOpname->status) }}
            </span>
        </div>

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">

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
                <p class="text-sm text-gray-500">Petugas Gudang</p>
                <p class="mt-1 font-medium text-gray-800">
                    {{ $stockOpname->creator->name ?? '-' }}
                </p>
            </div>

            <div>
                <p class="text-sm text-gray-500">Tanggal Pengajuan</p>
                <p class="mt-1 font-medium text-gray-800">
                    {{ $stockOpname->updated_at?->format('d-m-Y H:i') ?? '-' }}
                </p>
            </div>

            <div>
                <p class="text-sm text-gray-500">Admin Pemeriksa</p>
                <p class="mt-1 font-medium text-gray-800">
                    {{ $stockOpname->approver->name ?? '-' }}
                </p>
            </div>

            <div>
                <p class="text-sm text-gray-500">Tanggal Persetujuan</p>
                <p class="mt-1 font-medium text-gray-800">
                    {{ $stockOpname->approved_at?->format('d-m-Y H:i') ?? '-' }}
                </p>
            </div>

            <div class="sm:col-span-2 lg:col-span-3">
                <p class="text-sm text-gray-500">Keterangan</p>
                <p class="mt-1 font-medium text-gray-800">
                    {{ $stockOpname->notes ?: '-' }}
                </p>
            </div>

        </div>

    </div>

    {{-- Ringkasan --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-gray-500">Total Produk Diperiksa</p>
            <p class="mt-2 text-2xl font-bold text-gray-800">
                {{ $totalDetails }}
            </p>
        </div>

        <div class="rounded-xl border border-red-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-gray-500">Produk Memiliki Selisih</p>
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

        <div class="rounded-xl border border-green-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-gray-500">Sudah Diproses</p>
            <p class="mt-2 text-2xl font-bold text-green-600">
                {{ $totalApproved + $totalRejected }}
            </p>
            <p class="mt-1 text-xs text-gray-500">
                {{ $totalApproved }} disetujui · {{ $totalRejected }} ditolak
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
                Perbandingan stok sistem dan stok fisik berdasarkan total setiap produk.
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">

                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-600">No.</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-600">Produk</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase text-gray-600">Stok Sistem</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase text-gray-600">Stok Fisik</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase text-gray-600">Selisih</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-600">Catatan Petugas</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold uppercase text-gray-600">Status Penyesuaian</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">

                    @forelse ($stockOpname->details as $detail)

                        @php
                            $product = $detail->product
                                ?? $detail->batchStock?->productBatch?->product;

                            $unit = $product?->baseUnit;

                            $difference = (float) $detail->difference_quantity;

                            $differenceClass = $difference > 0
                                ? 'text-green-600'
                                : ($difference < 0 ? 'text-red-600' : 'text-gray-700');

                            $requestsForDetail = $detail->adjustmentRequests;

                            $pending = $requestsForDetail->where('status', 'pending')->count();
                            $approved = $requestsForDetail->where('status', 'approved')->count();
                            $rejected = $requestsForDetail->where('status', 'rejected')->count();
                        @endphp

                        <tr class="hover:bg-gray-50">

                            <td class="px-5 py-4 text-sm text-gray-600">
                                {{ $loop->iteration }}
                            </td>

                            <td class="px-5 py-4">
                                <p class="text-sm font-semibold text-gray-800">
                                    {{ $product->name ?? 'Produk tidak ditemukan' }}
                                </p>

                                <p class="text-xs text-gray-500">
                                    {{ $product->code ?? '-' }}
                                </p>

                                @if (!$detail->product_id)
                                    <span class="mt-1 inline-block rounded-full bg-gray-100 px-2 py-1 text-xs text-gray-600">
                                        Data Stock Opname Lama
                                    </span>
                                @endif
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-right text-sm text-gray-700">
                                {{ number_format((float) $detail->system_quantity, 3, ',', '.') }}
                                {{ $unit->symbol ?? '' }}
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-right text-sm font-medium text-gray-800">
                                {{ number_format((float) $detail->physical_quantity, 3, ',', '.') }}
                                {{ $unit->symbol ?? '' }}
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-right text-sm font-semibold {{ $differenceClass }}">
                                {{ $difference > 0 ? '+' : '' }}{{ number_format($difference, 3, ',', '.') }}
                                {{ $unit->symbol ?? '' }}
                            </td>

                            <td class="px-5 py-4 text-sm text-gray-700">
                                {{ $detail->notes ?: '-' }}
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-center">

                                @if ($pending > 0)
                                    <span class="rounded-full bg-yellow-100 px-3 py-1 text-xs font-medium text-yellow-700">
                                        {{ $pending }} Menunggu
                                    </span>
                                @elseif ($approved > 0 && $rejected > 0)
                                    <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-medium text-blue-700">
                                        Sebagian Diproses
                                    </span>
                                @elseif ($approved > 0)
                                    <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-700">
                                        Disetujui
                                    </span>
                                @elseif ($rejected > 0)
                                    <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-medium text-red-700">
                                        Ditolak
                                    </span>
                                @elseif (abs($difference) <= 0.0005)
                                    <span class="text-xs text-gray-500">
                                        Tidak Ada Selisih
                                    </span>
                                @else
                                    <span class="text-xs text-gray-500">
                                        Belum Ada Permintaan
                                    </span>
                                @endif

                            </td>

                        </tr>

                        {{-- Rincian Permintaan Penyesuaian --}}
                        @foreach ($requestsForDetail as $adjustmentRequest)

                            <tr class="bg-gray-50">
                                <td colspan="7" class="px-5 py-4">

                                    <div class="rounded-lg border border-gray-200 bg-white p-4">

                                        <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-start">

                                            <div class="space-y-2">

                                                <p class="text-sm font-semibold text-gray-800">
                                                    Permintaan Penyesuaian Stok
                                                </p>

                                                <p class="text-sm text-gray-600">
                                                    Alasan:
                                                    {{ $adjustmentRequest->request_reason ?: '-' }}
                                                </p>

                                                <p class="text-xs text-gray-500">
                                                    Diajukan oleh:
                                                    {{ $adjustmentRequest->requester->name ?? '-' }}
                                                </p>

                                                @if ($adjustmentRequest->reviewer)
                                                    <p class="text-xs text-gray-500">
                                                        Diproses oleh:
                                                        {{ $adjustmentRequest->reviewer->name }}
                                                    </p>
                                                @endif

                                                @if ($adjustmentRequest->reviewed_at)
                                                    <p class="text-xs text-gray-500">
                                                        Waktu diproses:
                                                        {{ $adjustmentRequest->reviewed_at->format('d-m-Y H:i') }}
                                                    </p>
                                                @endif

                                                @if ($adjustmentRequest->approval_notes)
                                                    <div class="rounded-lg bg-gray-50 p-3">
                                                        <p class="text-xs font-semibold text-gray-700">
                                                            Catatan Admin
                                                        </p>
                                                        <p class="mt-1 text-sm text-gray-600">
                                                            {{ $adjustmentRequest->approval_notes }}
                                                        </p>
                                                    </div>
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

                                            <span class="h-fit w-fit rounded-full px-3 py-1 text-xs font-medium {{ $requestClasses[$adjustmentRequest->status] ?? 'bg-gray-100 text-gray-700' }}">
                                                {{ $requestLabels[$adjustmentRequest->status] ?? ucfirst($adjustmentRequest->status) }}
                                            </span>

                                        </div>

                                        {{-- Aksi Admin --}}
                                        @if ($adjustmentRequest->status === 'pending' && $stockOpname->status === 'submitted')

                                            <div class="mt-4 flex flex-col gap-3 border-t border-gray-100 pt-4 sm:flex-row sm:items-start">

                                                <form
                                                    action="{{ route('admin.stock-adjustments.approve', $adjustmentRequest) }}"
                                                    method="POST"
                                                    onsubmit="return confirm('Yakin ingin menyetujui penyesuaian stok produk ini? Stok akan langsung diperbarui.')"
                                                >
                                                    @csrf

                                                    <button type="submit"
                                                            class="rounded-lg bg-green-600 px-4 py-2.5 text-xs font-semibold text-white hover:bg-green-700">
                                                        Setujui Penyesuaian
                                                    </button>
                                                </form>

                                                <form
                                                    action="{{ route('admin.stock-adjustments.reject', $adjustmentRequest) }}"
                                                    method="POST"
                                                    onsubmit="return confirm('Yakin ingin menolak permintaan penyesuaian stok ini?')"
                                                    class="flex w-full flex-col gap-2 sm:max-w-md"
                                                >
                                                    @csrf

                                                    <textarea
                                                        name="approval_notes"
                                                        rows="2"
                                                        required
                                                        maxlength="1000"
                                                        placeholder="Masukkan alasan penolakan..."
                                                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-xs focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-100"
                                                    ></textarea>

                                                    <button type="submit"
                                                            class="w-fit rounded-lg bg-red-600 px-4 py-2.5 text-xs font-semibold text-white hover:bg-red-700">
                                                        Tolak Permintaan
                                                    </button>
                                                </form>

                                            </div>

                                        @endif

                                    </div>

                                </td>
                            </tr>

                        @endforeach

                    @empty

                        <tr>
                            <td colspan="7" class="px-6 py-10 text-center text-sm text-gray-500">
                                Belum ada detail penghitungan stok.
                            </td>
                        </tr>

                    @endforelse

                </tbody>
            </table>
        </div>

        {{-- Kembali --}}
        <div class="border-t border-gray-200 p-6">
            <a href="{{ route('admin.stock-opnames.index') }}"
               class="inline-flex rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50">
                Kembali
            </a>
        </div>

    </div>

</div>
@endsection