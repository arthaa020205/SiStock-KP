
@extends('layouts.app')

@section('title', 'Tambah Barang Keluar - SiStock')
@section('page-title', 'Tambah Barang Keluar')

@section('content')
<div class="space-y-6">

    <div>
        <a href="{{ route('petugas.outbounds.index') }}"
           class="text-sm text-blue-600 hover:text-blue-800">
            ← Kembali ke Barang Keluar
        </a>
        <p class="text-sm text-gray-500 mt-2">
            Buat transaksi pengeluaran stok dengan beberapa barang dan batch sekaligus.
        </p>
    </div>

    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 rounded-xl p-4">
            <p class="font-semibold text-red-700 mb-2">
                Data belum dapat disimpan:
            </p>
            <ul class="list-disc list-inside text-sm text-red-600 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('petugas.outbounds.store') }}"
          method="POST"
          id="outboundForm"
          class="space-y-6">
        @csrf

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-5">
                Informasi Transaksi
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Tanggal Pengeluaran
                    </label>
                    <input type="date"
                           name="transaction_date"
                           value="{{ old('transaction_date', date('Y-m-d')) }}"
                           required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Alasan Pengeluaran
                    </label>
                    <select name="reason"
                            id="reason"
                            required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <option value="">Pilih alasan</option>
                        <option value="penjualan">Penjualan</option>
                        <option value="transfer_toko">Transfer ke Toko</option>
                        <option value="rusak">Barang Rusak</option>
                        <option value="kadaluarsa">Barang Kadaluarsa</option>
                        <option value="pemakaian">Pemakaian</option>
                        <option value="lainnya">Lainnya</option>
                    </select>
                </div>

                <div id="customerWrapper" class="hidden">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Pelanggan
                    </label>
                    <select name="customer_id"
                            id="customer_id"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <option value="">Pilih pelanggan (opsional)</option>
                        @foreach ($customers as $customer)
                            <option value="{{ $customer->id }}">
                                {{ $customer->name }}
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
                              placeholder="Tambahkan keterangan transaksi jika diperlukan..."
                              class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none">{{ old('notes') }}</textarea>
                </div>

            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-5">
                <div>
                    <h2 class="text-lg font-semibold text-gray-800">
                        Daftar Barang Keluar
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">
                        Tambahkan barang yang akan dikeluarkan dari gudang.
                    </p>
                </div>

                <button type="button"
                        id="addItemButton"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-lg text-sm font-medium">
                    + Tambah Barang
                </button>
            </div>

            <div id="itemsContainer" class="space-y-5"></div>

            <div id="emptyItems"
                 class="border-2 border-dashed border-gray-200 rounded-xl p-8 text-center">
                <p class="text-gray-500 text-sm">
                    Belum ada barang yang ditambahkan.
                </p>
                <p class="text-gray-400 text-xs mt-1">
                    Klik tombol Tambah Barang untuk memulai.
                </p>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row justify-end gap-3">
            <a href="{{ route('petugas.outbounds.index') }}"
               class="px-5 py-2.5 border border-gray-300 rounded-lg text-gray-700 text-sm font-medium text-center hover:bg-gray-50">
                Batal
            </a>

            <button type="submit"
                    id="submitButton"
                    class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium">
                Simpan sebagai Draft
            </button>
        </div>

    </form>
</div>

<script>
const products = {{ Illuminate\Support\Js::from($productData) }};
let itemIndex = 0;

const itemsContainer = document.getElementById('itemsContainer');
const emptyItems = document.getElementById('emptyItems');
const addItemButton = document.getElementById('addItemButton');
const outboundForm = document.getElementById('outboundForm');

function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, function (char) {
        return {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        }[char];
    });
}

function formatNumber(value) {
    return Number(value).toLocaleString('id-ID', {
        maximumFractionDigits: 3
    });
}

function getUnitOptions(product) {
    if (!product) {
        return '';
    }

    let units = [{
        id: product.base_unit_id,
        name: product.base_unit_name,
        symbol: product.base_unit_symbol,
        factor: 1
    }];

    product.conversions.forEach(conversion => {
        units.push({
            id: conversion.unit_id,
            name: conversion.unit_name,
            symbol: conversion.unit_symbol,
            factor: conversion.factor
        });
    });

    return units.map(unit => `
        <option value="${unit.id}" data-factor="${unit.factor}">
            ${escapeHtml(unit.name)} (${escapeHtml(unit.symbol)})
        </option>
    `).join('');
}

