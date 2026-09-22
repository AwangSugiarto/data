<div class="py-12" x-data="{ tab: 'manual' }">
    <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-900">Entri Agregat PMB (Total)</h1>
            <p class="text-sm text-gray-500 mt-1">Formulir untuk memperbarui angka daya tampung (kuota), pendaftar, lulus, dan registrasi per program studi. Angka yang Anda masukkan akan langsung mengubah grafik di Dashboard.</p>
        </div>

        <!-- Alpine Tabs Header -->
        <div class="mb-6 border-b border-gray-200">
            <ul class="flex flex-wrap -mb-px text-sm font-medium text-center">
                <li class="mr-2">
                    <button @click="tab = 'manual'" 
                        :class="{'text-purple-700 border-b-2 border-purple-700': tab === 'manual', 'text-gray-500 hover:text-gray-700 hover:border-gray-300': tab !== 'manual'}" 
                        class="inline-block p-4 rounded-t-lg transition-colors flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Entri Manual (Agregat)
                    </button>
                </li>
                <li class="mr-2">
                    <button @click="tab = 'import'" 
                        :class="{'text-green-600 border-b-2 border-green-600': tab === 'import', 'text-gray-500 hover:text-gray-700 hover:border-gray-300': tab !== 'import'}" 
                        class="inline-block p-4 rounded-t-lg transition-colors flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        Import dari Excel (Bulk)
                    </button>
                </li>
            </ul>
        </div>

        <div x-show="tab === 'manual'">
        <!-- Flash Messages (Toast) -->
        <div class="fixed top-5 right-5 z-50 flex flex-col gap-3 w-80">
            @if (session()->has('success'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" 
                     x-transition:enter="transition ease-out duration-300" 
                     x-transition:enter-start="opacity-0 translate-x-10" 
                     x-transition:enter-end="opacity-100 translate-x-0" 
                     x-transition:leave="transition ease-in duration-300" 
                     x-transition:leave-start="opacity-100 translate-x-0" 
                     x-transition:leave-end="opacity-0 translate-x-10"
                     class="bg-green-50 border-l-4 border-green-500 p-4 rounded-lg shadow-lg flex items-start justify-between">
                    <div class="flex items-start">
                        <svg class="h-5 w-5 text-green-500 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <p class="text-sm text-green-700 font-medium">{{ session('success') }}</p>
                    </div>
                    <button @click="show = false" class="text-green-500 hover:text-green-700 font-bold ml-2">&times;</button>
                </div>
            @endif
            @if (session()->has('error'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" 
                     x-transition:enter="transition ease-out duration-300" 
                     x-transition:enter-start="opacity-0 translate-x-10" 
                     x-transition:enter-end="opacity-100 translate-x-0" 
                     x-transition:leave="transition ease-in duration-300" 
                     x-transition:leave-start="opacity-100 translate-x-0" 
                     x-transition:leave-end="opacity-0 translate-x-10"
                     class="bg-red-50 border-l-4 border-red-500 p-4 rounded-lg shadow-lg flex items-start justify-between">
                    <div class="flex items-start">
                        <svg class="h-5 w-5 text-red-500 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <p class="text-sm text-red-700 font-medium">{{ session('error') }}</p>
                    </div>
                    <button @click="show = false" class="text-red-500 hover:text-red-700 font-bold ml-2">&times;</button>
                </div>
            @endif
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <form wire:submit.prevent="save" class="divide-y divide-gray-100">
                
                <!-- 1. Kriteria Data -->
                <div class="p-6 md:p-8 bg-gray-50/50">
                    <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <span class="bg-indigo-100 text-indigo-700 p-1.5 rounded-lg"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg></span>
                        Langkah 1: Pilih Kriteria (Tahun, Jalur & Fakultas)
                    </h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Tahun Akademik -->
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-sm font-medium text-gray-700">Tahun Akademik <span class="text-red-500">*</span></label>
                                <div class="flex items-center gap-3">
                                    <button type="button" onclick="let t = prompt('Masukkan Tahun Akademik Baru (contoh: 2027):'); if(t) @this.tambahTahunAkademik(t);" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                        Tambah
                                    </button>
                                    @if($tahun_akademik_id)
                                    <button type="button" wire:click="hapusTahunAkademik" wire:confirm="Yakin ingin menghapus tahun akademik ini? Data tidak akan bisa dihapus jika sudah ada isian didalamnya." class="text-xs text-red-500 hover:text-red-700 font-medium flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        Hapus
                                    </button>
                                    @endif
                                </div>
                            </div>
                            <select wire:model.live="tahun_akademik_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow outline-none bg-white">
                                <option value="">-- Pilih Tahun --</option>
                                @foreach($tahunList as $tahun)
                                <option value="{{ $tahun->id }}">{{ $tahun->tahun }}</option>
                                @endforeach
                            </select>
                            @error('tahun_akademik_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Jalur Seleksi -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Jalur Seleksi <span class="text-red-500">*</span></label>
                            <select wire:model.live="jalur_seleksi_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow outline-none bg-white">
                                <option value="">-- Pilih Jalur --</option>
                                @foreach($jalurList as $jalur)
                                <option value="{{ $jalur->id }}">{{ $jalur->nama_jalur }}</option>
                                @endforeach
                            </select>
                            @error('jalur_seleksi_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Fakultas -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Fakultas <span class="text-red-500">*</span></label>
                            <select wire:model.live="fakultas_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow outline-none bg-white">
                                <option value="">-- Pilih Fakultas --</option>
                                @foreach($fakultasList as $fakultas)
                                <option value="{{ $fakultas->id }}">{{ $fakultas->nama_fakultas }}</option>
                                @endforeach
                            </select>
                            @error('fakultas_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <!-- 2. Form Angka (Grid) -->
                <div class="relative">
                    
                    <!-- Overlay if not complete -->
                    @if(!$tahun_akademik_id || !$fakultas_id || !$jalur_seleksi_id)
                    <div class="absolute inset-0 z-10 bg-white/80 backdrop-blur-[2px] flex flex-col items-center justify-center p-8">
                        <div class="bg-white px-6 py-4 rounded-xl shadow-lg border border-gray-100 flex flex-col items-center gap-3">
                            <div class="animate-pulse bg-indigo-100 text-indigo-600 p-3 rounded-full">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            </div>
                            <p class="text-sm font-medium text-gray-700 text-center">Lengkapi Tahun, Jalur, dan Fakultas di atas <br>terlebih dahulu untuk menampilkan seluruh form Prodi.</p>
                        </div>
                    </div>
                    @endif

                    <div class="p-6 md:p-8">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                                <span class="bg-green-100 text-green-700 p-1.5 rounded-lg"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/></svg></span>
                                Langkah 2: Masukkan Angka Agregat
                            </h2>
                            
                            <div wire:loading wire:target="tahun_akademik_id, fakultas_id, jalur_seleksi_id" class="text-xs font-medium text-indigo-600 bg-indigo-50 px-3 py-1 rounded-full flex items-center gap-1.5">
                                <svg class="animate-spin h-3 w-3" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                Memuat data prodi...
                            </div>
                        </div>
                        
                        <div class="overflow-x-auto rounded-xl border border-gray-200">
                            <table class="w-full text-sm text-left">
                                <thead class="bg-gray-50 border-b border-gray-200">
                                    <tr>
                                        <th class="px-4 py-3 w-12 text-center">
                                            <input type="checkbox" wire:model.live="selectAllEntries" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                        </th>
                                        <th class="px-2 py-3 font-semibold text-gray-700 text-center w-12">No</th>
                                        <th class="px-4 py-3 font-semibold text-gray-700">Program Studi</th>
                                        <th class="px-3 py-3 font-semibold text-gray-700 text-center w-28">Daya Tampung</th>
                                        <th class="px-2 py-3 font-semibold text-gray-700 text-center w-16" title="Pilihan 1">Pil. 1</th>
                                        <th class="px-2 py-3 font-semibold text-gray-700 text-center w-16" title="Pilihan 2">Pil. 2</th>
                                        <th class="px-2 py-3 font-semibold text-gray-700 text-center w-16" title="Pilihan 3">Pil. 3</th>
                                        <th class="px-2 py-3 font-semibold text-gray-700 text-center w-16" title="Pilihan 4">Pil. 4</th>
                                        <th class="px-3 py-3 font-semibold text-gray-700 text-center w-24">Total Daftar</th>
                                        <th class="px-3 py-3 font-semibold text-gray-700 text-center w-28">Lulus</th>
                                        <th class="px-3 py-3 font-semibold text-gray-700 text-center w-28">Registrasi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 bg-white">
                                    @forelse($prodiList as $index => $prodi)
                                    <tr wire:key="row-{{ $prodi->id }}" class="hover:bg-gray-50 transition-colors {{ !empty($entries[$prodi->id]['selected']) ? 'bg-indigo-50/30' : '' }}">
                                        <td class="px-4 py-3 text-center">
                                            <input type="checkbox" wire:model.live="entries.{{ $prodi->id }}.selected" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                        </td>
                                        <td class="px-2 py-3 text-center text-gray-500 font-medium">{{ $index + 1 }}</td>
                                        <td class="px-4 py-3">
                                            <span class="font-medium text-gray-900">{{ $prodi->nama_prodi }}</span>
                                            <span class="text-xs text-gray-500 ml-1">({{ $prodi->jenjang }})</span>
                                        </td>
                                        <td class="px-2 py-2">
                                            <input type="number" wire:model="entries.{{ $prodi->id }}.kuota" min="0" class="w-full px-2 py-1.5 text-center border border-blue-200 rounded text-blue-700 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 outline-none font-mono font-medium">
                                        </td>
                                        <td class="px-1 py-2">
                                            <input type="number" wire:model="entries.{{ $prodi->id }}.pil1" min="0" class="w-full px-1 py-1.5 text-center border border-orange-200 rounded text-orange-700 focus:ring-1 focus:ring-orange-500 focus:border-orange-500 outline-none font-mono font-medium text-sm">
                                        </td>
                                        <td class="px-1 py-2">
                                            <input type="number" wire:model="entries.{{ $prodi->id }}.pil2" min="0" class="w-full px-1 py-1.5 text-center border border-orange-200 rounded text-orange-700 focus:ring-1 focus:ring-orange-500 focus:border-orange-500 outline-none font-mono font-medium text-sm">
                                        </td>
                                        <td class="px-1 py-2">
                                            <input type="number" wire:model="entries.{{ $prodi->id }}.pil3" min="0" class="w-full px-1 py-1.5 text-center border border-orange-200 rounded text-orange-700 focus:ring-1 focus:ring-orange-500 focus:border-orange-500 outline-none font-mono font-medium text-sm">
                                        </td>
                                        <td class="px-1 py-2">
                                            <input type="number" wire:model="entries.{{ $prodi->id }}.pil4" min="0" class="w-full px-1 py-1.5 text-center border border-orange-200 rounded text-orange-700 focus:ring-1 focus:ring-orange-500 focus:border-orange-500 outline-none font-mono font-medium text-sm">
                                        </td>
                                        <td class="px-2 py-2 text-center font-bold text-orange-700 bg-orange-50/50" x-data x-text="(parseInt($wire.entries[{{ $prodi->id }}].pil1)||0) + (parseInt($wire.entries[{{ $prodi->id }}].pil2)||0) + (parseInt($wire.entries[{{ $prodi->id }}].pil3)||0) + (parseInt($wire.entries[{{ $prodi->id }}].pil4)||0)"></td>
                                        <td class="px-2 py-2">
                                            <input type="number" wire:model="entries.{{ $prodi->id }}.lulus" min="0" class="w-full px-2 py-1.5 text-center border border-green-200 rounded text-green-700 focus:ring-1 focus:ring-green-500 focus:border-green-500 outline-none font-mono font-medium">
                                        </td>
                                        <td class="px-2 py-2">
                                            <input type="number" wire:model="entries.{{ $prodi->id }}.registrasi" min="0" class="w-full px-2 py-1.5 text-center border border-purple-200 rounded text-purple-700 focus:ring-1 focus:ring-purple-500 focus:border-purple-500 outline-none font-mono font-medium">
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="10" class="px-4 py-8 text-center text-gray-500">Pilih Fakultas terlebih dahulu.</td>
                                    </tr>
                                    @endforelse
                                    
                                    @if(count($prodiList) > 0)
                                    <tr class="bg-gray-50/80 font-bold border-t-2 border-gray-200" x-data="{
                                        getTotal(key) {
                                            return Object.values($wire.entries).reduce((sum, entry) => sum + (parseInt(entry[key]) || 0), 0);
                                        },
                                        getTotalDaftar() {
                                            return this.getTotal('pil1') + this.getTotal('pil2') + this.getTotal('pil3') + this.getTotal('pil4');
                                        }
                                    }">
                                        <td colspan="3" class="px-4 py-3 text-right text-gray-700 uppercase text-xs tracking-wider">Grand Total</td>
                                        <td class="px-2 py-3 text-center text-blue-700" x-text="getTotal('kuota').toLocaleString('id-ID')"></td>
                                        <td class="px-1 py-3 text-center text-orange-700" x-text="getTotal('pil1').toLocaleString('id-ID')"></td>
                                        <td class="px-1 py-3 text-center text-orange-700" x-text="getTotal('pil2').toLocaleString('id-ID')"></td>
                                        <td class="px-1 py-3 text-center text-orange-700" x-text="getTotal('pil3').toLocaleString('id-ID')"></td>
                                        <td class="px-1 py-3 text-center text-orange-700" x-text="getTotal('pil4').toLocaleString('id-ID')"></td>
                                        <td class="px-2 py-3 text-center text-orange-800 bg-orange-100/50" x-text="getTotalDaftar().toLocaleString('id-ID')"></td>
                                        <td class="px-2 py-3 text-center text-green-700" x-text="getTotal('lulus').toLocaleString('id-ID')"></td>
                                        <td class="px-2 py-3 text-center text-purple-700" x-text="getTotal('registrasi').toLocaleString('id-ID')"></td>
                                    </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Form Action -->
                <div class="p-6 md:px-8 bg-gray-50 flex items-center justify-between border-t border-gray-100 rounded-b-2xl">
                    <div class="text-sm text-gray-500 flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Pastikan untuk mencentang kotak di kiri pada program studi yang ingin disimpan.
                    </div>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('dashboard') }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 focus:outline-none transition-colors shadow-sm">
                            Batal
                        </a>
                        <button type="submit" class="px-5 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors shadow-sm inline-flex items-center disabled:opacity-50 disabled:cursor-not-allowed" 
                            {{ (!$tahun_akademik_id || !$fakultas_id || !$jalur_seleksi_id || empty($entries)) ? 'disabled' : '' }}>
                            <svg wire:loading wire:target="save" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Simpan Data Terpilih
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Tabel Data Existing (Ditampilkan di bawah form jika tahun_akademik_id dipilih) -->
        <div class="mt-8 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6 md:p-8 bg-gray-50/50 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                    <span class="bg-purple-100 text-purple-700 p-1.5 rounded-lg"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg></span>
                    Informasi Data Tersimpan
                </h2>
                @if($tahun_akademik_id)
                <div class="flex items-center gap-3">
                    <button wire:click="exportExcel" 
                        class="text-sm bg-green-50 text-green-700 px-3 py-1.5 rounded-lg border border-green-200 hover:bg-green-100 transition-colors font-medium flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Download Excel
                    </button>
                    @if(count($selectedRows) > 0)
                    <button wire:click="deleteSelected" 
                        wire:confirm="Yakin ingin menghapus {{ count($selectedRows) }} data terpilih secara permanen?"
                        class="text-sm bg-red-50 text-red-600 px-3 py-1.5 rounded-lg border border-red-200 hover:bg-red-100 transition-colors font-medium flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Hapus Terpilih ({{ count($selectedRows) }})
                    </button>
                    @endif
                    <span class="text-sm text-gray-500 bg-white px-3 py-1 rounded-full border border-gray-200">
                        Tahun: {{ $tahunList->firstWhere('id', $tahun_akademik_id)?->tahun }}
                    </span>
                </div>
                @endif
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 border-b border-gray-100 text-gray-700">
                        <tr>
                            <th class="px-4 py-4 w-12 text-center">
                                <input type="checkbox" wire:model.live="selectAll" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                            </th>
                            <th class="px-2 py-4 font-semibold text-center w-12">No</th>
                            <th class="px-6 py-4 font-semibold">Fakultas & Prodi</th>
                            <th class="px-6 py-4 font-semibold">Jalur Seleksi</th>
                            <th class="px-6 py-4 font-semibold text-right">Kuota</th>
                            <th class="px-6 py-4 font-semibold text-right">Pendaftar</th>
                            <th class="px-6 py-4 font-semibold text-right">Lulus</th>
                            <th class="px-6 py-4 font-semibold text-right">Registrasi</th>
                            <th class="px-6 py-4 font-semibold text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @if(!$tahun_akademik_id)
                        <tr>
                            <td colspan="9" class="px-6 py-12 text-center text-gray-500">
                                <svg class="mx-auto h-12 w-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                Silakan pilih Tahun Akademik pada form di atas terlebih dahulu.
                            </td>
                        </tr>
                        @elseif(isset($listData) && count($listData) > 0)
                            @foreach($listData as $index => $row)
                            <tr wire:key="data-row-{{ $row->tahun_akademik_id }}-{{ $row->program_studi_id }}-{{ $row->jalur_seleksi_id }}" class="hover:bg-gray-50 transition-colors {{ in_array($row->tahun_akademik_id . '-' . $row->program_studi_id . '-' . $row->jalur_seleksi_id, $selectedRows) ? 'bg-indigo-50/50' : '' }}">
                                <td class="px-4 py-4 text-center">
                                    <input type="checkbox" value="{{ $row->tahun_akademik_id }}-{{ $row->program_studi_id }}-{{ $row->jalur_seleksi_id }}" wire:model.live="selectedRows" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                </td>
                                <td class="px-2 py-4 text-center text-gray-500 font-medium">{{ $index + 1 }}</td>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-gray-900">{{ $row->programStudi->nama_prodi }}</div>
                                    <div class="text-xs text-gray-500">{{ $row->programStudi->fakultas->nama_fakultas }}</div>
                                </td>
                                <td class="px-6 py-4 text-gray-700">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                        {{ $row->jalurSeleksi->nama_jalur }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right font-mono">{{ number_format($row->kuota_akhir ?? 0, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 text-right font-mono">{{ number_format($row->jumlah_daftar ?? 0, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 text-right font-mono">{{ number_format($row->jumlah_lulus ?? 0, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 text-right font-mono">{{ number_format($row->jumlah_registrasi ?? 0, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <button wire:click="editRow({{ $row->tahun_akademik_id }}, {{ $row->program_studi_id }}, {{ $row->jalur_seleksi_id }})" 
                                            class="inline-flex items-center justify-center p-1.5 text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 hover:text-blue-700 transition-colors" title="Edit Data">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                        </button>
                                        <button wire:click="deleteRow({{ $row->tahun_akademik_id }}, {{ $row->program_studi_id }}, {{ $row->jalur_seleksi_id }})" 
                                            wire:confirm="Yakin ingin menghapus data ini secara permanen?"
                                            class="inline-flex items-center justify-center p-1.5 text-red-600 bg-red-50 rounded-lg hover:bg-red-100 hover:text-red-700 transition-colors" title="Hapus Data">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach

                            <tr class="bg-gray-50/80 font-bold border-t-2 border-gray-200">
                                <td colspan="4" class="px-4 py-4 text-right text-gray-700 uppercase text-xs tracking-wider">Total (Halaman Ini)</td>
                                <td class="px-6 py-4 text-right font-mono">{{ number_format($listData->sum('kuota_akhir'), 0, ',', '.') }}</td>
                                <td class="px-6 py-4 text-right font-mono">{{ number_format($listData->sum('jumlah_daftar'), 0, ',', '.') }}</td>
                                <td class="px-6 py-4 text-right font-mono">{{ number_format($listData->sum('jumlah_lulus'), 0, ',', '.') }}</td>
                                <td class="px-6 py-4 text-right font-mono">{{ number_format($listData->sum('jumlah_registrasi'), 0, ',', '.') }}</td>
                                <td></td>
                            </tr>
                        @else
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                <svg class="mx-auto h-12 w-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
                                Informasi data masih kosong untuk tahun tersebut.
                            </td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            @if($listData instanceof \Illuminate\Pagination\LengthAwarePaginator && $listData->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 bg-white">
                {{ $listData->links() }}
            </div>
            @endif
        </div>
        
    </div>

    <!-- TAB 2: Import dari Excel -->
    <div x-show="tab === 'import'" x-cloak>
        <livewire:import.import-wizard />
        </div>
    </div>
</div>

<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('scrollToForm', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    });
</script>
