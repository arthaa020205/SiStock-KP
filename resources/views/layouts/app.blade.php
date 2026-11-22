<!DOCTYPE html>
<html lang="id">

<style>
    /* Header glass */
    header {
        -webkit-backdrop-filter: blur(24px) saturate(180%);
        backdrop-filter: blur(24px) saturate(180%);
    }

    /* Tombol header */
    header button,
    header a {
        transition: all 250ms ease;
    }

    /* Hover tombol */
    header button:hover,
    header a:hover {
        transform: translateY(-1px);
    }

    /* Panel notifikasi */
    #notification-dropdown {
        background: rgba(255, 255, 255, 0.82) !important;
        backdrop-filter: blur(24px) saturate(180%);
        -webkit-backdrop-filter: blur(24px) saturate(180%);
        border: 1px solid rgba(255, 255, 255, 0.85);
        box-shadow: 0 15px 45px rgba(15, 23, 42, 0.12);
        border-radius: 18px;
    }
</style>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'SiStock')</title>

    {{-- Font Awesome --}}
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.7.2/css/all.min.css">

    {{-- Tailwind CSS dan JavaScript --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 text-gray-800">

    {{-- Sidebar --}}
    @include('components.sidebar')

    {{-- Area Utama --}}
    <div class="ml-64 min-h-screen flex flex-col">

        {{-- Header / Navbar --}}
        <header class="glass-header fixed top-4 left-[17rem] right-4 z-40 rounded-2xl px-6 py-4">

            <div class="flex items-center justify-between gap-3">

                {{-- Judul Halaman --}}
                <div class="min-w-0">
                    <h1 class="text-lg font-semibold truncate">
                        @yield('page-title', 'Halaman Utama')
                    </h1>

                    <p class="text-sm text-gray-500">
                        Sistem Inventory Stock
                    </p>
                </div>

                <div class="flex items-center gap-3 sm:gap-4">

                    {{-- Notifikasi Khusus Admin --}}
                    @hasrole('Admin')

                        @php
                            $currentUser = auth()->user();

                            $headerNotifications = $currentUser
                                ->notifications()
                                ->latest()
                                ->take(5)
                                ->get();

                            $unreadNotificationCount = $currentUser
                                ->unreadNotifications()
                                ->count();
                        @endphp

                        <div class="relative" id="notification-wrapper">

                            {{-- Tombol Lonceng --}}
                            <button type="button"
                                id="notification-button"
                                class="relative flex h-10 w-10 items-center justify-center rounded-xl
                                       border border-gray-200 text-gray-600 hover:bg-gray-100
                                       hover:text-blue-600 transition"
                                aria-label="Notifikasi"
                                aria-expanded="false">

                                <i class="fas fa-bell text-lg"></i>

                                @if ($unreadNotificationCount > 0)
                                    <span class="absolute -right-1 -top-1 flex h-5 min-w-5
                                                 items-center justify-center rounded-full
                                                 bg-red-600 px-1 text-[10px] font-bold text-white">
                                        {{ $unreadNotificationCount > 99 ? '99+' : $unreadNotificationCount }}
                                    </span>
                                @endif

                            </button>

                            {{-- Dropdown Notifikasi --}}
                            <div id="notification-dropdown"
                                class="absolute right-0 top-12 z-50 hidden
                                       w-[min(360px,calc(100vw-2rem))]
                                       overflow-hidden rounded-xl border border-gray-200
                                       bg-white shadow-xl">

                                {{-- Header Dropdown --}}
                                <div class="flex items-center justify-between border-b px-4 py-3">

                                    <div>
                                        <h3 class="font-semibold text-gray-800">
                                            Notifikasi
                                        </h3>

                                        <p class="text-xs text-gray-500">
                                            {{ $unreadNotificationCount }} belum dibaca
                                        </p>
                                    </div>

                                    @if ($unreadNotificationCount > 0)
                                        <form action="{{ route('admin.notifications.mark-all-read') }}"
                                            method="POST">
                                            @csrf

                                            <button type="submit"
                                                class="text-xs font-medium text-blue-600 hover:text-blue-800">
                                                Tandai semua dibaca
                                            </button>
                                        </form>
                                    @endif

                                </div>

                                {{-- Daftar Notifikasi --}}
                                <div class="max-h-96 overflow-y-auto">

                                    @forelse ($headerNotifications as $notification)

                                        @php
                                            $data = $notification->data;
                                            $isUnread = is_null($notification->read_at);

                                            $colorClasses = match ($data['color'] ?? 'blue') {
                                                'red' => 'bg-red-100 text-red-600',
                                                'yellow' => 'bg-yellow-100 text-yellow-600',
                                                'green' => 'bg-green-100 text-green-600',
                                                default => 'bg-blue-100 text-blue-600',
                                            };
                                        @endphp

                                        <form
                                            action="{{ route('admin.notifications.read', $notification->id) }}"
                                            method="POST">

                                            @csrf

                                            <button type="submit"
                                                class="flex w-full items-start gap-3 border-b px-4 py-3
                                                       text-left transition hover:bg-gray-50
                                                       {{ $isUnread ? 'bg-blue-50/60' : 'bg-white' }}">

                                                {{-- Ikon --}}
                                                <span class="flex h-9 w-9 shrink-0 items-center
                                                             justify-center rounded-full {{ $colorClasses }}">
                                                    <i class="fas {{ $data['icon'] ?? 'fa-bell' }}"></i>
                                                </span>

                                                {{-- Informasi --}}
                                                <span class="min-w-0 flex-1">

                                                    <span class="flex items-center justify-between gap-2">

                                                        <span class="text-sm font-semibold text-gray-800">
                                                            {{ $data['title'] ?? 'Notifikasi' }}
                                                        </span>

                                                        @if ($isUnread)
                                                            <span class="h-2 w-2 shrink-0 rounded-full bg-blue-600"></span>
                                                        @endif

                                                    </span>

                                                    <span class="mt-1 block text-xs leading-relaxed text-gray-600">
                                                        {{ $data['message'] ?? '-' }}
                                                    </span>

                                                    <span class="mt-1 block text-[11px] text-gray-400">
                                                        {{ $notification->created_at->diffForHumans() }}
                                                    </span>

                                                </span>

                                            </button>

                                        </form>

                                    @empty

                                        <div class="px-4 py-10 text-center">

                                            <i class="fas fa-bell-slash text-3xl text-gray-300"></i>

                                            <p class="mt-3 text-sm font-medium text-gray-600">
                                                Belum ada notifikasi
                                            </p>

                                            <p class="mt-1 text-xs text-gray-400">
                                                Notifikasi terbaru akan muncul di sini.
                                            </p>

                                        </div>

                                    @endforelse

                                </div>

                                {{-- Lihat Semua --}}
                                <a href="{{ route('admin.notifications.index') }}"
                                    class="block border-t bg-gray-50 px-4 py-3 text-center
                                           text-sm font-semibold text-blue-600 hover:bg-gray-100">

                                    Lihat Semua Notifikasi

                                    <i class="fas fa-arrow-right ml-1 text-xs"></i>

                                </a>

                            </div>

                        </div>

                    @endhasrole

                    {{-- Informasi Pengguna --}}
                    <div class="text-right">
                        <p class="text-sm font-semibold">
                            {{ auth()->user()->name }}
                        </p>

                        <p class="text-xs text-gray-500">
                            {{ auth()->user()->getRoleNames()->implode(', ') }}
                        </p>
                    </div>

                    {{-- Logout --}}
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf

                        <button type="submit"
                            class="rounded-lg bg-red-600 px-4 py-2 text-sm text-white
                                   hover:bg-red-700 transition">
                            Logout
                        </button>
                    </form>

                </div>

            </div>

        </header>

        {{-- Konten Halaman --}}
        <main class="flex-1 min-w-0 px-4 pb-6 pt-28 sm:px-6">

            @yield('content')

        </main>

    </div>

    {{-- Script Tambahan dari Halaman --}}
    @stack('scripts')

    {{-- Script Dropdown Notifikasi --}}
    @hasrole('Admin')
        <script>
            document.addEventListener('DOMContentLoaded', function () {

                const button = document.getElementById('notification-button');
                const dropdown = document.getElementById('notification-dropdown');
                const wrapper = document.getElementById('notification-wrapper');

                if (!button || !dropdown || !wrapper) {
                    return;
                }

                // Buka dan tutup dropdown.
                button.addEventListener('click', function (event) {
                    event.stopPropagation();

                    const isHidden = dropdown.classList.contains('hidden');

                    dropdown.classList.toggle('hidden');

                    button.setAttribute(
                        'aria-expanded',
                        isHidden ? 'true' : 'false'
                    );
                });

                // Tutup dropdown ketika klik di luar.
                document.addEventListener('click', function (event) {

                    if (!wrapper.contains(event.target)) {
                        dropdown.classList.add('hidden');

                        button.setAttribute('aria-expanded', 'false');
                    }

                });

                // Tutup dropdown dengan tombol Escape.
                document.addEventListener('keydown', function (event) {

                    if (event.key === 'Escape') {
                        dropdown.classList.add('hidden');

                        button.setAttribute('aria-expanded', 'false');
                    }

                });

            });
        </script>
    @endhasrole

</body>

</html>