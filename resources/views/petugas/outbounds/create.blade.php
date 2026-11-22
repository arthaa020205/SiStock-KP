@extends('layouts.app')

@section('title', 'Barang Keluar - SiStock')
@section('page-title', 'Barang Keluar')

@section('content')
<div class="space-y-5">

    <div>
        <a href="{{ route('petugas.outbounds.index') }}"
           class="text-sm text-blue-600 hover:text-blue-800">
            ← Kembali ke Barang Keluar
        </a>
        <p class="text-sm text-gray-500 mt-2">
            Pilih produk, masukkan jumlah, lalu simpan transaksi.
        </p>
    </div>

    @if ($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 p-4">
            <p class="mb-2 font-semibold text-red-700">Transaksi belum dapat disimpan:</p>
            <ul class="list-inside list-disc space-y-1 text-sm text-red-600">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('petugas.outbounds.store') }}"
          method="POST"
          id="outboundForm">
        @csrf

        {{-- INFORMASI TRANSAKSI --}}
        <div class="mb-5 rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <h2 class="mb-4 font-semibold text-gray-800">Informasi Transaksi</h2>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">

                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700">
                        Tanggal Pengeluaran
                    </label>
                    <input type="date"
                           name="transaction_date"
                           value="{{ old('transaction_date', date('Y-m-d')) }}"
                           required
                           class="w-full rounded-lg border border-gray-300 px-3 py-2.5 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700">
                        Jenis Pengeluaran
                    </label>
                    <select name="reason"
                            id="reason"
                            required
                            class="w-full rounded-lg border border-gray-300 px-3 py-2.5 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                        <option value="">Pilih jenis</option>
                        <option value="penjualan">Penjualan</option>
                        <option value="transfer_toko">Transfer ke Toko</option>
                        <option value="rusak">Barang Rusak</option>
                        <option value="kadaluarsa">Barang Kadaluarsa</option>
                        <option value="pemakaian">Pemakaian</option>
                        <option value="lainnya">Lainnya</option>
                    </select>
                </div>

                <div id="customerWrapper" class="hidden">
                    <label class="mb-2 block text-sm font-medium text-gray-700">
                        Pelanggan
                    </label>
                    <select name="customer_id"
                            id="customer_id"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2.5">
                        <option value="">Pilih pelanggan (opsional)</option>
                        @foreach ($customers as $customer)
                            <option value="{{ $customer->id }}">
                                {{ $customer->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-3">
                    <label class="mb-2 block text-sm font-medium text-gray-700">
                        Catatan
                    </label>
                    <textarea name="notes"
                              rows="2"
                              placeholder="Catatan tambahan jika diperlukan..."
                              class="w-full rounded-lg border border-gray-300 px-3 py-2.5">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        {{-- POS --}}
        <div class="grid grid-cols-1 items-start gap-5 xl:grid-cols-12">

            {{-- KATALOG --}}
            <section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm xl:col-span-7">

                <div class="border-b border-gray-200 p-5">
                    <div class="mb-4 flex items-center justify-between">
                        <div>
                            <h2 class="font-semibold text-gray-800">Katalog Produk</h2>
                            <p class="mt-1 text-xs text-gray-500">
                                Cari produk untuk ditambahkan ke transaksi.
                            </p>
                        </div>
                        <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">
                            {{ count($productData) }} Produk
                        </span>
                    </div>

                    <input type="text"
                           id="productSearch"
                           placeholder="Cari nama atau kode produk..."
                           class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                </div>

                <div id="productCatalog"
                     class="grid grid-cols-1 gap-3 p-4 sm:grid-cols-2">
                </div>

                <div id="noProductFound"
                     class="hidden p-8 text-center text-sm text-gray-500">
                    Produk tidak ditemukan.
                </div>
            </section>

            {{-- KERANJANG --}}
            <section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm xl:sticky xl:top-4 xl:col-span-5">

                <div class="flex items-center justify-between border-b border-gray-200 p-5">
                    <div>
                        <h2 class="font-semibold text-gray-800">Keranjang Transaksi</h2>
                        <p class="mt-1 text-xs text-gray-500">
                            Atur jumlah barang yang akan dikeluarkan.
                        </p>
                    </div>

                    <span id="cartCount"
                          class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">
                        0 Item
                    </span>
                </div>

                <div id="cartItems" class="max-h-[650px] space-y-4 overflow-y-auto p-4">
                    <div id="emptyCart"
                         class="rounded-xl border-2 border-dashed border-gray-200 p-8 text-center">
                        <div class="mb-2 text-3xl">🛒</div>
                        <p class="font-medium text-gray-700">Keranjang masih kosong</p>
                        <p class="mt-1 text-xs text-gray-400">
                            Pilih produk dari katalog untuk memulai.
                        </p>
                    </div>
                </div>

                <div class="border-t border-gray-200 p-5">

                    <div class="mb-3 flex justify-between text-sm">
                        <span class="text-gray-500">Jumlah jenis produk</span>
                        <span id="summaryItems" class="font-semibold">0</span>
                    </div>

                    <div class="mb-3 flex justify-between text-sm">
                        <span class="text-gray-500">Total kuantitas</span>
                        <span id="summaryQuantity" class="font-semibold">0</span>
                    </div>

                    <div id="saleTotalWrapper"
                         class="mb-4 hidden border-t border-gray-200 pt-4">
                        <div class="flex items-center justify-between">
                            <span class="font-semibold text-gray-700">Total Penjualan</span>
                            <span id="grandTotal"
                                  class="text-xl font-bold text-blue-700">
                                Rp0
                            </span>
                        </div>
                    </div>

                    <button type="submit"
                            id="submitButton"
                            disabled
                            class="w-full rounded-lg bg-blue-600 px-4 py-3 font-semibold text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:bg-gray-300">
                        Simpan sebagai Draft
                    </button>

                    <a href="{{ route('petugas.outbounds.index') }}"
                       class="mt-3 block text-center text-sm text-gray-500 hover:text-gray-800">
                        Batal
                    </a>
                </div>
            </section>

        </div>
    </form>
</div>

<script>
const products = {{ Illuminate\Support\Js::from($productData) }};

let cart = [];
let nextIndex = 0;

const reasonSelect = document.getElementById('reason');
const cartItems = document.getElementById('cartItems');
const productCatalog = document.getElementById('productCatalog');

const money = value => new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0
}).format(value || 0);

