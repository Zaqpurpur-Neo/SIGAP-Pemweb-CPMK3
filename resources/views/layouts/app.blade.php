{{-- resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'SIGAP') }} — @yield('title', 'Dashboard')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="font-sans antialiased bg-gray-100">
    <div class="flex h-screen overflow-hidden" x-data="{ sidebarOpen: false }">

        {{-- =================== SIDEBAR =================== --}}
        {{-- Desktop sidebar --}}
        <aside class="hidden lg:flex lg:flex-col lg:w-64 bg-slate-800 text-white fixed inset-y-0 left-0 z-30">
            @include('layouts.partials.sidebar-content')
        </aside>

        {{-- Mobile sidebar overlay --}}
        <div x-show="sidebarOpen" x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-40 bg-gray-900/60 lg:hidden"
             @click="sidebarOpen = false" style="display: none;">
        </div>

        {{-- Mobile sidebar --}}
        <aside x-show="sidebarOpen" x-transition:enter="transition ease-in-out duration-300 transform"
               x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
               x-transition:leave="transition ease-in-out duration-300 transform"
               x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full"
               class="fixed inset-y-0 left-0 z-50 w-64 bg-slate-800 text-white lg:hidden" style="display: none;">
            @include('layouts.partials.sidebar-content')
        </aside>

        {{-- =================== MAIN CONTENT =================== --}}
        <div class="flex-1 flex flex-col lg:ml-64 min-h-screen">

            {{-- TOPBAR --}}
            <header class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-20">
                <div class="flex items-center justify-between px-4 sm:px-6 lg:px-8 h-16">
                    <div class="flex items-center">
                        <button @click="sidebarOpen = true" class="lg:hidden p-2 rounded-md text-gray-600 hover:bg-gray-100">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                        </button>
                        <h1 class="text-lg font-semibold text-gray-800 ml-2 lg:ml-0">@yield('title', 'Dashboard')</h1>
                    </div>

                    <div class="flex items-center gap-3">
                        <span class="hidden sm:inline text-sm text-gray-700 font-medium">{{ auth()->user()->name }}</span>
                        <x-badge :type="auth()->user()->role === 'admin' ? 'danger' : 'info'"
                                 :text="ucfirst(auth()->user()->role)" />

                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-100 rounded-md">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            {{-- PAGE CONTENT --}}
            <main class="flex-1 p-4 sm:p-6 lg:p-8">
                {{ $slot ?? '' }}
                @hasSection('content')
                    @yield('content')
                @endif
            </main>
        </div>

        {{-- =================== FLASH TOAST =================== --}}
        @if (session('success'))
            <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)"
                 x-show="show" x-transition
                 class="fixed top-6 right-6 z-[60] bg-green-500 text-white px-5 py-3 rounded-lg shadow-lg">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)"
                 x-show="show" x-transition
                 class="fixed top-6 right-6 z-[60] bg-red-500 text-white px-5 py-3 rounded-lg shadow-lg">
                {{ session('error') }}
            </div>
        @endif

        @if (session('warning'))
            <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)"
                 x-show="show" x-transition
                 class="fixed top-6 right-6 z-[60] bg-yellow-500 text-white px-5 py-3 rounded-lg shadow-lg">
                {{ session('warning') }}
            </div>
        @endif
    </div>

    @livewireScripts
    @stack('scripts')
</body>
</html>
