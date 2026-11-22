@extends('layouts.app')

@section('title', 'Edit Barang Masuk - SiStock')
@section('page-title', 'Edit Barang Masuk')

@section('content')
<div class="space-y-6">

    <div>
        <h2 class="text-xl font-semibold text-gray-800">Edit Draft Barang Masuk</h2>
        <p class="text-sm text-gray-500 mt-1">
            Perbarui informasi faktur dan daftar barang sebelum transaksi diposting.
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

    @php
        $initialItems = old('items') ?? $inbound->details->map(function ($detail) {
            $batchDetail = $detail->batchDetails->first();
            $batchStock = $batchDetail?->destinationBatchStock;
            $batch = $batchStock?->productBatch;

            return [
                'product_id' => $detail->product_id,
                'unit_id' => $detail->unit_id,
                'quantity' => $detail->quantity,
                'batch_code' => $batch?->batch_code ?? '',
                'expiry_date' => $batch?->expiry_date?->format('Y-m-d') ?? '',
                'rack_location_id' => $batchStock?->rack_location_id ?? '',
            ];
        })->values()->all();
    @endphp

    <form action="{{ route('petugas.inbounds.update', $inbound->id) }}"
          method="POST"
          id="inbound-form"
          class="space-y-6">

        @csrf
        @method('PUT')

        {{-- INFORMASI TRANSAKSI --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">

            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="font-semibold text-gray-800">Informasi Transaksi</h3>
            </div>

            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-5">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Nomor Faktur Supplier <span class="text-red-500">*</span>
                    </label>

                    <input type="text"
                           name="supplier_invoice_number"
                           value="{{ old('supplier_invoice_number', $inbound->supplier_invoice_number) }}"
                           maxlength="100"
                           required
                           placeholder="Contoh: INV/2026/001"
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Tanggal Barang Masuk <span class="text-red-500">*</span>
                    </label>

                    <input type="datetime-local"
                           name="transaction_date"
                           value="{{ old('transaction_date', $inbound->transaction_date->format('Y-m-d\TH:i')) }}"
                           required
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Supplier <span class="text-red-500">*</span>
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

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Catatan
                    </label>

                    <textarea name="notes"
                              rows="2"
                              placeholder="Catatan tambahan (opsional)"
                              class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">{{ old('notes', $inbound->notes) }}</textarea>
                </div>

            </div>
        </div>

        {{-- FORM INPUT BARANG --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">

            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="font-semibold text-gray-800">Input Barang</h3>
                <p class="text-sm text-gray-500 mt-1">
                    Gunakan form ini untuk menambahkan barang atau mengubah barang yang sudah ada.
                </p>
            </div>

            <div class="p-6">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                    {{-- PRODUK --}}
                    <div class="md:col-span-2 product-field relative">

                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Produk <span class="text-red-500">*</span>
                        </label>

                        <input type="text"
                               id="product-search"
                               autocomplete="off"
                               placeholder="Cari berdasarkan kode atau nama barang..."
                               class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">

                        <input type="hidden" id="selected-product-id">

                        <div id="product-results"
                             class="hidden absolute z-30 left-0 right-0 mt-1 max-h-60 overflow-y-auto rounded-lg border border-gray-200 bg-white shadow-lg">

                            @foreach ($products as $product)
                                <button type="button"
                                        class="product-result w-full text-left px-4 py-3 hover:bg-blue-50 border-b border-gray-100 last:border-b-0"
                                        data-id="{{ $product->id }}"
                                        data-code="{{ strtolower($product->code) }}"
                                        data-name="{{ strtolower($product->name) }}"
                                        data-label="{{ $product->code }} - {{ $product->name }}">

                                    <div class="font-medium text-gray-800">
                                        {{ $product->code }}
                                    </div>

                                    <div class="text-sm text-gray-500">
                                        {{ $product->name }}
                                    </div>

                                </button>
                            @endforeach

                            <div id="product-no-result"
                                 class="hidden px-4 py-3 text-sm text-gray-500">
                                Produk tidak ditemukan.
                            </div>

                        </div>

                        <p class="text-xs text-gray-500 mt-1">
                            Ketik kode atau nama barang, kemudian pilih produk.
                        </p>
                    </div>

                    {{-- SATUAN --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Satuan <span class="text-red-500">*</span>
                        </label>

                        <select id="unit-id"
                                class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">

                            <option value="">Pilih Satuan</option>

                            @foreach ($units as $unit)
                                <option value="{{ $unit->id }}">
                                    {{ $unit->name }} ({{ $unit->symbol }})
                                </option>
                            @endforeach

                        </select>
                    </div>

                    {{-- JUMLAH --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Jumlah <span class="text-red-500">*</span>
                        </label>

                        <input type="number"
                               id="quantity"
                               min="0.001"
                               step="0.001"
                               placeholder="Masukkan jumlah"
                               class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    {{-- BATCH --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Kode Batch <span class="text-red-500">*</span>
                        </label>

                        <input type="text"
                               id="batch-code"
                               placeholder="Contoh: B-001"
                               class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    {{-- EXPIRED --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Tanggal Kedaluwarsa
                        </label>

                        <input type="date"
                               id="expiry-date"
                               class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    {{-- LOKASI RAK --}}
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Lokasi Rak <span class="text-red-500">*</span>
                        </label>

                        <select id="rack-location-id"
                                class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">

                            <option value="">Pilih Lokasi Rak</option>

                            @foreach ($rackLocations as $rack)
                                <option value="{{ $rack->id }}">
                                    {{ $rack->code }} - {{ $rack->name }}
                                </option>
                            @endforeach

                        </select>
                    </div>

                </div>

                <div class="flex flex-wrap justify-end gap-3 mt-6">

                    <button type="button"
                            id="cancel-edit"
                            class="hidden px-5 py-2.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Batal Edit
                    </button>

                    <button type="button"
                            id="add-item"
                            class="px-5 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">
                        + Tambah ke Daftar
                    </button>

                </div>

            </div>
        </div>

        {{-- TABEL DAFTAR BARANG --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">

            <div class="px-6 py-4 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">

                <div>
                    <h3 class="font-semibold text-gray-800">Daftar Barang Masuk</h3>
                    <p class="text-sm text-gray-500 mt-1">
                        Barang dalam transaksi ini.
                    </p>
                </div>

                <div class="text-sm text-gray-600">
                    Total Barang:
                    <span id="total-items" class="font-semibold text-blue-600">0</span>
                </div>

            </div>

            <div class="overflow-x-auto">

                <table class="min-w-full divide-y divide-gray-200">

                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">No.</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Kode Barang</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Nama Barang</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Satuan</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Jumlah</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Kode Batch</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Expired Date</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Lokasi Rak</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Aksi</th>
                        </tr>
                    </thead>

                    <tbody id="items-table-body" class="divide-y divide-gray-100">
                        <tr>
                            <td colspan="9" class="px-4 py-10 text-center text-sm text-gray-400">
                                Memuat daftar barang...
                            </td>
                        </tr>
                    </tbody>

                </table>

            </div>

            <div id="hidden-items-container"></div>

            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 rounded-b-xl">
                <p class="text-sm text-gray-500">
                    Periksa kembali seluruh barang sebelum menyimpan perubahan.
                </p>
            </div>

        </div>

        {{-- TOMBOL AKSI --}}
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
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const form = document.getElementById('inbound-form');

    const productSearch = document.getElementById('product-search');
    const selectedProductId = document.getElementById('selected-product-id');
    const productResults = document.getElementById('product-results');
    const productNoResult = document.getElementById('product-no-result');

    const unitSelect = document.getElementById('unit-id');
    const quantityInput = document.getElementById('quantity');
    const batchInput = document.getElementById('batch-code');
    const expiryInput = document.getElementById('expiry-date');
    const rackSelect = document.getElementById('rack-location-id');

    const addButton = document.getElementById('add-item');
    const cancelEditButton = document.getElementById('cancel-edit');

    const tableBody = document.getElementById('items-table-body');
    const hiddenContainer = document.getElementById('hidden-items-container');
    const totalItems = document.getElementById('total-items');

    const initialItems = @json($initialItems);

    let items = [];
    let editingIndex = null;

    function getProduct(id) {
        return productResults.querySelector(
            `.product-result[data-id="${CSS.escape(String(id))}"]`
        );
    }

    function getProductName(button) {
        const label = button.dataset.label || '';
        const separator = label.indexOf(' - ');

        return separator >= 0 ? label.substring(separator + 3) : label;
    }

    function getUnitName(id) {
        const option = unitSelect.querySelector(
            `option[value="${CSS.escape(String(id))}"]`
        );

        return option ? option.textContent.trim() : '-';
    }

    function getRackName(id) {
        const option = rackSelect.querySelector(
            `option[value="${CSS.escape(String(id))}"]`
        );

        return option ? option.textContent.trim() : '-';
    }

    /*
    |--------------------------------------------------------------------------
    | PENCARIAN PRODUK
    |--------------------------------------------------------------------------
    */

    productSearch.addEventListener('input', function () {

        selectedProductId.value = '';

        const keyword = this.value.trim().toLowerCase();
        const buttons = productResults.querySelectorAll('.product-result');

        let found = 0;

        buttons.forEach(button => {

            const code = button.dataset.code || '';
            const name = button.dataset.name || '';

            const match = code.includes(keyword) || name.includes(keyword);

            if (keyword !== '' && match) {
                button.classList.remove('hidden');
                found++;
            } else {
                button.classList.add('hidden');
            }

        });

        if (keyword === '') {
            productResults.classList.add('hidden');
            productNoResult.classList.add('hidden');
            return;
        }

        productResults.classList.remove('hidden');
        productNoResult.classList.toggle('hidden', found !== 0);

    });

    productResults.addEventListener('click', function (event) {

        const button = event.target.closest('.product-result');

        if (!button) return;

        selectedProductId.value = button.dataset.id;
        productSearch.value = button.dataset.label;

        productResults.classList.add('hidden');

    });

    document.addEventListener('click', function (event) {

        if (!event.target.closest('.product-field')) {
            productResults.classList.add('hidden');
        }

    });

    /*
    |--------------------------------------------------------------------------
    | RESET FORM INPUT
    |--------------------------------------------------------------------------
    */

    function resetItemForm() {

        productSearch.value = '';
        selectedProductId.value = '';

        unitSelect.value = '';
        quantityInput.value = '';
        batchInput.value = '';
        expiryInput.value = '';
        rackSelect.value = '';

        productResults.classList.add('hidden');

        editingIndex = null;

        addButton.textContent = '+ Tambah ke Daftar';
        addButton.classList.remove('bg-green-600', 'hover:bg-green-700');
        addButton.classList.add('bg-blue-600', 'hover:bg-blue-700');

        cancelEditButton.classList.add('hidden');

    }

    /*
    |--------------------------------------------------------------------------
    | MEMBUAT HIDDEN INPUT
    |--------------------------------------------------------------------------
    */

    function createHiddenInput(name, value) {

        const input = document.createElement('input');

        input.type = 'hidden';
        input.name = name;
        input.value = value ?? '';

        hiddenContainer.appendChild(input);

    }

    /*
    |--------------------------------------------------------------------------
    | RENDER TABEL DAN DATA FORM
    |--------------------------------------------------------------------------
    */

    function renderItems() {

        tableBody.innerHTML = '';
        hiddenContainer.innerHTML = '';

        totalItems.textContent = items.length;

        if (items.length === 0) {

            tableBody.innerHTML = `
                <tr>
                    <td colspan="9" class="px-4 py-10 text-center text-sm text-gray-400">
                        Belum ada barang dalam transaksi ini.
                    </td>
                </tr>
            `;

            return;
        }

        items.forEach((item, index) => {

            const row = document.createElement('tr');

            row.className = 'hover:bg-gray-50';

            const values = [
                index + 1,
                item.code,
                item.name,
                item.unit_name,
                item.quantity,
                item.batch_code,
                item.expiry_date || '-',
                item.rack_name
            ];

            values.forEach((value, cellIndex) => {

                const td = document.createElement('td');

                td.className = 'px-4 py-3 text-sm text-gray-700 whitespace-nowrap';

                if (cellIndex === 4) {
                    td.classList.add('text-right', 'font-medium');
                }

                td.textContent = value;

                row.appendChild(td);

            });

            const actionCell = document.createElement('td');

            actionCell.className = 'px-4 py-3 text-center whitespace-nowrap';

            actionCell.innerHTML = `
                <button type="button"
                        data-action="edit"
                        data-index="${index}"
                        class="text-blue-600 hover:text-blue-800 text-sm font-medium mr-3">
                    Edit
                </button>

                <button type="button"
                        data-action="delete"
                        data-index="${index}"
                        class="text-red-600 hover:text-red-800 text-sm font-medium">
                    Hapus
                </button>
            `;

            row.appendChild(actionCell);
            tableBody.appendChild(row);

            createHiddenInput(`items[${index}][product_id]`, item.product_id);
            createHiddenInput(`items[${index}][unit_id]`, item.unit_id);
            createHiddenInput(`items[${index}][quantity]`, item.quantity);
            createHiddenInput(`items[${index}][batch_code]`, item.batch_code);
            createHiddenInput(`items[${index}][expiry_date]`, item.expiry_date);
            createHiddenInput(`items[${index}][rack_location_id]`, item.rack_location_id);

        });

    }

    /*
    |--------------------------------------------------------------------------
    | TAMBAH BARANG / SIMPAN EDIT
    |--------------------------------------------------------------------------
    */

    addButton.addEventListener('click', function () {

        const productId = selectedProductId.value;
        const productButton = productId ? getProduct(productId) : null;

        const unitId = unitSelect.value;
        const quantity = quantityInput.value;
        const batchCode = batchInput.value.trim();
        const expiryDate = expiryInput.value;
        const rackId = rackSelect.value;

        if (!productId || !productButton) {
            alert('Silakan pilih produk dari hasil pencarian.');
            productSearch.focus();
            return;
        }

        if (!unitId) {
            alert('Silakan pilih satuan.');
            unitSelect.focus();
            return;
        }

        if (!quantity || Number(quantity) <= 0) {
            alert('Jumlah barang harus lebih dari 0.');
            quantityInput.focus();
            return;
        }

        if (!batchCode) {
            alert('Kode batch wajib diisi.');
            batchInput.focus();
            return;
        }

        if (!rackId) {
            alert('Silakan pilih lokasi rak.');
            rackSelect.focus();
            return;
        }

        const item = {
            product_id: productId,
            code: productButton.dataset.label.split(' - ')[0],
            name: getProductName(productButton),
            unit_id: unitId,
            unit_name: getUnitName(unitId),
            quantity: quantity,
            batch_code: batchCode,
            expiry_date: expiryDate,
            rack_location_id: rackId,
            rack_name: getRackName(rackId)
        };

        if (editingIndex !== null) {
            items[editingIndex] = item;
        } else {
            items.push(item);
        }

        renderItems();
        resetItemForm();

    });

    /*
    |--------------------------------------------------------------------------
    | EDIT DAN HAPUS BARANG
    |--------------------------------------------------------------------------
    */

    tableBody.addEventListener('click', function (event) {

        const button = event.target.closest('button[data-action]');

        if (!button) return;

        const index = Number(button.dataset.index);
        const action = button.dataset.action;

        if (action === 'edit') {

            const item = items[index];

            editingIndex = index;

            selectedProductId.value = item.product_id;
            productSearch.value = item.code + ' - ' + item.name;

            unitSelect.value = item.unit_id;
            quantityInput.value = item.quantity;
            batchInput.value = item.batch_code;
            expiryInput.value = item.expiry_date;
            rackSelect.value = item.rack_location_id;

            addButton.textContent = 'Simpan Perubahan';

            addButton.classList.remove('bg-blue-600', 'hover:bg-blue-700');
            addButton.classList.add('bg-green-600', 'hover:bg-green-700');

            cancelEditButton.classList.remove('hidden');

            window.scrollTo({
                top: productSearch.getBoundingClientRect().top + window.scrollY - 150,
                behavior: 'smooth'
            });

        }

        if (action === 'delete') {

            if (!confirm('Yakin ingin menghapus barang ini dari transaksi?')) {
                return;
            }

            items.splice(index, 1);

            if (editingIndex === index) {
                resetItemForm();
            } else if (editingIndex !== null && editingIndex > index) {
                editingIndex--;
            }

            renderItems();

        }

    });

    cancelEditButton.addEventListener('click', resetItemForm);

    /*
    |--------------------------------------------------------------------------
    | VALIDASI SEBELUM MENYIMPAN
    |--------------------------------------------------------------------------
    */

    form.addEventListener('submit', function (event) {

        if (items.length === 0) {

            event.preventDefault();

            alert('Transaksi harus memiliki minimal satu barang.');

            productSearch.focus();

        }

    });

    /*
    |--------------------------------------------------------------------------
    | MEMUAT BARANG LAMA / OLD INPUT
    |--------------------------------------------------------------------------
    */

    initialItems.forEach(oldItem => {

        const productButton = getProduct(oldItem.product_id);

        if (!productButton) {
            return;
        }

        items.push({
            product_id: String(oldItem.product_id),
            code: productButton.dataset.label.split(' - ')[0],
            name: getProductName(productButton),
            unit_id: String(oldItem.unit_id),
            unit_name: getUnitName(oldItem.unit_id),
            quantity: oldItem.quantity,
            batch_code: oldItem.batch_code || '',
            expiry_date: oldItem.expiry_date || '',
            rack_location_id: String(oldItem.rack_location_id),
            rack_name: getRackName(oldItem.rack_location_id)
        });

    });

    renderItems();

});
</script>
@endpush