const number = value => Number(value || 0).toLocaleString('id-ID', {
    maximumFractionDigits: 3
});

function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, char => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    })[char]);
}

function getProduct(id) {
    return products.find(p => String(p.id) === String(id));
}

function getUnitOptions(product) {
    const units = [{
        id: product.base_unit_id,
        name: product.base_unit_name,
        symbol: product.base_unit_symbol,
        factor: 1
    }, ...product.conversions];

    return units.map(unit => `
        <option value="${unit.unit_id ?? unit.id}"
                data-factor="${unit.factor}">
            ${escapeHtml(unit.unit_name ?? unit.name)}
            (${escapeHtml(unit.unit_symbol ?? unit.symbol)})
        </option>
    `).join('');
}

function getUnitPrice(product, unitId) {
    // Jika menggunakan satuan dasar produk
    if (String(product.base_unit_id) === String(unitId)) {
        const price = Number(product.price_per_base_unit);

        return price > 0 ? price : null;
    }

    // Jika menggunakan satuan konversi
    const conversion = product.conversions.find(
        conversion => String(conversion.unit_id) === String(unitId)
    );

    if (!conversion || conversion.selling_price === null) {
        return null;
    }

    const price = Number(conversion.selling_price);

    return price > 0 ? price : null;
}

function isSale() {
    return reasonSelect.value === 'penjualan';
}

function requiresManualBatch() {
    return ['rusak', 'kadaluarsa'].includes(reasonSelect.value);
}

function getFactor(item) {
    return Number(
        item.querySelector('.unit-select')?.selectedOptions[0]?.dataset.factor || 1
    );
}

function getQuantity(item) {
    return Number(item.querySelector('.quantity-input')?.value || 0);
}

function getBaseQuantity(item) {
    return getQuantity(item) * getFactor(item);
}

function renderCatalog() {
    const keyword = document.getElementById('productSearch').value.toLowerCase();

    const filtered = products.filter(product =>
        product.name.toLowerCase().includes(keyword) ||
        product.code.toLowerCase().includes(keyword)
    );

    productCatalog.innerHTML = filtered.map(product => {
        const totalStock = product.stocks.reduce(
            (total, stock) => total + Number(stock.quantity), 0
        );

        return `
            <div class="rounded-xl border border-gray-200 p-4 transition hover:border-blue-300 hover:shadow-sm">

                <div class="mb-3 flex items-start justify-between gap-2">
                    <div>
                        <p class="text-xs text-gray-400">${escapeHtml(product.code)}</p>
                        <h3 class="mt-1 font-semibold text-gray-800">
                            ${escapeHtml(product.name)}
                        </h3>
                    </div>

                    <button type="button"
                            onclick="addToCart(${product.id})"
                            class="shrink-0 rounded-lg bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-700 hover:bg-blue-100">
                        + Tambah
                    </button>
                </div>

                <div class="border-t border-gray-100 pt-3">
                    <p class="text-xs text-gray-500">Stok tersedia</p>
                    <p class="mt-1 text-sm font-semibold text-gray-800">
                        ${number(totalStock)} ${escapeHtml(product.base_unit_symbol)}
                    </p>
                </div>
            </div>
        `;
    }).join('');

    document.getElementById('noProductFound')
        .classList.toggle('hidden', filtered.length > 0);
}

