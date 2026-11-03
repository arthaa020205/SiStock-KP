@extends('layouts.app')

@section('title', 'Transfer Baru - SiStock')
@section('page-title', 'Transfer Lokasi Rak')

@section('content')
<div class="space-y-6">

    <div>
        <a href="{{ route('petugas.transfers.index') }}"
           class="text-sm text-gray-600 hover:text-gray-900">
            &larr; Kembali ke Transfer Lokasi Rak
        </a>
    </div>

    @if($errors->any())
        <div class="rounded-lg bg-red-100 p-4 text-sm text-red-700">
            <ul class="list-inside list-disc">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('petugas.transfers.store') }}" method="POST"
          id="transferForm" class="space-y-6">
        @csrf

        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="mb-5 text-lg font-semibold text-gray-800">
                Informasi Transfer
            </h2>

            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">
                    Tanggal Transfer <span class="text-red-500">*</span>
                </label>

                <input type="date"
                       name="transaction_date"
                       value="{{ old('transaction_date', date('Y-m-d')) }}"
                       required
                       class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-blue-500">
            </div>

            <div class="mt-5">
                <label class="mb-2 block text-sm font-medium text-gray-700">
                    Catatan
                </label>

                <textarea name="notes" rows="3"
                          placeholder="Masukkan catatan transfer jika diperlukan..."
                          class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-blue-500">{{ old('notes') }}</textarea>
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">

            <div class="mb-5 flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
                <div>
                    <h2 class="text-lg font-semibold text-gray-800">
                        Barang yang Dipindahkan
                    </h2>

                    <p class="mt-1 text-sm text-gray-500">
                        Pilih produk, batch, rak asal, rak tujuan, dan jumlah transfer.
                    </p>
                </div>

                <button type="button" id="addItem"
                        class="rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
                    + Tambah Barang
                </button>
            </div>

            <div id="itemsContainer" class="space-y-5"></div>

        </div>

        <div class="flex flex-col-reverse justify-end gap-3 sm:flex-row">
            <a href="{{ route('petugas.transfers.index') }}"
               class="rounded-lg border border-gray-300 px-5 py-2.5 text-center text-sm font-semibold text-gray-700 hover:bg-gray-50">
                Batal
            </a>

            <button type="submit"
                    class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
                Simpan Draft Transfer
            </button>
        </div>

    </form>
</div>

<script>
const products = {{ Illuminate\Support\Js::from($productData) }};

let itemIndex = 0;

const container = document.getElementById('itemsContainer');
const addItemButton = document.getElementById('addItem');

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

function getProduct(productId) {
    return products.find(product => String(product.id) === String(productId));
}

