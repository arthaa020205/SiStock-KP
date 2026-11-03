<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SiStock - Pengujian</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 flex items-center justify-center p-6">

    <main class="w-full max-w-lg rounded-2xl bg-white p-8 shadow-lg">
        <div class="mb-6 flex h-14 w-14 items-center justify-center rounded-xl bg-blue-600 text-2xl font-bold text-white">
            S
        </div>

        <p class="mb-2 text-sm font-semibold uppercase tracking-wider text-blue-600">
            Sistem Inventory Stock
        </p>

        <h1 class="mb-3 text-3xl font-bold text-slate-800">
            SiStock
        </h1>

        <p class="mb-6 leading-relaxed text-slate-600">
            Fondasi sistem inventory sedang dipersiapkan.
            Pengelolaan barang, transaksi stok, dan monitoring
            akan dikembangkan secara bertahap.
        </p>

        <div class="rounded-lg border border-green-200 bg-green-50 p-4">
            <p class="font-semibold text-green-700">
                ✓ Pengujian tampilan
            </p>
            <p class="mt-1 text-sm text-green-700">
                Jika halaman ini tampil dengan warna, jarak,
                dan ukuran teks yang sesuai, Tailwind CSS
                berhasil diterapkan.
            </p>
        </div>

        <p class="mt-6 text-center text-xs text-slate-400">
            SiStock &copy; {{ date('Y') }}
        </p>
    </main>

</body>
</html>