function addToCart(productId) {
    const product = getProduct(productId);

    if (!product) return;

    const existing = cart.find(item => item.product.id === product.id);

    if (existing) {
        const input = existing.element.querySelector('.quantity-input');
        input.value = Number(input.value || 0) + 1;
        updateItem(existing.element);
        return;
    }

    const index = nextIndex++;

    const item = document.createElement('div');
    item.className = 'rounded-xl border border-gray-200 p-4';
    item.dataset.index = index;

    item.innerHTML = `
        <input type="hidden"
               name="items[${index}][product_id]"
               value="${product.id}">

        <div class="mb-3 flex items-start justify-between gap-2">
            <div>
                <p class="text-xs text-gray-400">${escapeHtml(product.code)}</p>
                <h3 class="font-semibold text-gray-800">
                    ${escapeHtml(product.name)}
                </h3>
            </div>

            <button type="button"
                    class="remove-item text-sm font-medium text-red-600 hover:text-red-800">
                Hapus
            </button>
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-600">
                    Satuan
                </label>
                <select name="items[${index}][unit_id]"
                        class="unit-select w-full rounded-lg border border-gray-300 px-2 py-2 text-sm"
                        required>
                    ${getUnitOptions(product)}
                </select>
            </div>

            <div>
                <label class="mb-1 block text-xs font-medium text-gray-600">
                    Jumlah
                </label>
                <input type="number"
                       name="items[${index}][quantity]"
                       class="quantity-input w-full rounded-lg border border-gray-300 px-2 py-2 text-sm"
                       min="0.001"
                       step="0.001"
                       value="1"
                       required>
            </div>
        </div>

        <div class="mt-3 rounded-lg bg-gray-50 p-3">
            <div class="flex justify-between text-xs">
                <span class="text-gray-500">Setara satuan dasar</span>
                <span class="base-quantity font-semibold text-gray-800">1</span>
            </div>

            <div class="pricing-wrapper mt-3 hidden border-t border-gray-200 pt-3">
                <div class="flex justify-between text-xs">
                    <span class="text-gray-500">Harga satuan</span>
                    <span class="unit-price font-semibold">-</span>
                </div>
                <div class="mt-2 flex justify-between text-sm">
                    <span class="font-medium text-gray-700">Subtotal</span>
                    <span class="item-subtotal font-bold text-blue-700">-</span>
                </div>
                <p class="price-warning mt-2 hidden text-xs text-red-600"></p>
            </div>
        </div>

        <div class="mt-3">
            <button type="button"
                    class="manual-toggle text-xs font-semibold text-blue-700 hover:text-blue-900">
                + Atur Batch Manual
            </button>

            <div class="manual-area mt-3 hidden">
                <p class="mb-2 text-xs text-gray-500">
                    Pilih batch dan rak serta masukkan jumlah yang diambil.
                </p>

                <fieldset class="allocation-fields space-y-2">
                    <div class="allocation-rows space-y-2"></div>

                    <button type="button"
                            class="add-allocation rounded-lg bg-gray-100 px-3 py-2 text-xs font-medium text-gray-700 hover:bg-gray-200">
                        + Tambah Alokasi
                    </button>
                </fieldset>
            </div>

            <p class="batch-mode mt-2 text-xs text-green-700">
                ✓ Batch dan rak akan dipilih otomatis oleh sistem (FEFO).
            </p>
        </div>
    `;

    cartItems.appendChild(item);

    const entry = { product, element: item };
    cart.push(entry);

    item.querySelector('.remove-item').addEventListener('click', () => {
        cart = cart.filter(row => row.element !== item);
        item.remove();
        updateCart();
    });

    item.querySelector('.unit-select').addEventListener('change', () => {
        resetManualAllocations(item);
        updateItem(item);
    });

    item.querySelector('.quantity-input').addEventListener('input', () => {
        updateItem(item);
    });

    item.querySelector('.manual-toggle').addEventListener('click', () => {
        const area = item.querySelector('.manual-area');
        const fieldset = item.querySelector('.allocation-fields');

        const opening = area.classList.contains('hidden');

        area.classList.toggle('hidden', !opening);
        fieldset.disabled = !opening;

        item.querySelector('.manual-toggle').textContent =
            opening ? '− Gunakan Alokasi Otomatis' : '+ Atur Batch Manual';

        if (opening && item.querySelector('.allocation-rows').children.length === 0) {
            addAllocationRow(item);
        }

        updateBatchMode(item);
    });

    item.querySelector('.add-allocation').addEventListener('click', () => {
        addAllocationRow(item);
    });

    updateItem(item);
    updateCart();
}

