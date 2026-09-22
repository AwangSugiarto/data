<div class="py-8 px-4 w-full max-w-6xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Master Data Beasiswa</h1>
            <p class="text-sm text-gray-500 mt-1">Kelola jenis beasiswa dan data penerima per tahun akademik</p>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm">{!! nl2br(e(session('error'))) !!}</div>
    @endif

    <div class="mb-6 border-b border-gray-200">
        <nav class="-mb-px flex space-x-8">
            <button wire:click="setTab('master')" class="{{ $activeTab === 'master' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition">
                Daftar Jenis Beasiswa
            </button>
            <button wire:click="setTab('penerima')" class="{{ $activeTab === 'penerima' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition">
                Data Penerima Beasiswa
            </button>
        </nav>
    </div>

    @if($activeTab === 'master')
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex justify-between items-center mb-4">
                <input wire:model.live.debounce.300ms="searchMaster" type="text" placeholder="Cari beasiswa..." class="border border-gray-300 rounded-lg px-4 py-2 text-sm w-full max-w-md focus:ring-2 focus:ring-blue-500 focus:outline-none">
                <button wire:click="openMasterModal" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Tambah Jenis
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700 w-16 text-center">No</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700 w-48">Kode</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Nama Beasiswa</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Keterangan</th>
                            <th class="px-4 py-3 text-center font-semibold text-gray-700 w-32">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($masterList as $master)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-3 text-center text-gray-500">{{ $masterList->firstItem() + $loop->index }}</td>
                            <td class="px-4 py-3 font-medium text-blue-700">{{ $master->kode_beasiswa }}</td>
                            <td class="px-4 py-3 text-gray-900 font-semibold">{{ $master->nama_beasiswa }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $master->keterangan ?: '-' }}</td>
                            <td class="px-4 py-3 text-center flex items-center justify-center gap-2">
                                <button wire:click="openMasterModal({{ $master->id }})" class="text-blue-600 hover:text-blue-800">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                </button>
                                <button onclick="confirm('Yakin ingin menghapus jenis beasiswa ini? Data penerima beasiswa terkait juga akan terhapus!') || event.stopImmediatePropagation()" wire:click="deleteMaster({{ $master->id }})" class="text-red-500 hover:text-red-700">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="px-4 py-8 text-center text-gray-400">Tidak ada data jenis beasiswa.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $masterList->links() }}
            </div>
        </div>
    @endif

    @if($activeTab === 'penerima')
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-4">
                <div class="flex items-center gap-3 w-full md:w-auto">
                    <select wire:model.live="filterTahunAkademik" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none bg-white">
                        <option value="">-- Pilih Tahun --</option>
                        @foreach($tahunList as $th)
                            <option value="{{ $th->id }}">{{ $th->tahun }}</option>
                        @endforeach
                    </select>
                    <input wire:model.live.debounce.300ms="searchPenerima" type="text" placeholder="Cari NIM/Nama/Prodi..." class="border border-gray-300 rounded-lg px-4 py-2 text-sm w-full md:w-64 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
                
                <button wire:click="openImportModal" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition flex items-center gap-2 w-full md:w-auto justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    Import Penerima
                </button>
            </div>

            @if(!$filterTahunAkademik)
                <div class="bg-blue-50 border border-blue-200 text-blue-800 p-4 rounded-lg flex items-center gap-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Silakan pilih Tahun Akademik untuk melihat daftar penerima beasiswa.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700 w-16 text-center">No</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Mahasiswa</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Program Studi</th>
                                <th class="px-4 py-3 text-center font-semibold text-gray-700">Jenis Beasiswa</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($penerimaList as $penerima)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-4 py-3 text-center text-gray-500">{{ $penerimaList->firstItem() + $loop->index }}</td>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-900">{{ $penerima->nim }}</div>
                                    <div class="text-xs text-gray-500">{{ $penerima->nama_mahasiswa ?: 'Nama tidak tersedia' }}</div>
                                </td>
                                <td class="px-4 py-3 text-gray-700">{{ $penerima->programStudi->nama_prodi }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="px-2.5 py-1 bg-green-100 text-green-800 rounded-lg text-xs font-semibold border border-green-200">{{ $penerima->beasiswa->nama_beasiswa }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="px-4 py-8 text-center text-gray-400">Tidak ada data penerima beasiswa untuk filter ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $penerimaList->links() }}
                </div>
            @endif
        </div>
    @endif

    {{-- Modal Jenis Beasiswa --}}
    @if($showMasterModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4 p-6" wire:click.stop>
            <h2 class="text-lg font-bold text-gray-900 mb-5 pb-3 border-b border-gray-100">
                {{ $masterId ? 'Edit' : 'Tambah' }} Jenis Beasiswa
            </h2>
            
            <form wire:submit.prevent="saveMaster" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kode Beasiswa</label>
                    <input type="text" wire:model="kode_beasiswa" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none uppercase" placeholder="Misal: KIP-K">
                    @error('kode_beasiswa') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Beasiswa</label>
                    <input type="text" wire:model="nama_beasiswa" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="Misal: KIP Kuliah">
                    @error('nama_beasiswa') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan (Opsional)</label>
                    <textarea wire:model="keterangan" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="Penjelasan singkat..."></textarea>
                    @error('keterangan') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>
                
                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                    <button type="button" wire:click="$set('showMasterModal', false)" class="px-4 py-2 text-sm text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50 transition">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">Simpan</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- Modal Import Excel --}}
    @if($showImportModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4 p-6" wire:click.stop>
            <div class="flex justify-between items-center mb-5 pb-3 border-b border-gray-100">
                <h2 class="text-lg font-bold text-gray-900">Import Data Penerima</h2>
                <button wire:click="$set('showImportModal', false)" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <div class="space-y-4">
                <div class="bg-blue-50 text-blue-800 p-3 rounded-lg text-xs">
                    <ul class="list-disc pl-4 space-y-1">
                        <li>Unduh template dan isi data.</li>
                        <li>Pastikan kolom <strong>tahun_akademik</strong> diisi tahun yang valid (contoh: 2024).</li>
                        <li>Pastikan <strong>Kode Prodi</strong> sesuai dengan master program studi.</li>
                        <li>Pastikan <strong>Kode Beasiswa</strong> sudah didaftarkan di sistem (Tab Master).</li>
                    </ul>
                </div>

                <button wire:click="downloadTemplate" class="w-full bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 px-4 py-2 rounded-lg text-sm font-medium flex items-center justify-center gap-2 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    Unduh Template Excel
                </button>

                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">File Excel (.xlsx)</label>
                    <input type="file" wire:model="importFile" accept=".xlsx, .xls" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 border border-gray-300 rounded-lg">
                    <div wire:loading wire:target="importFile" class="text-xs text-blue-600 mt-1">Mengunggah file...</div>
                    @error('importFile') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>
            
            <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-gray-100">
                <button wire:click="$set('showImportModal', false)" class="px-4 py-2 text-sm text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50 transition">Batal</button>
                <button wire:click="importData" wire:loading.attr="disabled" wire:target="importData,importFile" class="px-4 py-2 text-sm text-white bg-green-600 rounded-lg hover:bg-green-700 transition disabled:opacity-50">Mulai Import</button>
            </div>
        </div>
    </div>
    @endif
</div>