function addItem() {
    const index = itemIndex++;

    const productOptions = products.map(product => `
        <option value="${product.id}">
            ${escapeHtml(product.code)} - ${escapeHtml(product.name)}
        </option>
    `).join('');

    const html = `
        <div class="item-card rounded-xl border border-gray-200 p-5"
             data-index="${index}">

            <div class="mb-5 flex items-center justify-between">
                <h3 class="font-semibold text-gray-800">
                    Barang #${index + 1}
                </h3>

                <button type="button"
                        class="remove-item rounded-lg bg-red-50 px-3 py-2 text-xs font-semibold text-red-600 hover:bg-red-100">
                    Hapus
                </button>
            </div>

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">

                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700">
                        Produk <span class="text-red-500">*</span>
                    </label>

                    <select name="items[${index}][product_id]"
                            class="product-select w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm"
                            required>
                        <option value="">Pilih Produk</option>
                        ${productOptions}
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700">
                        Batch dan Rak Asal <span class="text-red-500">*</span>
                    </label>

                    <select name="items[${index}][source_batch_stock_id]"
                            class="source-select w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm"
                            required disabled>
                        <option value="">Pilih Produk Terlebih Dahulu</option>
                    </select>

                    <p class="source-info mt-2 text-xs text-gray-500">
                        Stok tersedia: -
                    </p>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700">
                        Rak Tujuan <span class="text-red-500">*</span>
                    </label>

                    <select name="items[${index}][destination_rack_location_id]"
                            class="destination-select w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm"
                            required disabled>
                        <option value="">Pilih Batch Terlebih Dahulu</option>
                        @foreach($rackLocations as $rack)
                            <option value="{{ $rack->id }}">
                                {{ $rack->code }} - {{ $rack->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700">
                        Jumlah Transfer <span class="text-red-500">*</span>
                    </label>

                    <input type="number"
                           name="items[${index}][quantity]"
                           class="quantity-input w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm"
                           min="0.001"
                           step="0.001"
                           placeholder="Masukkan jumlah"
                           required disabled>

                    <p class="unit-info mt-2 text-xs text-gray-500">
                        Satuan: -
                    </p>
                </div>

            </div>

            <div class="mt-5 rounded-lg bg-blue-50 p-3 text-sm text-blue-700">
                Pastikan rak asal dan rak tujuan berbeda. Batch yang dipindahkan tetap sama.
            </div>

        </div>
    `;

    container.insertAdjacentHTML('beforeend', html);

    const card = container.querySelector(
        `.item-card[data-index="${index}"]`
    );

    const productSelect = card.querySelector('.product-select');
    const sourceSelect = card.querySelector('.source-select');
    const destinationSelect = card.querySelector('.destination-select');
    const quantityInput = card.querySelector('.quantity-input');
    const sourceInfo = card.querySelector('.source-info');
    const unitInfo = card.querySelector('.unit-info');

    productSelect.addEventListener('change', function () {
        const product = getProduct(this.value);

        sourceSelect.innerHTML = '<option value="">Pilih Batch dan Rak Asal</option>';
        destinationSelect.value = '';
        quantityInput.value = '';
        quantityInput.disabled = true;
        destinationSelect.disabled = true;

        if (!product) {
            sourceSelect.disabled = true;
            sourceInfo.textContent = 'Stok tersedia: -';
            unitInfo.textContent = 'Satuan: -';
            return;
        }

        const stocks = product.stocks || [];

        if (stocks.length === 0) {
            sourceSelect.innerHTML =
                '<option value="">Tidak ada stok tersedia</option>';

            sourceSelect.disabled = true;
            sourceInfo.textContent = 'Stok tersedia: 0';
            unitInfo.textContent =
                'Satuan: ' + product.base_unit_symbol;

            return;
        }

        stocks.forEach(stock => {
            const option = document.createElement('option');

            option.value = stock.id;
            option.textContent =
                `${stock.batch_code} - ${stock.rack_code} (${stock.quantity} ${product.base_unit_symbol})`;

            sourceSelect.appendChild(option);
        });

        sourceSelect.disabled = false;

        unitInfo.textContent =
            'Satuan: ' + product.base_unit_name +
            ' (' + product.base_unit_symbol + ')';

        sourceInfo.textContent = 'Stok tersedia: -';
    });

    sourceSelect.addEventListener('change', function () {
        const product = getProduct(productSelect.value);

        const stock = product?.stocks.find(
            item => String(item.id) === String(this.value)
        );

        destinationSelect.value = '';
        quantityInput.value = '';

        if (!stock) {
            destinationSelect.disabled = true;
            quantityInput.disabled = true;
            sourceInfo.textContent = 'Stok tersedia: -';
            return;
        }

        sourceInfo.textContent =
            `Stok tersedia: ${stock.quantity} ${product.base_unit_symbol} | ED: ${stock.expiry_date}`;

        quantityInput.max = stock.quantity;
        quantityInput.disabled = false;
        destinationSelect.disabled = false;

        Array.from(destinationSelect.options).forEach(option => {
            if (!option.value) return;

            option.disabled =
                String(option.value) === String(stock.rack_location_id);
        });
    });

    quantityInput.addEventListener('input', function () {
        const max = Number(this.max);

        if (Number(this.value) > max) {
            this.setCustomValidity(
                'Jumlah transfer melebihi stok pada rak asal.'
            );
        } else if (Number(this.value) <= 0) {
            this.setCustomValidity(
                'Jumlah transfer harus lebih dari 0.'
            );
        } else {
            this.setCustomValidity('');
        }
    });

    card.querySelector('.remove-item').addEventListener('click', function () {
        card.remove();
        updateItemNumbers();
    });
}

function updateItemNumbers() {
    container.querySelectorAll('.item-card').forEach((card, index) => {
        card.querySelector('h3').textContent = `Barang #${index + 1}`;
    });
}

addItemButton.addEventListener('click', addItem);

document.getElementById('transferForm').addEventListener('submit', function (event) {
    const cards = container.querySelectorAll('.item-card');

    if (cards.length === 0) {
        event.preventDefault();
        alert('Tambahkan minimal satu barang untuk transfer.');
        return;
    }

    let valid = true;

    cards.forEach(card => {
        const product = card.querySelector('.product-select');
        const source = card.querySelector('.source-select');
        const destination = card.querySelector('.destination-select');
        const quantity = card.querySelector('.quantity-input');

        const max = Number(quantity.max);
        const amount = Number(quantity.value);

        if (
            !product.value ||
            !source.value ||
            !destination.value ||
            !amount ||
            amount <= 0 ||
            amount > max ||
            source.selectedOptions[0]?.value === destination.value
        ) {
            valid = false;
        }
    });

    if (!valid) {
        event.preventDefault();
        alert('Periksa kembali produk, batch, rak tujuan, dan jumlah transfer.');
    }
});

addItem();
</script>
@endsection