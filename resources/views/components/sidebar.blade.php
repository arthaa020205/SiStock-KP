<aside
    class="fixed top-0 left-0 z-50 flex h-screen w-64 flex-col overflow-hidden border-r border-white/60 text-slate-700 shadow-[8px_0_40px_rgba(15,23,42,0.06)]"
    style="
        background: linear-gradient(160deg, rgba(255,255,255,0.88), rgba(239,246,255,0.78));
        backdrop-filter: blur(24px);
        -webkit-backdrop-filter: blur(24px);
    "
>
    {{-- Dekorasi Background --}}
    <div class="pointer-events-none absolute -right-20 -top-20 h-56 w-56 rounded-full bg-blue-300/20 blur-3xl"></div>
    <div class="pointer-events-none absolute -bottom-24 -left-20 h-56 w-56 rounded-full bg-indigo-300/20 blur-3xl"></div>

    {{-- Logo --}}
    <div class="relative flex flex-shrink-0 items-center gap-3 border-b border-slate-200/70 px-5 py-6">
        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white shadow-lg shadow-blue-500/25">
            <i class="fas fa-boxes-stacked text-xl"></i>
        </div>

        <div>
            <h1 class="text-xl font-extrabold tracking-tight text-slate-800">
                SiStock
            </h1>
            <p class="mt-0.5 text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-400">
                Inventory Management
            </p>
        </div>
    </div>

    {{-- Menu Navigasi --}}
    <div class="relative flex-1 space-y-7 overflow-y-auto px-4 py-6">

        {{-- ================= ADMIN ================= --}}
        @if(auth()->user()->hasRole('Admin'))

            <div>
                <p class="mb-3 px-3 text-[10px] font-bold uppercase tracking-[0.18em] text-slate-400">
                    Menu Utama
                </p>

                <nav class="space-y-1.5">

                    <a href="{{ route('admin.dashboard') }}"
                        class="group flex items-center gap-3 rounded-xl px-3.5 py-3 text-sm font-medium transition-all duration-200
                        {{ request()->routeIs('admin.dashboard')
                            ? 'bg-gradient-to-r from-blue-500 to-indigo-500 text-white shadow-lg shadow-blue-500/20'
                            : 'text-slate-600 hover:bg-white/80 hover:text-blue-600 hover:shadow-sm' }}">
                        <i class="fas fa-house w-5 text-center text-base {{ request()->routeIs('admin.dashboard') ? 'text-white' : 'text-slate-400 group-hover:text-blue-500' }}"></i>
                        <span>Dashboard</span>
                        @if(request()->routeIs('admin.dashboard'))
                            <i class="fas fa-chevron-right ml-auto text-[10px] opacity-80"></i>
                        @endif
                    </a>

                    <a href="{{ route('admin.users.index') }}"
                        class="group flex items-center gap-3 rounded-xl px-3.5 py-3 text-sm font-medium transition-all duration-200
                        {{ request()->routeIs('admin.users.*')
                            ? 'bg-gradient-to-r from-blue-500 to-indigo-500 text-white shadow-lg shadow-blue-500/20'
                            : 'text-slate-600 hover:bg-white/80 hover:text-blue-600 hover:shadow-sm' }}">
                        <i class="fas fa-users w-5 text-center text-base {{ request()->routeIs('admin.users.*') ? 'text-white' : 'text-slate-400 group-hover:text-blue-500' }}"></i>
                        <span>Pengguna</span>
                    </a>

                    <a href="{{ route('admin.stock-opnames.index') }}"
                        class="group flex items-center gap-3 rounded-xl px-3.5 py-3 text-sm font-medium transition-all duration-200
                        {{ request()->routeIs('admin.stock-opnames.*')
                            ? 'bg-gradient-to-r from-blue-500 to-indigo-500 text-white shadow-lg shadow-blue-500/20'
                            : 'text-slate-600 hover:bg-white/80 hover:text-blue-600 hover:shadow-sm' }}">
                        <i class="fas fa-clipboard-check w-5 text-center text-base {{ request()->routeIs('admin.stock-opnames.*') ? 'text-white' : 'text-slate-400 group-hover:text-blue-500' }}"></i>
                        <span>Stock Opname</span>
                    </a>

                    <a href="{{ route('admin.stock-movements.index') }}"
                        class="group flex items-center gap-3 rounded-xl px-3.5 py-3 text-sm font-medium transition-all duration-200
                        {{ request()->routeIs('admin.stock-movements.*')
                            ? 'bg-gradient-to-r from-blue-500 to-indigo-500 text-white shadow-lg shadow-blue-500/20'
                            : 'text-slate-600 hover:bg-white/80 hover:text-blue-600 hover:shadow-sm' }}">
                        <i class="fas fa-arrow-right-arrow-left w-5 text-center text-base {{ request()->routeIs('admin.stock-movements.*') ? 'text-white' : 'text-slate-400 group-hover:text-blue-500' }}"></i>
                        <span>Pergerakan Stok</span>
                    </a>

                    <a href="{{ route('admin.stocks.index') }}"
                        class="group flex items-center gap-3 rounded-xl px-3.5 py-3 text-sm font-medium transition-all duration-200
                        {{ request()->routeIs('admin.stocks.*')
                            ? 'bg-gradient-to-r from-blue-500 to-indigo-500 text-white shadow-lg shadow-blue-500/20'
                            : 'text-slate-600 hover:bg-white/80 hover:text-blue-600 hover:shadow-sm' }}">
                        <i class="fas fa-chart-line w-5 text-center text-base {{ request()->routeIs('admin.stocks.*') ? 'text-white' : 'text-slate-400 group-hover:text-blue-500' }}"></i>
                        <span>Monitoring Stok</span>
                    </a>

                    <a href="{{ route('admin.activity-logs.index') }}"
                        class="group flex items-center gap-3 rounded-xl px-3.5 py-3 text-sm font-medium transition-all duration-200
                        {{ request()->routeIs('admin.activity-logs.*')
                            ? 'bg-gradient-to-r from-blue-500 to-indigo-500 text-white shadow-lg shadow-blue-500/20'
                            : 'text-slate-600 hover:bg-white/80 hover:text-blue-600 hover:shadow-sm' }}">
                        <i class="fas fa-clock-rotate-left w-5 text-center text-base {{ request()->routeIs('admin.activity-logs.*') ? 'text-white' : 'text-slate-400 group-hover:text-blue-500' }}"></i>
                        <span>Log Aktivitas</span>
                    </a>

                </nav>
            </div>

        {{-- ================= PETUGAS GUDANG ================= --}}
        @elseif(auth()->user()->hasRole('Petugas Gudang'))

            {{-- Menu Utama --}}
            <div>
                <p class="mb-3 px-3 text-[10px] font-bold uppercase tracking-[0.18em] text-slate-400">
                    Menu Utama
                </p>

                <nav class="space-y-1.5">
                    <a href="{{ route('petugas.home') }}"
                        class="group flex items-center gap-3 rounded-xl px-3.5 py-3 text-sm font-medium transition-all duration-200
                        {{ request()->routeIs('petugas.home')
                            ? 'bg-gradient-to-r from-blue-500 to-indigo-500 text-white shadow-lg shadow-blue-500/20'
                            : 'text-slate-600 hover:bg-white/80 hover:text-blue-600 hover:shadow-sm' }}">
                        <i class="fas fa-house w-5 text-center text-base {{ request()->routeIs('petugas.home') ? 'text-white' : 'text-slate-400 group-hover:text-blue-500' }}"></i>
                        <span>Dashboard</span>
                    </a>
                </nav>
            </div>

            {{-- Data Master --}}
            <div>
                <p class="mb-3 px-3 text-[10px] font-bold uppercase tracking-[0.18em] text-slate-400">
                    Data Master
                </p>

                <nav class="space-y-1.5">

                    <a href="{{ route('petugas.categories.index') }}"
                        class="group flex items-center gap-3 rounded-xl px-3.5 py-3 text-sm font-medium transition-all duration-200
                        {{ request()->routeIs('petugas.categories.*') ? 'bg-gradient-to-r from-blue-500 to-indigo-500 text-white shadow-lg shadow-blue-500/20' : 'text-slate-600 hover:bg-white/80 hover:text-blue-600 hover:shadow-sm' }}">
                        <i class="fas fa-layer-group w-5 text-center text-base {{ request()->routeIs('petugas.categories.*') ? 'text-white' : 'text-slate-400 group-hover:text-blue-500' }}"></i>
                        <span>Kategori</span>
                    </a>

                    <a href="{{ route('petugas.suppliers.index') }}"
                        class="group flex items-center gap-3 rounded-xl px-3.5 py-3 text-sm font-medium transition-all duration-200
                        {{ request()->routeIs('petugas.suppliers.*') ? 'bg-gradient-to-r from-blue-500 to-indigo-500 text-white shadow-lg shadow-blue-500/20' : 'text-slate-600 hover:bg-white/80 hover:text-blue-600 hover:shadow-sm' }}">
                        <i class="fas fa-truck-field w-5 text-center text-base {{ request()->routeIs('petugas.suppliers.*') ? 'text-white' : 'text-slate-400 group-hover:text-blue-500' }}"></i>
                        <span>Supplier</span>
                    </a>

                    <a href="{{ route('petugas.customers.index') }}"
                        class="group flex items-center gap-3 rounded-xl px-3.5 py-3 text-sm font-medium transition-all duration-200
                        {{ request()->routeIs('petugas.customers.*') ? 'bg-gradient-to-r from-blue-500 to-indigo-500 text-white shadow-lg shadow-blue-500/20' : 'text-slate-600 hover:bg-white/80 hover:text-blue-600 hover:shadow-sm' }}">
                        <i class="fas fa-user-group w-5 text-center text-base {{ request()->routeIs('petugas.customers.*') ? 'text-white' : 'text-slate-400 group-hover:text-blue-500' }}"></i>
                        <span>Customer</span>
                    </a>

                    <a href="{{ route('petugas.units.index') }}"
                        class="group flex items-center gap-3 rounded-xl px-3.5 py-3 text-sm font-medium transition-all duration-200
                        {{ request()->routeIs('petugas.units.*') ? 'bg-gradient-to-r from-blue-500 to-indigo-500 text-white shadow-lg shadow-blue-500/20' : 'text-slate-600 hover:bg-white/80 hover:text-blue-600 hover:shadow-sm' }}">
                        <i class="fas fa-ruler-combined w-5 text-center text-base {{ request()->routeIs('petugas.units.*') ? 'text-white' : 'text-slate-400 group-hover:text-blue-500' }}"></i>
                        <span>Satuan</span>
                    </a>

                    <a href="{{ route('petugas.rack-locations.index') }}"
                        class="group flex items-center gap-3 rounded-xl px-3.5 py-3 text-sm font-medium transition-all duration-200
                        {{ request()->routeIs('petugas.rack-locations.*') ? 'bg-gradient-to-r from-blue-500 to-indigo-500 text-white shadow-lg shadow-blue-500/20' : 'text-slate-600 hover:bg-white/80 hover:text-blue-600 hover:shadow-sm' }}">
                        <i class="fas fa-warehouse w-5 text-center text-base {{ request()->routeIs('petugas.rack-locations.*') ? 'text-white' : 'text-slate-400 group-hover:text-blue-500' }}"></i>
                        <span>Lokasi Rak</span>
                    </a>

                    <a href="{{ route('petugas.products.index') }}"
                        class="group flex items-center gap-3 rounded-xl px-3.5 py-3 text-sm font-medium transition-all duration-200
                        {{ request()->routeIs('petugas.products.*') ? 'bg-gradient-to-r from-blue-500 to-indigo-500 text-white shadow-lg shadow-blue-500/20' : 'text-slate-600 hover:bg-white/80 hover:text-blue-600 hover:shadow-sm' }}">
                        <i class="fas fa-box w-5 text-center text-base {{ request()->routeIs('petugas.products.*') ? 'text-white' : 'text-slate-400 group-hover:text-blue-500' }}"></i>
                        <span>Produk</span>
                    </a>

                    <a href="{{ route('petugas.unit-conversions.index') }}"
                        class="group flex items-center gap-3 rounded-xl px-3.5 py-3 text-sm font-medium transition-all duration-200
                        {{ request()->routeIs('petugas.unit-conversions.*') ? 'bg-gradient-to-r from-blue-500 to-indigo-500 text-white shadow-lg shadow-blue-500/20' : 'text-slate-600 hover:bg-white/80 hover:text-blue-600 hover:shadow-sm' }}">
                        <i class="fas fa-arrows-rotate w-5 text-center text-base {{ request()->routeIs('petugas.unit-conversions.*') ? 'text-white' : 'text-slate-400 group-hover:text-blue-500' }}"></i>
                        <span>Konversi Satuan</span>
                    </a>

                    <a href="{{ route('petugas.stocks.index') }}"
                        class="group flex items-center gap-3 rounded-xl px-3.5 py-3 text-sm font-medium transition-all duration-200
                        {{ request()->routeIs('petugas.stocks.*') ? 'bg-gradient-to-r from-blue-500 to-indigo-500 text-white shadow-lg shadow-blue-500/20' : 'text-slate-600 hover:bg-white/80 hover:text-blue-600 hover:shadow-sm' }}">
                        <i class="fas fa-boxes-stacked w-5 text-center text-base {{ request()->routeIs('petugas.stocks.*') ? 'text-white' : 'text-slate-400 group-hover:text-blue-500' }}"></i>
                        <span>Stok Barang</span>
                    </a>

                </nav>
            </div>

            {{-- Transaksi --}}
            <div>
                <p class="mb-3 px-3 text-[10px] font-bold uppercase tracking-[0.18em] text-slate-400">
                    Transaksi
                </p>

                <nav class="space-y-1.5">

                    <a href="{{ route('petugas.inbounds.index') }}"
                        class="group flex items-center gap-3 rounded-xl px-3.5 py-3 text-sm font-medium transition-all duration-200
                        {{ request()->routeIs('petugas.inbounds.*') ? 'bg-gradient-to-r from-blue-500 to-indigo-500 text-white shadow-lg shadow-blue-500/20' : 'text-slate-600 hover:bg-white/80 hover:text-blue-600 hover:shadow-sm' }}">
                        <i class="fas fa-download w-5 text-center text-base {{ request()->routeIs('petugas.inbounds.*') ? 'text-white' : 'text-slate-400 group-hover:text-blue-500' }}"></i>
                        <span>Barang Masuk</span>
                    </a>

                    <a href="{{ route('petugas.outbounds.index') }}"
                        class="group flex items-center gap-3 rounded-xl px-3.5 py-3 text-sm font-medium transition-all duration-200
                        {{ request()->routeIs('petugas.outbounds.*') ? 'bg-gradient-to-r from-blue-500 to-indigo-500 text-white shadow-lg shadow-blue-500/20' : 'text-slate-600 hover:bg-white/80 hover:text-blue-600 hover:shadow-sm' }}">
                        <i class="fas fa-arrow-up-from-bracket w-5 text-center text-base {{ request()->routeIs('petugas.outbounds.*') ? 'text-white' : 'text-slate-400 group-hover:text-blue-500' }}"></i>
                        <span>Barang Keluar</span>
                    </a>

                    <a href="{{ route('petugas.transfers.index') }}"
                        class="group flex items-center gap-3 rounded-xl px-3.5 py-3 text-sm font-medium transition-all duration-200
                        {{ request()->routeIs('petugas.transfers.*') ? 'bg-gradient-to-r from-blue-500 to-indigo-500 text-white shadow-lg shadow-blue-500/20' : 'text-slate-600 hover:bg-white/80 hover:text-blue-600 hover:shadow-sm' }}">
                        <i class="fas fa-right-left w-5 text-center text-base {{ request()->routeIs('petugas.transfers.*') ? 'text-white' : 'text-slate-400 group-hover:text-blue-500' }}"></i>
                        <span>Transfer Lokasi Rak</span>
                    </a>

                    <a href="{{ route('petugas.stock-opnames.index') }}"
                        class="group flex items-center gap-3 rounded-xl px-3.5 py-3 text-sm font-medium transition-all duration-200
                        {{ request()->routeIs('petugas.stock-opnames.*') ? 'bg-gradient-to-r from-blue-500 to-indigo-500 text-white shadow-lg shadow-blue-500/20' : 'text-slate-600 hover:bg-white/80 hover:text-blue-600 hover:shadow-sm' }}">
                        <i class="fas fa-clipboard-list w-5 text-center text-base {{ request()->routeIs('petugas.stock-opnames.*') ? 'text-white' : 'text-slate-400 group-hover:text-blue-500' }}"></i>
                        <span>Stock Opname</span>
                    </a>

                </nav>
            </div>

        @endif
    </div>

    {{-- Footer Sidebar --}}
    <div class="relative flex-shrink-0 border-t border-slate-200/70 px-5 py-4">
        <div class="flex items-center gap-3 rounded-xl border border-white/80 bg-white/50 px-3 py-3 shadow-sm">
            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                <i class="fas fa-shield-halved"></i>
            </div>

            <div class="min-w-0">
                <p class="text-xs font-bold text-slate-700">SiStock Inventory</p>
                <p class="mt-0.5 text-[10px] text-slate-400">
                    © {{ date('Y') }} All rights reserved
                </p>
            </div>
        </div>
    </div>
</aside>