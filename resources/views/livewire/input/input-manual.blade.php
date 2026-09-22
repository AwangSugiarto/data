<div class="py-12" x-data="{ tab: 'manual' }">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-900">Entri Individu PMB (Satu per Satu)</h1>
            <p class="text-sm text-gray-500 mt-1">Formulir untuk memasukkan data calon mahasiswa baru secara spesifik per individu. Pilihlah status dari daftar hingga pendaftaran ulang.</p>
        </div>

        <!-- Alpine Tabs Header -->
        <div class="mb-6 border-b border-gray-200">
            <ul class="flex flex-wrap -mb-px text-sm font-medium text-center">
                <li class="mr-2">
                    <button @click="tab = 'manual'" 
                        :class="{'text-indigo-700 border-b-2 border-indigo-700': tab === 'manual', 'text-gray-500 hover:text-gray-700 hover:border-gray-300': tab !== 'manual'}" 
                        class="inline-block p-4 rounded-t-lg transition-colors flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        Entri Manual (Individu)
                    </button>
                </li>
                <li class="mr-2">
                    <button @click="tab = 'import'" 
                        :class="{'text-green-600 border-b-2 border-green-600': tab === 'import', 'text-gray-500 hover:text-gray-700 hover:border-gray-300': tab !== 'import'}" 
                        class="inline-block p-4 rounded-t-lg transition-colors flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        Import dari Excel (Pendaftar)
                    </button>
                </li>
                <li class="mr-2">
                    <button @click="tab = 'import_aktif'" 
                        :class="{'text-purple-600 border-b-2 border-purple-600': tab === 'import_aktif', 'text-gray-500 hover:text-gray-700 hover:border-gray-300': tab !== 'import_aktif'}" 
                        class="inline-block p-4 rounded-t-lg transition-colors flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        Import Mahasiswa Aktif
                    </button>
                </li>
            </ul>
        </div>

        <div x-show="tab === 'manual'">

        <!-- Flash Messages -->
        @if (session()->has('success'))
            <div class="mb-6 bg-green-50 border border-green-200 p-4 rounded-xl shadow-sm flex items-start">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-green-700 font-medium">{{ session('success') }}</p>
                </div>
            </div>
        @endif
        @if (session()->has('error'))
            <div class="mb-6 bg-red-50 border border-red-200 p-4 rounded-xl shadow-sm flex items-start">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-red-700 font-medium">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <form wire:submit.prevent="save" class="divide-y divide-gray-100">
                
                <!-- 1. Data Akademik -->
                <div class="p-6 md:p-8">
                    <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <span class="bg-indigo-100 text-indigo-700 p-1.5 rounded-lg"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg></span>
                        Data Akademik & Pendaftaran
                    </h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Tahun Akademik -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tahun Akademik <span class="text-red-500">*</span></label>
                            <select wire:model.live="tahun_akademik_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow outline-none">
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
                            <select wire:model.live="jalur_seleksi_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow outline-none">
                                <option value="">-- Pilih Jalur --</option>
                                @foreach($jalurList as $jalur)
                                <option value="{{ $jalur->id }}">{{ $jalur->nama_jalur }}</option>
                                @endforeach
                            </select>
                            @error('jalur_seleksi_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Fakultas -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Fakultas Pilihan <span class="text-red-500">*</span></label>
                            <select wire:model.live="fakultas_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow outline-none">
                                <option value="">-- Pilih Fakultas --</option>
                                @foreach($fakultasList as $fakultas)
                                <option value="{{ $fakultas->id }}">{{ $fakultas->nama_fakultas }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Prodi -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Program Studi Pilihan <span class="text-red-500">*</span></label>
                            <select wire:model.live="program_studi_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow outline-none {{ empty($prodiList) ? 'bg-gray-100 cursor-not-allowed' : '' }}" {{ empty($prodiList) ? 'disabled' : '' }}>
                                <option value="">-- Pilih Prodi --</option>
                                @foreach($prodiList as $prodi)
                                <option value="{{ $prodi->id }}">{{ $prodi->nama_prodi }} ({{ $prodi->jenjang }})</option>
                                @endforeach
                            </select>
                            @error('program_studi_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Status Penerimaan -->
                        <div class="md:col-span-2 bg-indigo-50/50 p-4 rounded-xl border border-indigo-100">
                            <label class="block text-sm font-medium text-gray-900 mb-3">Status Saat Ini <span class="text-red-500">*</span></label>
                            <div class="flex flex-wrap gap-4">
                                <label class="inline-flex items-center">
                                    <input type="radio" wire:model="status" value="pendaftar" class="form-radio h-4 w-4 text-indigo-600 focus:ring-indigo-500">
                                    <span class="ml-2 text-sm text-gray-700">Pendaftar Saja</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" wire:model="status" value="lulus" class="form-radio h-4 w-4 text-green-600 focus:ring-green-500">
                                    <span class="ml-2 text-sm text-gray-700">Lulus Seleksi</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" wire:model="status" value="registrasi" class="form-radio h-4 w-4 text-purple-600 focus:ring-purple-500">
                                    <span class="ml-2 text-sm text-gray-700">Sudah Registrasi</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" wire:model="status" value="alumni" class="form-radio h-4 w-4 text-gray-600 focus:ring-gray-500">
                                    <span class="ml-2 text-sm text-gray-700">Alumni</span>
                                </label>
                            </div>
                            @error('status') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <!-- 2. Data Personal -->
                <div class="p-6 md:p-8">
                    <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <span class="bg-blue-100 text-blue-700 p-1.5 rounded-lg"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg></span>
                        Data Personal Individu
                    </h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Nomor Tes -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Tes / Pendaftaran <span class="text-red-500">*</span></label>
                            <input wire:model="nomor_tes" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow outline-none font-mono" placeholder="Contoh: 202410001">
                            @error('nomor_tes') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- NISN -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">NISN</label>
                            <input wire:model="nisn" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow outline-none font-mono" placeholder="Nomor Induk Siswa Nasional">
                            @error('nisn') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Nama Lengkap -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap Sesuai Ijazah <span class="text-red-500">*</span></label>
                            <input wire:model="nama" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow outline-none uppercase" placeholder="NAMA LENGKAP">
                            @error('nama') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Jenis Kelamin -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Kelamin <span class="text-red-500">*</span></label>
                            <select wire:model="jenis_kelamin" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow outline-none">
                                <option value="">-- Pilih Jenis Kelamin --</option>
                                <option value="L">Laki-laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                            @error('jenis_kelamin') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Wilayah -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Provinsi Asal <span class="text-red-500">*</span></label>
                            <select wire:model="wilayah_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow outline-none">
                                <option value="">-- Pilih Provinsi --</option>
                                @foreach($wilayahList as $wil)
                                <option value="{{ $wil->id }}">{{ $wil->provinsi }}</option>
                                @endforeach
                            </select>
                            @error('wilayah_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <!-- 3. Data Pendukung -->
                <div class="p-6 md:p-8">
                    <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <span class="bg-orange-100 text-orange-700 p-1.5 rounded-lg"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg></span>
                        Pendidikan Asal & UKT
                    </h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Asal Sekolah -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Sekolah Asal <span class="text-red-500">*</span></label>
                            <input wire:model="asal_sekolah" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow outline-none" placeholder="Contoh: SMA Negeri 1 Palembang">
                            @error('asal_sekolah') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- NPSN -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">NPSN</label>
                            <input wire:model="npsn" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow outline-none" placeholder="Nomor Pokok Sekolah Nasional">
                            @error('npsn') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Jenis Sekolah -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Sekolah <span class="text-red-500">*</span></label>
                            <select wire:model="jenis_sekolah" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow outline-none">
                                <option value="">-- Pilih Jenis --</option>
                                <option value="SMA">SMA (Sekolah Menengah Atas)</option>
                                <option value="SMK">SMK (Sekolah Menengah Kejuruan)</option>
                                <option value="MA">MA (Madrasah Aliyah)</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                            @error('jenis_sekolah') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Kelompok UKT -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Penetapan Kelompok UKT <span class="text-red-500">*</span></label>
                            <select wire:model="kelompok_ukt_id" class="w-full md:w-1/2 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow outline-none">
                                <option value="">-- Pilih Kelompok UKT --</option>
                                @foreach($uktList as $ukt)
                                <option value="{{ $ukt->id }}">Kelompok {{ $ukt->kelompok }} - Rp {{ number_format($ukt->nominal, 0, ',', '.') }}</option>
                                @endforeach
                            </select>
                            @error('kelompok_ukt_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <!-- Form Action -->
                <div class="p-6 md:px-8 bg-gray-50 flex items-center justify-end gap-3">
                    @if($editId)
                        <button type="button" wire:click="cancelEdit" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 focus:outline-none transition-colors shadow-sm">
                            Batal Edit
                        </button>
                    @endif
                    <button type="submit" class="px-5 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors shadow-sm inline-flex items-center">
                        <svg wire:loading wire:target="save" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ $editId ? 'Perbarui Data' : 'Simpan Data' }}
                    </button>
                </div>
            </form>
        </div>

        <!-- Tabel Data Tersimpan -->
        <div class="mt-10 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6 md:p-8 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold text-gray-900">Data Baru Saja Ditambahkan / Diubah</h2>
                    <p class="text-sm text-gray-500 mt-1">Data di bawah ini otomatis disaring berdasarkan Tahun, Jalur, dan Prodi yang Anda pilih di form atas.</p>
                </div>
                
                @if(count($selectedRows) > 0)
                <button type="button" wire:click="bulkDelete" wire:confirm="Yakin ingin menghapus {{ count($selectedRows) }} data terpilih?" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition">
                    Hapus Terpilih ({{ count($selectedRows) }})
                </button>
                @endif
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-600">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th scope="col" class="px-6 py-3 w-10 text-center">
                                <input type="checkbox" wire:model.live="selectAll" class="w-4 h-4 text-indigo-600 bg-gray-100 border-gray-300 rounded focus:ring-indigo-500">
                            </th>
                            <th scope="col" class="px-6 py-3 w-16 text-center">No.</th>
                            <th scope="col" class="px-6 py-3">No. Tes</th>
                            <th scope="col" class="px-6 py-3">Nama</th>
                            <th scope="col" class="px-6 py-3">Prodi / Jalur</th>
                            <th scope="col" class="px-6 py-3">Status</th>
                            <th scope="col" class="px-6 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentData as $index => $row)
                        <tr class="bg-white border-b hover:bg-gray-50 transition">
                            <td class="px-6 py-4 text-center">
                                <input type="checkbox" wire:model.live="selectedRows" value="{{ $row->id }}" class="w-4 h-4 text-indigo-600 bg-gray-100 border-gray-300 rounded focus:ring-indigo-500">
                            </td>
                            <td class="px-6 py-4 text-center text-gray-500">
                                {{ ($recentData->currentPage() - 1) * $recentData->perPage() + $loop->iteration }}
                            </td>
                            <td class="px-6 py-4 font-mono text-gray-900">{{ $row->nomor_tes }}</td>
                            <td class="px-6 py-4 font-medium text-gray-900">
                                {{ ucwords(strtolower($row->nama)) }}<br>
                                <span class="text-xs text-gray-500 font-normal">NISN: {{ $row->nisn ?? '-' }}</span>
                            </td>
                            <td class="px-6 py-4">
                                {{ $row->programStudi->nama_prodi ?? '-' }}<br>
                                <span class="text-xs text-gray-500">{{ $row->jalurSeleksi->nama_jalur ?? '-' }}</span>
                            </td>
                            <td class="px-6 py-4">
                                @if($row->status === 'lulus')
                                    <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded border border-green-200">Lulus</span>
                                @elseif($row->status === 'registrasi')
                                    <span class="bg-purple-100 text-purple-800 text-xs font-medium px-2.5 py-0.5 rounded border border-purple-200">Registrasi</span>
                                @elseif($row->status === 'alumni')
                                    <span class="bg-gray-100 text-gray-800 text-xs font-medium px-2.5 py-0.5 rounded border border-gray-200">Alumni</span>
                                @else
                                    <span class="bg-indigo-100 text-indigo-800 text-xs font-medium px-2.5 py-0.5 rounded border border-indigo-200">Pendaftar</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right space-x-2 whitespace-nowrap">
                                <button wire:click="edit({{ $row->id }})" class="font-medium text-indigo-600 hover:text-indigo-900 focus:outline-none">Edit</button>
                                <button wire:click="delete({{ $row->id }})" wire:confirm="Yakin ingin menghapus data ini?" class="font-medium text-red-600 hover:text-red-900 focus:outline-none">Hapus</button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                Belum ada data terbaru.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($recentData->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $recentData->links() }}
            </div>
            @endif
        </div>
        </div>

        <!-- TAB 2: Import dari Excel -->
        <div x-show="tab === 'import'" x-cloak>
            <livewire:import.import-individu-wizard />
        </div>

        <!-- TAB 3: Import Mahasiswa Aktif -->
        <div x-show="tab === 'import_aktif'" x-cloak>
            <livewire:import.import-mahasiswa-aktif-wizard />
        </div>
    </div>
</div>