function getProduct(productId) {
    return products.find(product => String(product.id) === String(productId));
}

function addItem() {
    const index = itemIndex++;

    const item = document.createElement('div');

    item.className = 'border border-gray-200 rounded-xl p-5 space-y-4';
    item.dataset.index = index;

    item.innerHTML = `
        <div class="flex items-center justify-between">
            <h3 class="font-semibold text-gray-800">
                Barang #${index + 1}
            </h3>

            <button type="button"
                    class="remove-item text-red-600 hover:text-red-800 text-sm font-medium">
                Hapus Barang
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Barang
                </label>

                <select name="items[${index}][product_id]"
                        class="product-select w-full border border-gray-300 rounded-lg px-3 py-2.5"
                        required>
                    <option value="">Pilih barang</option>

                    ${products.map(product => `
                        <option value="${product.id}">
                            ${escapeHtml(product.code)} - ${escapeHtml(product.name)}
                        </option>
                    `).join('')}
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Satuan
                </label>

                <select name="items[${index}][unit_id]"
                        class="unit-select w-full border border-gray-300 rounded-lg px-3 py-2.5"
                        required
                        disabled>
                    <option value="">Pilih barang terlebih dahulu</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Jumlah Keluar
                </label>

                <input type="number"
                       name="items[${index}][quantity]"
                       class="quantity-input w-full border border-gray-300 rounded-lg px-3 py-2.5"
                       min="0.001"
                       step="0.001"
                       placeholder="Masukkan jumlah"
                       required
                       disabled>
            </div>

        </div>

        <div class="bg-gray-50 rounded-lg p-3">
            <p class="text-xs text-gray-500">Jumlah dalam satuan dasar</p>
            <p class="base-total font-semibold text-gray-800 mt-1">0</p>
        </div>

        <div class="border-t border-gray-200 pt-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-3">
                <div>
                    <h4 class="text-sm font-semibold text-gray-800">
                        Alokasi Batch / Lokasi Rak
                    </h4>
                    <p class="text-xs text-gray-500 mt-1">
                        Bagikan jumlah pengeluaran ke satu atau beberapa batch.
                    </p>
                </div>

                <button type="button"
                        class="add-allocation bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-2 rounded-lg text-xs font-medium"
                        disabled>
                    + Tambah Batch / Rak
                </button>
            </div>

            <div class="allocations-container space-y-3"></div>

            <p class="allocation-summary text-xs text-gray-500 mt-3">
                Belum ada alokasi batch.
            </p>
        </div>
    `;

    itemsContainer.appendChild(item);

    item.querySelector('.remove-item').addEventListener('click', () => {
        item.remove();
        updateEmptyState();
    });

    item.querySelector('.product-select').addEventListener('change', () => {
        setupProduct(item);
    });

    item.querySelector('.unit-select').addEventListener('change', () => {
        updateAllocation(item);
    });

    item.querySelector('.quantity-input').addEventListener('input', () => {
        updateAllocation(item);
    });

    item.querySelector('.add-allocation').addEventListener('click', () => {
        addAllocation(item);
    });

    updateEmptyState();
}

function setupProduct(item) {
    const productId = item.querySelector('.product-select').value;
    const product = getProduct(productId);

    const unitSelect = item.querySelector('.unit-select');
    const quantityInput = item.querySelector('.quantity-input');
    const addAllocationButton = item.querySelector('.add-allocation');
    const allocationsContainer = item.querySelector('.allocations-container');

    allocationsContainer.innerHTML = '';

    if (!product) {
        unitSelect.innerHTML = '<option value="">Pilih barang terlebih dahulu</option>';
        unitSelect.disabled = true;
        quantityInput.value = '';
        quantityInput.disabled = true;
        addAllocationButton.disabled = true;
        updateAllocation(item);
        return;
    }

    unitSelect.innerHTML = getUnitOptions(product);
    unitSelect.disabled = false;
    quantityInput.disabled = false;
    addAllocationButton.disabled = false;

    addAllocation(item);
    updateAllocation(item);
}

