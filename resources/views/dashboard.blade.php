
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - SiStock</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100">

    <div class="max-w-5xl mx-auto p-8">

        <div class="bg-white rounded-xl shadow-sm p-8">
            <h1 class="text-2xl font-bold text-slate-800">
                Selamat Datang di SiStock
            </h1>

            <p class="text-slate-600 mt-3">
                Login berhasil.
            </p>

            <div class="mt-5">
                <p class="text-slate-700">
                    Nama: {{ auth()->user()->name }}
                </p>

                <p class="text-slate-700">
                    Email: {{ auth()->user()->email }}
                </p>

                <p class="text-slate-700">
                    Role: {{ auth()->user()->getRoleNames()->implode(', ') }}
                </p>
            </div>

            <form action="{{ route('logout') }}" method="POST" class="mt-6">
                @csrf

                <button
                    type="submit"
                    class="bg-red-600 hover:bg-red-700 text-white px-5 py-2 rounded-lg"
                >
                    Logout
                </button>
            </form>
        </div>

    </div>

</body>
</html>
