<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component
{
    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/');
    }
}; ?>

<nav x-data="{ open: false }" class="bg-white border-b border-gray-100 sticky top-0 z-50 shadow-sm">
    <!-- Primary Navigation Menu -->
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden md:-my-px md:ms-6 md:flex md:space-x-4 lg:space-x-6">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>

                    @can('view_master_data')
                    <div class="relative flex items-center" x-data="{ openMaster: false }" @click.outside="openMaster = false">
                        <button @click="openMaster = !openMaster"
                            class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out focus:outline-none
                            {{ request()->is('master/*') ? 'border-indigo-400 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                            Master Data
                            <svg class="ml-1 w-4 h-4 transition-transform" :class="{'rotate-180': openMaster}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="openMaster" x-transition
                            class="absolute top-full left-0 mt-1 w-52 bg-white rounded-xl shadow-lg border border-gray-200 z-50 py-1">
                            <a href="{{ route('master.fakultas') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">📚 Fakultas</a>
                            <a href="{{ route('master.prodi') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">🎓 Program Studi</a>
                            <a href="{{ route('master.jalur') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">🛤️ Jalur Seleksi</a>
                            <a href="{{ route('master.wilayah') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">🗺️ Master Wilayah</a>
                            <a href="{{ route('master.negara') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">🌍 Master Negara</a>
                            <a href="{{ route('master.lembaga') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">🏛️ Master Lembaga</a>
                            <a href="{{ route('master.sekolah') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">🏫 Master Sekolah</a>
                            <div class="border-t border-gray-100 my-1"></div>
                            <a href="{{ route('master.tarif-ukt') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">💵 Tarif Biaya Kuliah</a>
                            <a href="{{ route('master.beasiswa') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">🎓 Master Beasiswa</a>
                            <a href="{{ route('master.status-mahasiswa') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">📋 Status Mahasiswa</a>
                            <div class="border-t border-gray-100 my-1"></div>
                            <a href="{{ route('master.antrian') }}" class="block px-4 py-2 text-sm text-yellow-700 hover:bg-yellow-50 font-medium">⏳ Antrian Review</a>
                        </div>
                    </div>
                    @endcan


                    {{-- Data Individu: semua user yg login --}}
                    <div class="flex items-center">
                        <a href="{{ route('data-individu') }}"
                            class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out
                            {{ request()->is('data-individu*') ? 'border-indigo-400 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                            👥 Data Individu
                        </a>
                    </div>

                    @can('view_laporan')
                    <div class="flex items-center">
                        <a href="{{ route('laporan.pmb') }}"
                            class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out
                            {{ request()->is('laporan*') ? 'border-indigo-400 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                            📊 Laporan PMB
                        </a>
                    </div>
                    @endcan

                    @can('view_manajemen_pmb')
                    <div class="relative flex items-center" x-data="{ openPmb: false }" @click.outside="openPmb = false">
                        <button @click="openPmb = !openPmb"
                            class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out focus:outline-none
                            {{ request()->is('manajemen-pmb/*') || request()->is('entri-data/*') || request()->is('import/*') ? 'border-indigo-400 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                            🎯 Manajemen PMB
                            <svg class="ml-1 w-4 h-4 transition-transform" :class="{'rotate-180': openPmb}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="openPmb" x-transition class="absolute top-full left-0 mt-1 w-60 bg-white rounded-xl shadow-lg border border-gray-200 z-50 py-1">
                            {{-- Grup: Entri Data Kelulusan --}}
                            <p class="px-4 pt-2 pb-1 text-xs font-semibold text-gray-400 uppercase tracking-wide">Entri Data</p>
                            <a href="{{ route('manajemen-pmb.entri-data') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">📋 Entri Data Kelulusan</a>

                            {{-- Grup: Registrasi --}}
                            <div class="border-t border-gray-100 my-1"></div>
                            <p class="px-4 pt-2 pb-1 text-xs font-semibold text-gray-400 uppercase tracking-wide">Registrasi</p>
                            <a href="{{ route('manajemen-pmb.penetapan-registrasi') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">✅ Penetapan Registrasi</a>

                            @can('view_import')
                            <div class="border-t border-gray-100 my-1"></div>
                            {{-- Grup: Entri Data Agregat --}}
                            <p class="px-4 pt-2 pb-1 text-xs font-semibold text-gray-400 uppercase tracking-wide">Entri Data Agregat</p>
                            <a href="{{ route('entri-data.agregat') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">📊 Entri Agregat (Total)</a>
                            <a href="{{ route('import.riwayat') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">📋 Riwayat Import</a>
                            @endcan
                        </div>
                    </div>
                    @endcan

                    <div class="flex items-center">
                        <a href="{{ route('akademik.mahasiswa') }}"
                            class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out
                            {{ request()->is('akademik*') ? 'border-indigo-400 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                            📈 Dashboard Akademik
                        </a>
                    </div>

                    @can('manage_roles_permissions')
                    <div class="relative flex items-center" x-data="{ openPengaturan: false }" @click.outside="openPengaturan = false">
                        <button @click="openPengaturan = !openPengaturan"
                            class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out focus:outline-none
                            {{ request()->is('pengaturan/*') ? 'border-indigo-400 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                            ⚙️ Pengaturan
                            <svg class="ml-1 w-4 h-4 transition-transform" :class="{'rotate-180': openPengaturan}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="openPengaturan" x-transition class="absolute top-full left-0 mt-1 w-56 bg-white rounded-xl shadow-lg border border-gray-200 z-50 py-1">
                            <a href="{{ route('pengaturan.users') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">👤 Manajemen Pengguna</a>
                            <a href="{{ route('pengaturan.roles') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">🛡️ Manajemen Role (Akses)</a>
                            <a href="{{ route('pengaturan.permissions') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">🔑 Master Permissions</a>
                        </div>
                    </div>
                    @endcan
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden md:flex md:items-center md:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name" x-on:profile-updated.window="name = $event.detail.name"></div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <button wire:click="logout" class="w-full text-start">
                            <x-dropdown-link>
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </button>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center md:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden md:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>

            @can('view_master_data')
            <div x-data="{ openMasterResp: false }" class="space-y-1">
                <button @click="openMasterResp = !openMasterResp" class="w-full flex justify-between items-center px-4 py-2 text-left text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 focus:outline-none focus:text-gray-800 focus:bg-gray-50 transition duration-150 ease-in-out">
                    Master Data
                    <svg class="h-4 w-4 transform transition-transform" :class="{'rotate-180': openMasterResp}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="openMasterResp" class="pl-4 space-y-1 bg-gray-50 pb-2 border-l-4 border-transparent">
                    <x-responsive-nav-link :href="route('master.fakultas')">📚 Fakultas</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('master.prodi')">🎓 Program Studi</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('master.jalur')">🛤️ Jalur Seleksi</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('master.wilayah')">🗺️ Master Wilayah</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('master.negara')">🌍 Master Negara</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('master.lembaga')">🏛️ Master Lembaga</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('master.sekolah')">🏫 Master Sekolah</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('master.tarif-ukt')">💵 Tarif Biaya Kuliah</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('master.beasiswa')">🎓 Master Beasiswa</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('master.status-mahasiswa')">📋 Status Mahasiswa</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('master.antrian')" class="text-yellow-700">⏳ Antrian Review</x-responsive-nav-link>
                </div>
            </div>
            @endcan


            <x-responsive-nav-link :href="route('data-individu')" :active="request()->routeIs('data-individu*')">
                👥 Data Individu
            </x-responsive-nav-link>

            @can('view_laporan')
            <x-responsive-nav-link :href="route('laporan.pmb')" :active="request()->routeIs('laporan*')">
                📊 Laporan PMB
            </x-responsive-nav-link>
            @endcan

            @can('view_manajemen_pmb')
            <div x-data="{ openPmbResp: false }" class="space-y-1">
                <button @click="openPmbResp = !openPmbResp" class="w-full flex justify-between items-center px-4 py-2 text-left text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 focus:outline-none focus:text-gray-800 focus:bg-gray-50 transition duration-150 ease-in-out">
                    🎯 Manajemen PMB
                    <svg class="h-4 w-4 transform transition-transform" :class="{'rotate-180': openPmbResp}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="openPmbResp" class="pl-4 space-y-1 bg-gray-50 pb-2 border-l-4 border-transparent">
                    <p class="px-3 pt-2 pb-0.5 text-xs font-semibold text-gray-400 uppercase tracking-wide">Kelulusan & Registrasi</p>
                    <x-responsive-nav-link :href="route('manajemen-pmb.import-cama')">📥 Import Data Kelulusan</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('manajemen-pmb.penetapan-registrasi')">✅ Penetapan Registrasi</x-responsive-nav-link>
                    @can('view_import')
                    <p class="px-3 pt-3 pb-0.5 text-xs font-semibold text-gray-400 uppercase tracking-wide border-t border-gray-200 mt-1">Entri Data</p>
                    <x-responsive-nav-link :href="route('entri-data.agregat')">📊 Entri Agregat (Total)</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('entri-data.manual')">👤 Entri Individu PMB</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('import.riwayat')">📋 Riwayat Import</x-responsive-nav-link>
                    @endcan
                </div>
            </div>
            @endcan

            <x-responsive-nav-link :href="route('akademik.mahasiswa')" :active="request()->routeIs('akademik*')">
                📈 Dashboard Akademik
            </x-responsive-nav-link>

            @can('manage_roles_permissions')
            <div x-data="{ openPengaturanResp: false }" class="space-y-1">
                <button @click="openPengaturanResp = !openPengaturanResp" class="w-full flex justify-between items-center px-4 py-2 text-left text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 focus:outline-none focus:text-gray-800 focus:bg-gray-50 transition duration-150 ease-in-out">
                    ⚙️ Pengaturan
                    <svg class="h-4 w-4 transform transition-transform" :class="{'rotate-180': openPengaturanResp}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="openPengaturanResp" class="pl-4 space-y-1 bg-gray-50 pb-2 border-l-4 border-transparent">
                    <x-responsive-nav-link :href="route('pengaturan.users')">👤 Manajemen Pengguna</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('pengaturan.roles')">🛡️ Manajemen Role (Akses)</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('pengaturan.permissions')">🔑 Master Permissions</x-responsive-nav-link>
                </div>
            </div>
            @endcan
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800" x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name" x-on:profile-updated.window="name = $event.detail.name"></div>
                <div class="font-medium text-sm text-gray-500">{{ auth()->user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <button wire:click="logout" class="w-full text-start">
                    <x-responsive-nav-link>
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </button>
            </div>
        </div>
    </div>
</nav>