function addAllocation(item) {
    const product = getProduct(item.querySelector('.product-select').value);

    if (!product) {
        return;
    }

    const index = item.dataset.index;
    const allocationsContainer = item.querySelector('.allocations-container');

    const usedBatchIds = Array.from(
        allocationsContainer.querySelectorAll('.batch-select')
    ).map(select => select.value).filter(Boolean);

    const availableStocks = product.stocks.filter(stock => {
        return !usedBatchIds.includes(String(stock.id));
    });

    if (availableStocks.length === 0) {
        alert('Tidak ada batch/rak lain yang tersedia untuk barang ini.');
        return;
    }

    const allocationIndex = allocationsContainer.children.length;

    const row = document.createElement('div');

    row.className = 'grid grid-cols-1 md:grid-cols-12 gap-3 items-end bg-gray-50 p-3 rounded-lg';

    row.innerHTML = `
        <div class="md:col-span-7">
            <label class="block text-xs font-medium text-gray-600 mb-2">
                Batch / Lokasi Rak
            </label>

            <select name="items[${index}][allocations][${allocationIndex}][batch_stock_id]"
                    class="batch-select w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm"
                    required>
                <option value="">Pilih batch / rak</option>

                ${availableStocks.map(stock => `
                    <option value="${stock.id}">
                        Batch: ${escapeHtml(stock.batch_code)}
                        | Rak: ${escapeHtml(stock.rack)}
                        | Stok: ${formatNumber(stock.quantity)}
                        ${escapeHtml(product.base_unit_symbol)}
                        | ED: ${escapeHtml(stock.expiry_date)}
                    </option>
                `).join('')}
            </select>
        </div>

        <div class="md:col-span-4">
            <label class="block text-xs font-medium text-gray-600 mb-2">
                Jumlah Diambil (${escapeHtml(product.base_unit_symbol)})
            </label>

            <input type="number"
                   name="items[${index}][allocations][${allocationIndex}][base_quantity]"
                   class="allocation-quantity w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm"
                   min="0.001"
                   step="0.001"
                   placeholder="Jumlah"
                   required>
        </div>

        <div class="md:col-span-1">
            <button type="button"
                    class="remove-allocation w-full text-red-600 hover:bg-red-50 rounded-lg py-2.5 text-sm"
                    title="Hapus alokasi">
                ✕
            </button>
        </div>
    `;

    allocationsContainer.appendChild(row);

    row.querySelector('.remove-allocation').addEventListener('click', () => {
        row.remove();
        refreshBatchOptions(item);
        updateAllocation(item);
    });

    row.querySelector('.batch-select').addEventListener('change', () => {
        const selectedStock = product.stocks.find(stock =>
            String(stock.id) === row.querySelector('.batch-select').value
        );

        const quantityInput = row.querySelector('.allocation-quantity');

        if (selectedStock) {
            quantityInput.max = selectedStock.quantity;
        } else {
            quantityInput.removeAttribute('max');
        }

        refreshBatchOptions(item);
        updateAllocation(item);
    });

    row.querySelector('.allocation-quantity').addEventListener('input', () => {
        updateAllocation(item);
    });

    refreshBatchOptions(item);
    updateAllocation(item);
}

function refreshBatchOptions(item) {
    const product = getProduct(item.querySelector('.product-select').value);

    if (!product) {
        return;
    }

    const rows = Array.from(
        item.querySelectorAll('.allocations-container > div')
    );

    const selectedIds = rows.map(row =>
        row.querySelector('.batch-select').value
    ).filter(Boolean);

    rows.forEach(row => {
        const select = row.querySelector('.batch-select');
        const currentValue = select.value;

        const availableStocks = product.stocks.filter(stock => {
            return !selectedIds.includes(String(stock.id))
                || String(stock.id) === currentValue;
        });

        select.innerHTML = `
            <option value="">Pilih batch / rak</option>
            ${availableStocks.map(stock => `
                <option value="${stock.id}">
                    Batch: ${escapeHtml(stock.batch_code)}
                    | Rak: ${escapeHtml(stock.rack)}
                    | Stok: ${formatNumber(stock.quantity)}
                    ${escapeHtml(product.base_unit_symbol)}
                    | ED: ${escapeHtml(stock.expiry_date)}
                </option>
            `).join('')}
        `;

        select.value = currentValue;
    });
}

