<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="sm:flex sm:items-center sm:justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Master Sekolah</h1>
            <p class="mt-2 text-sm text-gray-600">Daftar sekolah seluruh Indonesia beserta status dan kelompoknya.</p>
        </div>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        @if (session()->has('message'))
            <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-4 mx-4 mt-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm leading-5 font-medium text-green-800">
                            {{ session('message') }}
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <div class="p-4 border-b border-gray-200 bg-gray-50/50">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Baris 1: Pencarian dan Wilayah -->
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari nama sekolah, NPSN..." class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition duration-150 ease-in-out">
                </div>

                <select wire:model.live="filterProvinsi" class="block w-full pl-3 pr-10 py-2 text-base border border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md bg-white">
                    <option value="">Semua Provinsi</option>
                    @foreach($provinsiList as $prov)
                        <option value="{{ $prov->kode_prop }}">{{ $prov->nama_prop }}</option>
                    @endforeach
                </select>

                <select wire:model.live="filterKabupaten" class="block w-full pl-3 pr-10 py-2 text-base border border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md bg-white {{ !$filterProvinsi ? 'opacity-50 cursor-not-allowed' : '' }}" {{ !$filterProvinsi ? 'disabled' : '' }}>
                    <option value="">Semua Kabupaten/Kota</option>
                    @foreach($kabupatenList as $kab)
                        <option value="{{ $kab->kode_kab }}">{{ $kab->nama_kab }}</option>
                    @endforeach
                </select>

                <select wire:model.live="filterKecamatan" class="block w-full pl-3 pr-10 py-2 text-base border border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md bg-white {{ !$filterKabupaten ? 'opacity-50 cursor-not-allowed' : '' }}" {{ !$filterKabupaten ? 'disabled' : '' }}>
                    <option value="">Semua Kecamatan</option>
                    @foreach($kecamatanList as $kec)
                        <option value="{{ $kec->kode_kec }}">{{ $kec->nama_kec }}</option>
                    @endforeach
                </select>

                <!-- Baris 2: Filter Status, Kelompok, dan Tombol Tambah -->
                <select wire:model.live="filterStatus" class="block w-full pl-3 pr-10 py-2 text-base border border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md bg-white">
                    <option value="">Semua Status</option>
                    @foreach($statusList as $status)
                        @if($status) <option value="{{ $status }}">{{ $status }}</option> @endif
                    @endforeach
                </select>

                <select wire:model.live="filterKelompok" class="block w-full pl-3 pr-10 py-2 text-base border border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md bg-white">
                    <option value="">Semua Kelompok</option>
                    @foreach($kelompokList as $kel)
                        @if($kel) <option value="{{ $kel }}">{{ $kel }}</option> @endif
                    @endforeach
                </select>
                
                <div class="hidden lg:block"></div>
                
                <div class="flex justify-end lg:justify-end md:col-span-2 lg:col-span-1">
                    <button wire:click="openAddSekolahModal" class="w-full lg:w-auto inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm leading-5 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-500 focus:outline-none focus:border-blue-700 focus:shadow-outline-blue active:bg-blue-700 transition ease-in-out duration-150">
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        Tambah Sekolah
                    </button>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-16">No</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">NPSN</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Sekolah</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Wilayah</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider text-center">Status / Kel.</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($sekolahList as $index => $sekolah)
                    <tr wire:click="openDetailModal('{{ $sekolah->npsn }}')" class="hover:bg-blue-50/50 transition duration-150 cursor-pointer">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $sekolahList->firstItem() + $index }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-mono">
                            {{ $sekolah->npsn }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">{{ $sekolah->nama_sekolah }}</div>
                            <div class="text-xs text-gray-500 mt-1 line-clamp-2" title="{{ $sekolah->alamat_sekolah }}">{{ $sekolah->alamat_sekolah }}</div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            @if($sekolah->kecamatan)
                                <div>Kec. {{ $sekolah->kecamatan->nama_kec }}</div>
                                @if($sekolah->kecamatan->kabupaten)
                                    <div class="text-xs text-gray-400">{{ $sekolah->kecamatan->kabupaten->nama_kab }}, {{ $sekolah->kecamatan->kabupaten->propinsi->nama_prop ?? '' }}</div>
                                @endif
                            @else
                                <span class="italic text-gray-400">Tidak ada info wilayah</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ strtolower($sekolah->status) == 'negeri' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $sekolah->status ?: '-' }}
                            </span>
                            <br>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-50 text-blue-700 mt-1">
                                {{ $sekolah->kelompok ?: '-' }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-10 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada data sekolah</h3>
                            <p class="mt-1 text-sm text-gray-500">Silakan gunakan kata kunci pencarian yang lain.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($sekolahList->hasPages())
        <div class="bg-gray-50 px-4 py-3 border-t border-gray-200 sm:px-6 rounded-b-xl">
            {{ $sekolahList->links() }}
        </div>
        @endif
    </div>

    <!-- Modal Detail Sekolah -->
    @if($isDetailModalOpen && $selectedSekolah)
    <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto overflow-x-hidden bg-gray-900 bg-opacity-50 backdrop-blur-sm transition-opacity">
        <div class="relative p-4 w-full max-w-4xl h-full md:h-auto">
            <!-- Modal content -->
            <div class="relative bg-white rounded-2xl shadow-xl">
                <!-- Modal header -->
                <div class="flex justify-between items-center p-5 rounded-t-2xl border-b border-gray-200 bg-gradient-to-r from-blue-50 to-white">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">
                            {{ $selectedSekolah->nama_sekolah }}
                        </h3>
                        <p class="text-sm text-gray-500 mt-1 flex items-center gap-2">
                            <span class="font-mono bg-gray-100 px-2 py-0.5 rounded text-gray-700">NPSN: {{ $selectedSekolah->npsn }}</span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ strtolower($selectedSekolah->status) == 'negeri' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $selectedSekolah->status ?: '-' }}
                            </span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                Akreditasi: {{ $selectedSekolah->akreditasi ?: '-' }}
                            </span>
                        </p>
                    </div>
                    <button wire:click="closeDetailModal" type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-2 ml-auto inline-flex items-center transition-colors">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>  
                    </button>
                </div>
                <!-- Modal body -->
                <div class="p-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Kolom Kiri -->
                        <div class="space-y-4">
                            <div>
                                <h4 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-2">Informasi Umum</h4>
                                <div class="bg-gray-50 rounded-xl p-4 space-y-3">
                                    <div class="grid grid-cols-3 gap-2">
                                        <div class="text-sm text-gray-500">Kelompok</div>
                                        <div class="text-sm font-medium text-gray-900 col-span-2">{{ $selectedSekolah->kelompok ?: '-' }}</div>
                                    </div>
                                    <div class="grid grid-cols-3 gap-2">
                                        <div class="text-sm text-gray-500">Kurikulum</div>
                                        <div class="text-sm font-medium text-gray-900 col-span-2">{{ $selectedSekolah->kurikulum ?: '-' }}</div>
                                    </div>
                                    <div class="grid grid-cols-3 gap-2">
                                        <div class="text-sm text-gray-500">Jml Siswa</div>
                                        <div class="text-sm font-medium text-gray-900 col-span-2">{{ $selectedSekolah->jumlah_siswa ? number_format($selectedSekolah->jumlah_siswa, 0, ',', '.') : '-' }}</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div>
                                <h4 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-2">Lokasi & Alamat</h4>
                                <div class="bg-gray-50 rounded-xl p-4 space-y-3">
                                    <div class="grid grid-cols-3 gap-2">
                                        <div class="text-sm text-gray-500">Alamat Lengkap</div>
                                        <div class="text-sm font-medium text-gray-900 col-span-2">{{ $selectedSekolah->alamat_sekolah ?: '-' }}</div>
                                    </div>
                                    <div class="grid grid-cols-3 gap-2">
                                        <div class="text-sm text-gray-500">Kecamatan</div>
                                        <div class="text-sm font-medium text-gray-900 col-span-2">{{ $selectedSekolah->kecamatan->nama_kec ?? '-' }}</div>
                                    </div>
                                    <div class="grid grid-cols-3 gap-2">
                                        <div class="text-sm text-gray-500">Kab/Kota</div>
                                        <div class="text-sm font-medium text-gray-900 col-span-2">{{ $selectedSekolah->kecamatan->kabupaten->nama_kab ?? '-' }}</div>
                                    </div>
                                    <div class="grid grid-cols-3 gap-2">
                                        <div class="text-sm text-gray-500">Provinsi</div>
                                        <div class="text-sm font-medium text-gray-900 col-span-2">{{ $selectedSekolah->kecamatan->kabupaten->propinsi->nama_prop ?? '-' }}</div>
                                    </div>
                                    <div class="grid grid-cols-3 gap-2">
                                        <div class="text-sm text-gray-500">Kode Pos</div>
                                        <div class="text-sm font-medium text-gray-900 col-span-2">{{ $selectedSekolah->kode_pos ?: '-' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Kolom Kanan -->
                        <div class="space-y-4">
                            <div>
                                <h4 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-2">Kontak Sekolah</h4>
                                <div class="bg-gray-50 rounded-xl p-4 space-y-3">
                                    <div class="grid grid-cols-3 gap-2 items-center">
                                        <div class="text-sm text-gray-500 flex items-center gap-1"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg> Telepon</div>
                                        <div class="text-sm font-medium text-gray-900 col-span-2">{{ $selectedSekolah->telepon ?: '-' }}</div>
                                    </div>
                                    <div class="grid grid-cols-3 gap-2 items-center">
                                        <div class="text-sm text-gray-500 flex items-center gap-1"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg> Fax</div>
                                        <div class="text-sm font-medium text-gray-900 col-span-2">{{ $selectedSekolah->fax ?: '-' }}</div>
                                    </div>
                                    <div class="grid grid-cols-3 gap-2 items-center">
                                        <div class="text-sm text-gray-500 flex items-center gap-1"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg> Email</div>
                                        <div class="text-sm font-medium text-gray-900 col-span-2">{{ $selectedSekolah->email ?: '-' }}</div>
                                    </div>
                                    <div class="grid grid-cols-3 gap-2 items-center">
                                        <div class="text-sm text-gray-500 flex items-center gap-1"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path></svg> Website</div>
                                        <div class="text-sm font-medium text-blue-600 col-span-2 truncate">
                                            @if($selectedSekolah->web)
                                                <a href="{{ str_starts_with($selectedSekolah->web, 'http') ? $selectedSekolah->web : 'http://'.$selectedSekolah->web }}" target="_blank" class="hover:underline">{{ $selectedSekolah->web }}</a>
                                            @else
                                                -
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <h4 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-2">Informasi Kepala Sekolah</h4>
                                <div class="bg-blue-50/50 border border-blue-100 rounded-xl p-4 space-y-3">
                                    <div class="grid grid-cols-3 gap-2">
                                        <div class="text-sm text-gray-500">Nama Kepsek</div>
                                        <div class="text-sm font-medium text-gray-900 col-span-2">{{ $selectedSekolah->nama_kepsek ?: '-' }}</div>
                                    </div>
                                    <div class="grid grid-cols-3 gap-2 items-center">
                                        <div class="text-sm text-gray-500 flex items-center gap-1"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg> HP Kepsek</div>
                                        <div class="text-sm font-medium text-gray-900 col-span-2">{{ $selectedSekolah->hp_kepsek ?: '-' }}</div>
                                    </div>
                                    <div class="grid grid-cols-3 gap-2 items-center">
                                        <div class="text-sm text-gray-500 flex items-center gap-1"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg> Email Kepsek</div>
                                        <div class="text-sm font-medium text-gray-900 col-span-2">{{ $selectedSekolah->email_kepsek ?: '-' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Modal footer -->
                <div class="flex items-center justify-end p-5 rounded-b-2xl border-t border-gray-200 bg-gray-50">
                    <button wire:click="closeDetailModal" type="button" class="text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center transition-colors shadow-sm">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Modal Tambah Sekolah -->
    @if($isAddSekolahModalOpen)
    <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto overflow-x-hidden bg-gray-900 bg-opacity-50 backdrop-blur-sm transition-opacity">
        <div class="relative p-4 w-full max-w-2xl h-full md:h-auto">
            <div class="relative bg-white rounded-2xl shadow-xl">
                <div class="flex justify-between items-center p-5 rounded-t-2xl border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-bold text-gray-900">
                        Tambah Data Sekolah Baru
                    </h3>
                    <button wire:click="closeAddSekolahModal" type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-2 ml-auto inline-flex items-center">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>  
                    </button>
                </div>
                <form wire:submit.prevent="saveSekolah">
                    <div class="p-6 space-y-4 max-h-[70vh] overflow-y-auto">
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">NPSN (8 digit angka)</label>
                                <input wire:model="newNpsn" type="text" class="mt-1 block w-full border border-gray-300 rounded-md py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                                @error('newNpsn') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nama Sekolah</label>
                                <input wire:model="newNama" type="text" class="mt-1 block w-full border border-gray-300 rounded-md py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                                @error('newNama') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Status</label>
                                <select wire:model="newStatus" class="mt-1 block w-full border border-gray-300 rounded-md py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                                    <option value="">Pilih Status</option>
                                    <option value="NEGERI">NEGERI</option>
                                    <option value="SWASTA">SWASTA</option>
                                </select>
                                @error('newStatus') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Kelompok / Jenis</label>
                                <select wire:model="newKelompok" class="mt-1 block w-full border border-gray-300 rounded-md py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                                    <option value="">Pilih Kelompok</option>
                                    <option value="SMA">SMA</option>
                                    <option value="SMK">SMK</option>
                                    <option value="MA">MA</option>
                                    <option value="PESANTREN">PESANTREN</option>
                                    <option value="LAINNYA">LAINNYA</option>
                                </select>
                                @error('newKelompok') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Alamat Lengkap</label>
                            <input wire:model="newAlamat" type="text" class="mt-1 block w-full border border-gray-300 rounded-md py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                            @error('newAlamat') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- SECTION INFO UMUM -->
                        <div class="pt-4 border-t border-gray-200">
                            <h4 class="text-sm font-semibold text-gray-900 mb-4">Informasi Umum</h4>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Akreditasi</label>
                                    <input wire:model="newAkreditasi" type="text" class="mt-1 block w-full border border-gray-300 rounded-md py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="Contoh: A">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Kode Pos</label>
                                    <input wire:model="newKodePos" type="text" class="mt-1 block w-full border border-gray-300 rounded-md py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Jumlah Siswa</label>
                                    <input wire:model="newJumlahSiswa" type="number" class="mt-1 block w-full border border-gray-300 rounded-md py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Kurikulum</label>
                                    <input wire:model="newKurikulum" type="text" class="mt-1 block w-full border border-gray-300 rounded-md py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="K-13 / Merdeka">
                                </div>
                            </div>
                        </div>

                        <!-- SECTION KONTAK SEKOLAH -->
                        <div class="pt-4 border-t border-gray-200">
                            <h4 class="text-sm font-semibold text-gray-900 mb-4">Kontak Sekolah</h4>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Telepon</label>
                                    <input wire:model="newTelepon" type="text" class="mt-1 block w-full border border-gray-300 rounded-md py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Fax</label>
                                    <input wire:model="newFax" type="text" class="mt-1 block w-full border border-gray-300 rounded-md py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4 mt-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Email Sekolah</label>
                                    <input wire:model="newEmail" type="email" class="mt-1 block w-full border border-gray-300 rounded-md py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Website</label>
                                    <input wire:model="newWeb" type="text" class="mt-1 block w-full border border-gray-300 rounded-md py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="https://...">
                                </div>
                            </div>
                        </div>

                        <!-- SECTION KEPALA SEKOLAH -->
                        <div class="pt-4 border-t border-gray-200">
                            <h4 class="text-sm font-semibold text-gray-900 mb-4">Biodata Kepala Sekolah</h4>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Nama Lengkap Kepsek</label>
                                    <input wire:model="newNamaKepsek" type="text" class="mt-1 block w-full border border-gray-300 rounded-md py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">No. HP Kepsek</label>
                                    <input wire:model="newHpKepsek" type="text" class="mt-1 block w-full border border-gray-300 rounded-md py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Email Kepsek</label>
                                    <input wire:model="newEmailKepsek" type="email" class="mt-1 block w-full border border-gray-300 rounded-md py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                </div>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-gray-200">
                            <h4 class="text-sm font-semibold text-gray-900 mb-4">Pemetaan Wilayah</h4>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Provinsi</label>
                                    <select wire:model.live="newProvinsi" class="mt-1 block w-full border border-gray-300 rounded-md py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                        <option value="">Pilih Provinsi</option>
                                        @foreach($provinsiList as $prov)
                                            <option value="{{ $prov->kode_prop }}">{{ $prov->nama_prop }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Kabupaten</label>
                                    <select wire:model.live="newKabupaten" class="mt-1 block w-full border border-gray-300 rounded-md py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm {{ !$newProvinsi ? 'bg-gray-100 cursor-not-allowed' : '' }}" {{ !$newProvinsi ? 'disabled' : '' }}>
                                        <option value="">Pilih Kabupaten</option>
                                        @foreach($formKabupatenList as $kab)
                                            <option value="{{ $kab->kode_kab }}">{{ $kab->nama_kab }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Kecamatan</label>
                                    <select wire:model="newKecamatan" class="mt-1 block w-full border border-gray-300 rounded-md py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm {{ !$newKabupaten ? 'bg-gray-100 cursor-not-allowed' : '' }}" {{ !$newKabupaten ? 'disabled' : '' }} required>
                                        <option value="">Pilih Kecamatan</option>
                                        @foreach($formKecamatanList as $kec)
                                            <option value="{{ $kec->kode_kec }}">{{ $kec->nama_kec }}</option>
                                        @endforeach
                                    </select>
                                    @error('newKecamatan') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="flex items-center justify-end p-5 rounded-b-2xl border-t border-gray-200 bg-gray-50 gap-2">
                        <button wire:click="closeAddSekolahModal" type="button" class="text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 focus:ring-4 focus:outline-none focus:ring-gray-200 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                            Batal
                        </button>
                        <button type="submit" class="text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                            Simpan Data
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>

