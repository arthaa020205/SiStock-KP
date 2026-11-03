@extends('layouts.app')

@section('title', 'Edit Barang Masuk - SiStock')
@section('page-title', 'Edit Barang Masuk')

@section('content')
<div class="space-y-6">

    <div>
        <h2 class="text-xl font-semibold text-gray-800">
            Edit Draft Barang Masuk
        </h2>
        <p class="text-sm text-gray-500 mt-1">
            Perbarui informasi transaksi sebelum diposting.
        </p>
    </div>

    @if ($errors->any())
        <div class="p-4 rounded-lg bg-red-50 border border-red-200 text-red-700">
            <ul class="list-disc list-inside text-sm space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('petugas.inbounds.update', $inbound->id) }}"
          method="POST"
          class="space-y-6">
        @csrf
        @method('PUT')

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-5">
            <h3 class="font-semibold text-gray-800">Informasi Transaksi</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Tanggal Barang Masuk
                    </label>

                    <input type="datetime-local"
                           name="transaction_date"
                           value="{{ old('transaction_date', $inbound->transaction_date->format('Y-m-d\TH:i')) }}"
                           required
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Supplier
                    </label>

                    <select name="supplier_id"
                            required
                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Pilih Supplier</option>

                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->id }}"
                                {{ old('supplier_id', $inbound->supplier_id) == $supplier->id ? 'selected' : '' }}>
                                {{ $supplier->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Catatan
                    </label>

                    <textarea name="notes"
                              rows="3"
                              class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">{{ old('notes', $inbound->notes) }}</textarea>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-5">

            <div class="flex items-center justify-between">
                <h3 class="font-semibold text-gray-800">Detail Barang</h3>

                <button type="button"
                        id="add-item"
                        class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700">
                    + Tambah Produk
                </button>
            </div>

            <div id="items-container" class="space-y-5">

                @foreach ($inbound->details as $index => $detail)
                    @php
                        $batchDetail = $detail->batchDetails->first();
                        $batchStock = $batchDetail?->destinationBatchStock;
                        $batch = $batchStock?->productBatch;
                    @endphp

                    <div class="item-row border border-gray-200 rounded-lg p-5">

                        <div class="flex justify-between items-center mb-4">
                            <h4 class="font-semibold text-gray-700 item-title">
                                Produk {{ $index + 1 }}
                            </h4>

                            <button type="button"
                                    class="remove-item text-red-600 text-sm hover:text-red-800">
                                Hapus
                            </button>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Produk
                                </label>

                                <select name="items[{{ $index }}][product_id]"
                                        required
                                        class="w-full rounded-lg border-gray-300">
                                    <option value="">Pilih Produk</option>

                                    @foreach ($products as $product)
                                        <option value="{{ $product->id }}"
                                            {{ $detail->product_id == $product->id ? 'selected' : '' }}>
                                            {{ $product->code }} - {{ $product->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Satuan
                                </label>

                                <select name="items[{{ $index }}][unit_id]"
                                        required
                                        class="w-full rounded-lg border-gray-300">
                                    <option value="">Pilih Satuan</option>

                                    @foreach ($units as $unit)
                                        <option value="{{ $unit->id }}"
                                            {{ $detail->unit_id == $unit->id ? 'selected' : '' }}>
                                            {{ $unit->name }} ({{ $unit->symbol }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Jumlah
                                </label>

                                <input type="number"
                                       name="items[{{ $index }}][quantity]"
                                       min="0.001"
                                       step="0.001"
                                       value="{{ $detail->quantity }}"
                                       required
                                       class="w-full rounded-lg border-gray-300">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Kode Batch
                                </label>

                                <input type="text"
                                       name="items[{{ $index }}][batch_code]"
                                       value="{{ $batch?->batch_code }}"
                                       required
                                       class="w-full rounded-lg border-gray-300">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Tanggal Kedaluwarsa
                                </label>

                                <input type="date"
                                       name="items[{{ $index }}][expiry_date]"
                                       value="{{ $batch?->expiry_date?->format('Y-m-d') }}"
                                       class="w-full rounded-lg border-gray-300">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Lokasi Rak
                                </label>

                                <select name="items[{{ $index }}][rack_location_id]"
                                        required
                                        class="w-full rounded-lg border-gray-300">
                                    <option value="">Pilih Lokasi Rak</option>

                                    @foreach ($rackLocations as $rack)
                                        <option value="{{ $rack->id }}"
                                            {{ $batchStock?->rack_location_id == $rack->id ? 'selected' : '' }}>
                                            {{ $rack->code }} - {{ $rack->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                        </div>
                    </div>
                @endforeach

            </div>
        </div>

        <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3">

            <a href="{{ route('petugas.inbounds.index') }}"
               class="px-5 py-2.5 text-center border border-gray-300 text-gray-700 text-sm rounded-lg hover:bg-gray-50">
                Kembali
            </a>

            <button type="submit"
                    class="px-5 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                Simpan Perubahan
            </button>

        </div>
    </form>
</div>

<script>
    let itemIndex = {{ $inbound->details->count() }};

    const container = document.getElementById('items-container');
    const addButton = document.getElementById('add-item');

    function updateItemTitles() {
        const rows = container.querySelectorAll('.item-row');

        rows.forEach((row, index) => {
            row.querySelector('.item-title').textContent = `Produk ${index + 1}`;

            const removeButton = row.querySelector('.remove-item');

            if (rows.length > 1) {
                removeButton.classList.remove('hidden');
            } else {
                removeButton.classList.add('hidden');
            }
        });
    }

    addButton.addEventListener('click', function () {
        const firstRow = container.querySelector('.item-row');
        const newRow = firstRow.cloneNode(true);

        newRow.querySelectorAll('input, select').forEach(function (field) {
            field.name = field.name.replace(
                /items\[\d+\]/,
                `items[${itemIndex}]`
            );

            field.value = '';
        });

        container.appendChild(newRow);
        itemIndex++;

        updateItemTitles();
    });

    container.addEventListener('click', function (event) {
        if (event.target.classList.contains('remove-item')) {
            const rows = container.querySelectorAll('.item-row');

            if (rows.length > 1) {
                event.target.closest('.item-row').remove();
                updateItemTitles();
            }
        }
    });

    updateItemTitles();
</script>
@endsection