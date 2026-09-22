<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Sistem Informasi Data - UIN Raden Fatah</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Tailwind CSS -->
        <script src="https://cdn.tailwindcss.com"></script>
        
        <script>
            tailwind.config = {
                theme: {
                    extend: {
                        fontFamily: {
                            sans: ['Inter', 'sans-serif'],
                        },
                        colors: {
                            brand: {
                                50: '#f0f9ff',
                                100: '#e0f2fe',
                                500: '#0ea5e9',
                                600: '#0284c7',
                                700: '#0369a1',
                                900: '#0c4a6e',
                            }
                        }
                    }
                }
            }
        </script>
        
        <style>
            .bg-pattern {
                background-color: #f8fafc;
                background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23cbd5e1' fill-opacity='0.2'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            }
        </style>
    </head>
    <body class="antialiased bg-pattern text-slate-800 selection:bg-brand-500 selection:text-white flex flex-col min-h-screen">
        
        <!-- Navbar -->
        <nav class="bg-white/80 backdrop-blur-md border-b border-slate-200 sticky top-0 z-50">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16 items-center">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-brand-600 rounded-xl flex items-center justify-center text-white font-bold text-xl shadow-lg shadow-brand-500/30">
                            ID
                        </div>
                        <span class="font-bold text-xl tracking-tight text-slate-900">InformasiData</span>
                    </div>
                    <div>
                        @if (Route::has('login'))
                            <div class="flex items-center gap-4">
                                @auth
                                    <a href="{{ url('/dashboard') }}" class="font-semibold text-slate-600 hover:text-brand-600 transition-colors">Dashboard</a>
                                @else
                                    <a href="{{ route('login') }}" class="font-semibold text-slate-600 hover:text-brand-600 transition-colors">Log in</a>

                                    @if (Route::has('register'))
                                        <a href="{{ route('register') }}" class="font-semibold text-white bg-brand-600 hover:bg-brand-700 px-5 py-2 rounded-lg shadow-md transition-all hover:shadow-lg hover:-translate-y-0.5">Register</a>
                                    @endif
                                @endauth
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <main class="flex-grow flex items-center justify-center relative overflow-hidden">
            <!-- Decorative blobs -->
            <div class="absolute top-0 left-1/4 w-96 h-96 bg-brand-200 rounded-full mix-blend-multiply filter blur-3xl opacity-50 animate-blob"></div>
            <div class="absolute top-0 right-1/4 w-96 h-96 bg-indigo-200 rounded-full mix-blend-multiply filter blur-3xl opacity-50 animate-blob animation-delay-2000"></div>
            <div class="absolute -bottom-32 left-1/2 w-96 h-96 bg-pink-200 rounded-full mix-blend-multiply filter blur-3xl opacity-50 animate-blob animation-delay-4000"></div>

            <div class="relative z-10 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-20 text-center">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-brand-50 border border-brand-100 text-brand-700 text-sm font-medium mb-8">
                    <span class="flex w-2 h-2 rounded-full bg-brand-500 animate-pulse"></span>
                    Sistem Penerimaan Mahasiswa Baru
                </div>
                
                <h1 class="text-5xl md:text-7xl font-extrabold tracking-tight text-slate-900 mb-6 drop-shadow-sm">
                    Pusat Informasi <span class="text-transparent bg-clip-text bg-gradient-to-r from-brand-600 to-indigo-600">Data UIN RF</span>
                </h1>
                
                <p class="mt-4 text-lg md:text-xl text-slate-600 max-w-3xl mx-auto mb-10 leading-relaxed">
                    Sistem Informasi terintegrasi untuk pengelolaan, visualisasi, dan pelaporan data Penerimaan Mahasiswa Baru (PMB) secara *real-time* dan akurat.
                </p>

                <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="w-full sm:w-auto text-white bg-brand-600 hover:bg-brand-700 px-8 py-4 rounded-xl font-bold text-lg shadow-lg shadow-brand-500/30 transition-all hover:-translate-y-1 hover:shadow-xl flex items-center justify-center gap-2">
                            Buka Dashboard
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="w-full sm:w-auto text-white bg-slate-900 hover:bg-slate-800 px-8 py-4 rounded-xl font-bold text-lg shadow-lg shadow-slate-900/20 transition-all hover:-translate-y-1 hover:shadow-xl flex items-center justify-center gap-2">
                            Masuk ke Sistem
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                        </a>
                    @endauth
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-white border-t border-slate-200 py-8 relative z-10">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="flex items-center gap-2 text-slate-500 font-medium">
                    <svg class="w-5 h-5 text-brand-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/></svg>
                    UIN Raden Fatah Palembang
                </div>
                <div class="text-slate-400 text-sm">
                    &copy; {{ date('Y') }} Pusat Teknologi Informasi dan Pangkalan Data.
                </div>
            </div>
        </footer>

        <style>
            @keyframes blob {
                0% { transform: translate(0px, 0px) scale(1); }
                33% { transform: translate(30px, -50px) scale(1.1); }
                66% { transform: translate(-20px, 20px) scale(0.9); }
                100% { transform: translate(0px, 0px) scale(1); }
            }
            .animate-blob {
                animation: blob 7s infinite;
            }
            .animation-delay-2000 {
                animation-delay: 2s;
            }
            .animation-delay-4000 {
                animation-delay: 4s;
            }
        </style>
    </body>
</html>
