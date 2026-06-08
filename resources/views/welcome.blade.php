<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SIGAP - Sistem Inventaris & Aset Barang</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-gray-100 min-h-screen flex flex-col items-center justify-center p-4">

    <div class="w-full max-w-md text-center">
        <!-- Logo / Title Area -->
        <div class="mb-8">
            <h1 class="text-4xl font-bold text-slate-800 tracking-tight">SIGAP</h1>
            <p class="text-slate-500 mt-2 text-lg font-medium">Sistem Inventaris & Aset Barang</p>
        </div>

        <!-- Access Card -->
        <div class="bg-white p-8 rounded-xl shadow-sm border border-gray-200">
            <p class="text-gray-600 mb-6 text-sm leading-relaxed">
                Silakan masuk untuk mengakses sistem manajemen inventaris laboratorium.
            </p>

            @if (Route::has('login'))
                <div class="flex flex-col gap-3">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="w-full inline-flex justify-center items-center px-4 py-3 bg-blue-600 border border-transparent rounded-lg font-semibold text-sm text-white uppercase tracking-wider hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Buka Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="w-full inline-flex justify-center items-center px-4 py-3 bg-blue-600 border border-transparent rounded-lg font-semibold text-sm text-white uppercase tracking-wider hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Masuk ke Sistem
                        </a>
                    @endauth
                </div>
            @endif
        </div>

        <!-- Footer -->
        <p class="text-xs text-gray-400 mt-8">
            &copy; {{ date('Y') }} SIGAP. Internal Use Only.
        </p>
    </div>

</body>
</html>
