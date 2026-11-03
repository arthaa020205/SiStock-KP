<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'SiStock')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 text-gray-800">

    {{-- Sidebar sudah fixed --}}
    @include('components.sidebar')

    {{-- Area Utama --}}
    <div class="ml-64 min-h-screen flex flex-col">

        {{-- Header / Navbar --}}
        <header class="sticky top-0 z-40 bg-white border-b border-gray-200 px-6 py-4 shadow-sm">

            <div class="flex items-center justify-between">

                <div>
                    <h1 class="text-lg font-semibold">
                        @yield('page-title', 'Halaman Utama')
                    </h1>

                    <p class="text-sm text-gray-500">
                        Sistem Inventory Stock
                    </p>
                </div>

                <div class="flex items-center gap-4">

                    <div class="text-right">
                        <p class="text-sm font-semibold">
                            {{ auth()->user()->name }}
                        </p>

                        <p class="text-xs text-gray-500">
                            {{ auth()->user()->getRoleNames()->implode(', ') }}
                        </p>
                    </div>

                    <form action="{{ route('logout') }}" method="POST">
                        @csrf

                        <button type="submit"
                            class="bg-red-600 hover:bg-red-700 text-white text-sm px-4 py-2 rounded-lg">
                            Logout
                        </button>
                    </form>

                </div>

            </div>

        </header>

        {{-- Konten Halaman --}}
        <main class="flex-1 p-6">

            @yield('content')

        </main>

    </div>

</body>
</html>