function updateAllocation(item) {
    const product = getProduct(item.querySelector('.product-select').value);

    const quantity = parseFloat(
        item.querySelector('.quantity-input').value
    ) || 0;

    const unitSelect = item.querySelector('.unit-select');
    const factor = parseFloat(
        unitSelect.selectedOptions[0]?.dataset.factor
    ) || 1;

    const requiredBaseQuantity = quantity * factor;

    const allocationInputs = Array.from(
        item.querySelectorAll('.allocation-quantity')
    );

    const allocatedQuantity = allocationInputs.reduce((total, input) => {
        return total + (parseFloat(input.value) || 0);
    }, 0);

    const difference = requiredBaseQuantity - allocatedQuantity;

    item.querySelector('.base-total').textContent =
        `${formatNumber(requiredBaseQuantity)} ${product?.base_unit_symbol ?? ''}`;

    const summary = item.querySelector('.allocation-summary');

    if (requiredBaseQuantity <= 0) {
        summary.textContent = 'Masukkan jumlah barang yang akan dikeluarkan.';
        summary.className = 'allocation-summary text-xs text-gray-500 mt-3';
    } else if (Math.abs(difference) <= 0.001) {
        summary.textContent =
            `Alokasi sesuai: ${formatNumber(allocatedQuantity)} ${product.base_unit_symbol}`;
        summary.className = 'allocation-summary text-xs text-green-700 font-medium mt-3';
    } else if (difference > 0) {
        summary.textContent =
            `Masih kurang ${formatNumber(difference)} ${product.base_unit_symbol} dari jumlah yang dibutuhkan.`;
        summary.className = 'allocation-summary text-xs text-red-600 font-medium mt-3';
    } else {
        summary.textContent =
            `Alokasi melebihi jumlah yang dibutuhkan sebanyak ${formatNumber(Math.abs(difference))} ${product.base_unit_symbol}.`;
        summary.className = 'allocation-summary text-xs text-red-600 font-medium mt-3';
    }
}

function updateEmptyState() {
    emptyItems.classList.toggle('hidden', itemsContainer.children.length > 0);
}

addItemButton.addEventListener('click', addItem);

document.getElementById('reason').addEventListener('change', function () {
    const customerWrapper = document.getElementById('customerWrapper');
    const customerSelect = document.getElementById('customer_id');

    const isSale = this.value === 'penjualan';

    customerWrapper.classList.toggle('hidden', !isSale);
    customerSelect.required = false;

    if (!isSale) {
        customerSelect.value = '';
    }
});

outboundForm.addEventListener('submit', function (event) {
    const items = Array.from(itemsContainer.children);

    if (items.length === 0) {
        event.preventDefault();
        alert('Tambahkan minimal satu barang.');
        return;
    }

    for (const item of items) {
        const product = getProduct(item.querySelector('.product-select').value);

        if (!product) {
            event.preventDefault();
            alert('Pilih barang pada setiap detail transaksi.');
            return;
        }

        const quantity = parseFloat(
            item.querySelector('.quantity-input').value
        ) || 0;

        const factor = parseFloat(
            item.querySelector('.unit-select').selectedOptions[0]?.dataset.factor
        ) || 1;

        const required = quantity * factor;

        const rows = Array.from(
            item.querySelectorAll('.allocations-container > div')
        );

        if (rows.length === 0) {
            event.preventDefault();
            alert(`Tambahkan alokasi batch untuk barang ${product.name}.`);
            return;
        }

        let allocated = 0;

        for (const row of rows) {
            const batchId = row.querySelector('.batch-select').value;
            const allocation = parseFloat(
                row.querySelector('.allocation-quantity').value
            ) || 0;

            const stock = product.stocks.find(stock =>
                String(stock.id) === batchId
            );

            if (!stock || allocation <= 0) {
                event.preventDefault();
                alert(`Periksa pilihan batch dan jumlah alokasi barang ${product.name}.`);
                return;
            }

            if (allocation > stock.quantity) {
                event.preventDefault();
                alert(`Alokasi melebihi stok tersedia pada batch ${stock.batch_code}.`);
                return;
            }

            allocated += allocation;
        }

        if (Math.abs(required - allocated) > 0.001) {
            event.preventDefault();
            alert(`Total alokasi batch barang ${product.name} harus sama dengan jumlah pengeluaran.`);
            return;
        }
    }

    document.getElementById('submitButton').disabled = true;
    document.getElementById('submitButton').textContent = 'Menyimpan...';
});

updateEmptyState();
</script>
@endsection