function resetManualAllocations(item) {
    const rows = item.querySelector('.allocation-rows');

    if (rows) rows.innerHTML = '';

    if (!item.querySelector('.manual-area').classList.contains('hidden')) {
        addAllocationRow(item);
    }
}

function addAllocationRow(item) {
    const productId = item.querySelector('input[name$="[product_id]"]').value;
    const product = getProduct(productId);

    const rows = item.querySelector('.allocation-rows');
    const index = item.dataset.index;
    const rowIndex = rows.children.length;

    const row = document.createElement('div');
    row.className = 'rounded-lg border border-gray-200 p-3';

    row.innerHTML = `
        <div class="mb-2 flex items-center justify-between">
            <span class="text-xs font-semibold text-gray-600">
                Alokasi ${rowIndex + 1}
            </span>
            <button type="button"
                    class="remove-allocation text-xs text-red-600">
                Hapus
            </button>
        </div>

        <select name="items[${index}][allocations][${rowIndex}][batch_stock_id]"
                class="batch-select mb-2 w-full rounded-lg border border-gray-300 px-2 py-2 text-xs"
                required>
            <option value="">Pilih batch / rak</option>
            ${product.stocks.map(stock => `
                <option value="${stock.id}">
                    ${escapeHtml(stock.batch_code)}
                    | ${escapeHtml(stock.rack)}
                    | Stok ${number(stock.quantity)}
                    | ED ${escapeHtml(stock.expiry_date)}
                </option>
            `).join('')}
        </select>

        <input type="number"
               name="items[${index}][allocations][${rowIndex}][base_quantity]"
               class="allocation-quantity w-full rounded-lg border border-gray-300 px-2 py-2 text-xs"
               min="0.001"
               step="0.001"
               placeholder="Jumlah dalam ${escapeHtml(product.base_unit_symbol)}"
               required>
    `;

    rows.appendChild(row);

    row.querySelector('.remove-allocation').addEventListener('click', () => {
        row.remove();
        updateBatchMode(item);
    });

    updateBatchMode(item);
}

function updateBatchMode(item) {
    const area = item.querySelector('.manual-area');
    const fieldset = item.querySelector('.allocation-fields');
    const message = item.querySelector('.batch-mode');

    const isManual = !area.classList.contains('hidden');

    fieldset.disabled = !isManual;

    message.textContent = isManual
        ? 'Pengaturan batch manual aktif. Pastikan jumlah alokasi sesuai.'
        : '✓ Batch dan rak akan dipilih otomatis oleh sistem (FEFO).';

    message.className = isManual
        ? 'batch-mode mt-2 text-xs text-orange-700'
        : 'batch-mode mt-2 text-xs text-green-700';
}

function updateItem(item) {
    const product = getProduct(
        item.querySelector('input[name$="[product_id]"]').value
    );

    const quantity = getQuantity(item);
    const baseQuantity = getBaseQuantity(item);

    item.querySelector('.base-quantity').textContent =
        `${number(baseQuantity)} ${product.base_unit_symbol}`;

    const pricing = item.querySelector('.pricing-wrapper');
    const priceWarning = item.querySelector('.price-warning');

    if (isSale()) {
        pricing.classList.remove('hidden');

        const unitSelect = item.querySelector('.unit-select');
        const unitId = unitSelect.value;
        const selectedUnit = unitSelect.selectedOptions[0];

        const unitName = selectedUnit
            ? selectedUnit.textContent.trim()
            : 'satuan ini';

        const price = getUnitPrice(product, unitId);

        if (price === null) {
            item.querySelector('.unit-price').textContent = '-';
            item.querySelector('.item-subtotal').textContent = '-';

            priceWarning.textContent =
                `Harga jual untuk ${unitName} belum diatur atau masih bernilai 0.`;

            priceWarning.classList.remove('hidden');
        } else {
            item.querySelector('.unit-price').textContent = money(price);

            item.querySelector('.item-subtotal').textContent =
                money(price * quantity);

            priceWarning.classList.add('hidden');
        }
    } else {
        pricing.classList.add('hidden');
    }

    updateCart();
}

