<aside class="fixed top-0 left-0 z-50 w-64 h-screen bg-slate-900 text-white flex flex-col overflow-y-auto">

    {{-- Logo --}}
    <div class="px-6 py-5 border-b border-slate-700 flex-shrink-0">
        <h1 class="text-2xl font-bold tracking-wide">SiStock</h1>
        <p class="text-xs text-slate-400 mt-1">
            Sistem Inventory Stock
        </p>
    </div>

    {{-- Menu Navigasi --}}
    <div class="px-4 py-5 flex-1">

        <p class="text-xs uppercase tracking-wider text-slate-400 mb-3">
            Menu Utama
        </p>

        @if(auth()->user()->hasRole('Admin'))

            <a href="{{ route('admin.dashboard') }}"
                class="block px-4 py-3 rounded-lg hover:bg-slate-700">
                Dashboard
            </a>

            <a href="{{ route('admin.stock-opnames.index') }}"
                class="block px-4 py-3 rounded-lg hover:bg-slate-700">
                Stock Opname
            </a>

            <a href="{{ route('admin.stock-movements.index') }}"
                class="block px-4 py-3 rounded-lg hover:bg-slate-700">
                Riwayat Pergerakan Stok
            </a>

            <a href="{{ route('admin.stocks.index') }}"
                class="block px-4 py-3 rounded-lg hover:bg-slate-700">
                Monitoring Stok Barang
            </a>

            <a href="{{ route('admin.activity-logs.index') }}"
                class="block px-4 py-3 rounded-lg hover:bg-slate-700">
                Log Aktivitas
            </a>

        @elseif(auth()->user()->hasRole('Petugas Gudang'))

            <a href="{{ route('petugas.home') }}"
                class="block px-4 py-3 rounded-lg hover:bg-slate-700">
                Halaman Utama
            </a>

            <p class="text-xs uppercase tracking-wider text-slate-400 mt-7 mb-3">
                Data Master
            </p>

            <a href="{{ route('petugas.categories.index') }}"
                class="block px-4 py-3 rounded-lg hover:bg-slate-700">
                Kategori
            </a>

            <a href="{{ route('petugas.suppliers.index') }}"
                class="block px-4 py-3 rounded-lg hover:bg-slate-700">
                Supplier
            </a>

            <a href="{{ route('petugas.customers.index') }}"
                class="block px-4 py-3 rounded-lg hover:bg-slate-700">
                Customer
            </a>

            <a href="{{ route('petugas.units.index') }}"
                class="block px-4 py-3 rounded-lg hover:bg-slate-700">
                Satuan
            </a>

            <a href="{{ route('petugas.rack-locations.index') }}"
                class="block px-4 py-3 rounded-lg hover:bg-slate-700">
                Lokasi Rak
            </a>

            <a href="{{ route('petugas.products.index') }}"
                class="block px-4 py-3 rounded-lg hover:bg-slate-700">
                Produk
            </a>

            <a href="{{ route('petugas.unit-conversions.index') }}"
                class="block px-4 py-3 rounded-lg hover:bg-slate-700">
                Konversi Satuan
            </a>

            <a href="{{ route('petugas.stocks.index') }}"
                class="block px-4 py-3 rounded-lg hover:bg-slate-700">
                Stok Barang
            </a>

            <p class="text-xs uppercase tracking-wider text-slate-400 mt-7 mb-3">
                Transaksi
            </p>

            <a href="{{ route('petugas.inbounds.index') }}"
                class="block px-4 py-3 rounded-lg hover:bg-slate-700">
                Barang Masuk
            </a>

            <a href="{{ route('petugas.outbounds.index') }}"
                class="block px-4 py-3 rounded-lg hover:bg-slate-700">
                Barang Keluar
            </a>

            <a href="{{ route('petugas.transfers.index') }}"
                class="block px-4 py-3 rounded-lg hover:bg-slate-700">
                Transfer Lokasi Rak
            </a>

            <a href="{{ route('petugas.stock-opnames.index') }}"
                class="block px-4 py-3 rounded-lg hover:bg-slate-700">
                Stock Opname
            </a>

        @endif

    </div>

    {{-- Footer Sidebar --}}
    <div class="px-6 py-5 text-xs text-slate-500 border-t border-slate-700 flex-shrink-0">
        © {{ date('Y') }} SiStock
    </div>

</aside>