<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ isset($title) ? $title . ' — ' : '' }}{{ config('app.name', 'Dashboard PMB') }}</title>
        <link rel="icon" type="image/jpeg" href="{{ asset('images/logo.jpg') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Chart.js: local copy to avoid CDN latency on first page load -->
        <script src="{{ asset('js/chart.umd.min.js') }}"></script>

        <!-- Inline utility scripts (registered via @push('head-scripts')) -->
        @stack('head-scripts')
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            <livewire:layout.navigation />

            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-white shadow">
                    <div class="max-w-6xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main class="pb-16">
                {{ $slot }}
            </main>

            <!-- Footer -->
            <footer class="bg-white border-t border-gray-200 py-4">
                <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-gray-400">
                    <span>
                        &copy; {{ date('Y') }} <strong class="text-gray-500">PUSTIPD</strong> — Pusat Teknologi Informasi dan Pangkalan Data
                    </span>
                    <span class="text-gray-300">Sistem Informasi PMB · {{ config('app.name', 'InformasiData') }}</span>
                </div>
            </footer>
        </div>

        {{-- Slot untuk chart scripts (Chart.js, dll) --}}
        @stack('scripts')
    </body>
</html>