function updateCart() {
    const count = cart.length;

    const totalQuantity = cart.reduce((total, entry) => {
        return total + getQuantity(entry.element);
    }, 0);

    document.getElementById('emptyCart')
        ?.classList.toggle('hidden', count > 0);

    document.getElementById('cartCount').textContent = `${count} Item`;

    document.getElementById('summaryItems').textContent = count;

    document.getElementById('summaryQuantity').textContent =
        number(totalQuantity);

    let total = 0;
    let hasInvalidPrice = false;

    cart.forEach(entry => {
        if (!isSale()) {
            return;
        }

        const unitId = entry.element.querySelector('.unit-select').value;

        const price = getUnitPrice(entry.product, unitId);

        if (price === null) {
            hasInvalidPrice = true;
            return;
        }

        total += price * getQuantity(entry.element);
    });

    document.getElementById('saleTotalWrapper')
        .classList.toggle('hidden', !isSale());

    document.getElementById('grandTotal').textContent =
        isSale() && hasInvalidPrice
            ? 'Periksa harga'
            : money(total);

    document.getElementById('submitButton').disabled =
        count === 0 || (isSale() && hasInvalidPrice);
}

function validateManualAllocation(item) {
    const area = item.querySelector('.manual-area');

    if (area.classList.contains('hidden')) {
        return true;
    }

    const required = getBaseQuantity(item);

    const rows = Array.from(
        item.querySelectorAll('.allocation-rows > div')
    );

    if (rows.length === 0) {
        alert('Tambahkan minimal satu alokasi batch.');
        return false;
    }

    let allocated = 0;

    for (const row of rows) {
        const batchId = row.querySelector('.batch-select').value;
        const quantity = Number(
            row.querySelector('.allocation-quantity').value || 0
        );

        if (!batchId || quantity <= 0) {
            alert('Lengkapi pilihan batch dan jumlah alokasi.');
            return false;
        }

        allocated += quantity;
    }

    if (Math.abs(required - allocated) > 0.001) {
        alert(
            'Jumlah alokasi batch harus sama dengan jumlah barang dalam satuan dasar.'
        );
        return false;
    }

    return true;
}

function updateReason() {
    const sale = isSale();
    const manualRequired = requiresManualBatch();

    document.getElementById('customerWrapper')
        .classList.toggle('hidden', !sale);

    document.getElementById('saleTotalWrapper')
        .classList.toggle('hidden', !sale);

    if (!sale) {
        document.getElementById('customer_id').value = '';
    }

    cart.forEach(entry => {
        const item = entry.element;
        const area = item.querySelector('.manual-area');
        const fieldset = item.querySelector('.allocation-fields');

        if (manualRequired) {
            area.classList.remove('hidden');
            fieldset.disabled = false;

            item.querySelector('.manual-toggle').classList.add('hidden');

            if (item.querySelector('.allocation-rows').children.length === 0) {
                addAllocationRow(item);
            }
        } else {
            area.classList.add('hidden');
            fieldset.disabled = true;
            item.querySelector('.manual-toggle').classList.remove('hidden');
        }

        updateBatchMode(item);
        updateItem(item);
    });
}

document.getElementById('productSearch')
    .addEventListener('input', renderCatalog);

reasonSelect.addEventListener('change', updateReason);

document.getElementById('outboundForm')
    .addEventListener('submit', function (event) {

        if (cart.length === 0) {
            event.preventDefault();
            alert('Tambahkan minimal satu produk.');
            return;
        }

        for (const entry of cart) {
            const item = entry.element;
            const product = entry.product;

            if (getQuantity(item) <= 0) {
                event.preventDefault();
                alert(`Masukkan jumlah untuk ${product.name}.`);
                return;
            }

            if (isSale()) {
                const price = getUnitPrice(
                    product,
                    item.querySelector('.unit-select').value
                );

                if (price === null || price <= 0) {
                    event.preventDefault();
                    alert(`Harga jual ${product.name} belum diatur.`);
                    return;
                }
            }

            if (requiresManualBatch() || !item.querySelector('.manual-area').classList.contains('hidden')) {
                if (!validateManualAllocation(item)) {
                    event.preventDefault();
                    return;
                }
            }
        }

        const button = document.getElementById('submitButton');
        button.disabled = true;
        button.textContent = 'Menyimpan...';
    });

renderCatalog();
updateCart();
</script>
@endsection