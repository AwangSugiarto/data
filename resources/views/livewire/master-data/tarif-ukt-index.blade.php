<div class="py-8 px-4 w-full">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Tarif UKT Berdasarkan Program Studi</h1>
            <p class="text-sm text-gray-500 mt-1">Atur besaran nominal UKT per prodi</p>
        </div>
        <div class="flex items-center gap-3">
            <select wire:model.live="filterTahunAkademik" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none bg-white shadow-sm">
                <option value="">-- Pilih Tahun Akademik --</option>
                @foreach($tahunList as $th)
                    <option value="{{ $th->id }}">{{ $th->tahun }}</option>
                @endforeach
            </select>
            <button wire:click="openImportModal" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2 transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Import Excel
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm">{{ session('error') }}</div>
    @endif

    @if(!$filterTahunAkademik)
        <div class="bg-blue-50 border border-blue-200 text-blue-800 p-4 rounded-lg flex items-center gap-3">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            Silakan pilih Tahun Akademik terlebih dahulu untuk mengelola tarif UKT.
        </div>
    @elseif($kelompokUktList->isEmpty())
        <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 p-4 rounded-lg flex items-center gap-3">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            Belum ada definisi Kelompok UKT untuk tahun akademik ini. Silakan klik tombol <strong>"Import Excel"</strong> di pojok kanan atas, unduh template-nya, dan unggah kembali untuk mendaftarkan Kelompok UKT secara otomatis berdasarkan kolom di Excel Anda.
        </div>
    @else
        <div class="mb-4 flex gap-3">
            <input wire:model.live.debounce.300ms="searchProdi" type="text" placeholder="Cari program studi..." class="border border-gray-300 rounded-lg px-4 py-2 text-sm w-full max-w-md focus:ring-2 focus:ring-blue-500 focus:outline-none shadow-sm">
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="sticky left-0 z-10 bg-gray-50 px-4 py-3 text-left font-semibold text-gray-700 w-12 text-center border-r border-gray-200">No</th>
                            <th class="sticky left-[4rem] z-10 bg-gray-50 px-4 py-3 text-left font-semibold text-gray-700 w-64 border-r border-gray-200">Program Studi</th>
                            <th class="px-4 py-3 text-center font-semibold text-gray-700 w-32">Jenjang</th>
                            @foreach($kelompokUktList as $kelompok)
                                <th class="px-4 py-3 text-right font-semibold text-gray-700 whitespace-nowrap text-xs">{{ $kelompok->kelompok }}</th>
                            @endforeach
                            <th class="px-4 py-3 text-right font-semibold text-gray-700 whitespace-nowrap text-xs">SPP</th>
                            <th class="sticky right-0 z-10 bg-gray-50 px-4 py-3 text-center font-semibold text-gray-700 min-w-[150px] border-l border-gray-200">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($prodiList as $p)
                        <tr class="hover:bg-gray-50 transition group">
                            <td class="sticky left-0 z-10 bg-white group-hover:bg-gray-50 px-4 py-3 text-center text-gray-500 text-xs border-r border-gray-100">{{ $prodiList->firstItem() + $loop->index }}</td>
                            <td class="sticky left-[4rem] z-10 bg-white group-hover:bg-gray-50 px-4 py-3 border-r border-gray-100 min-w-[200px]">
                                <div class="font-medium text-gray-900">{{ $p->nama_prodi }}</div>
                                <div class="text-xs text-gray-500">{{ $p->fakultas?->nama_fakultas }}</div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $p->jenjang === 'S1' ? 'bg-blue-100 text-blue-800' : ($p->jenjang === 'S2' ? 'bg-purple-100 text-purple-800' : 'bg-orange-100 text-orange-800') }}">
                                    {{ $p->jenjang }}
                                </span>
                            </td>
                            @foreach($kelompokUktList as $kelompok)
                                @php
                                    $tarif = $p->tarifUkt->where('kelompok_ukt_id', $kelompok->id)->first();
                                @endphp
                                <td class="px-4 py-3 text-right text-gray-600 text-xs whitespace-nowrap">
                                    {{ $tarif && $tarif->nominal > 0 ? number_format($tarif->nominal, 0, ',', '.') : '-' }}
                                </td>
                            @endforeach
                            <td class="px-4 py-3 text-right text-gray-600 text-xs whitespace-nowrap">
                                @php
                                    $spp = $p->tarifSpp->first();
                                @endphp
                                <span class="text-purple-700 font-medium">
                                    {{ $spp && $spp->nominal > 0 ? number_format($spp->nominal, 0, ',', '.') : '-' }}
                                </span>
                            </td>
                            <td class="sticky right-0 z-10 bg-white group-hover:bg-gray-50 px-4 py-3 text-center border-l border-gray-100">
                                <button wire:click="openEditTarif({{ $p->id }}, '{{ addslashes($p->nama_prodi) }}', '{{ $p->jenjang }}')" class="bg-blue-50 text-blue-600 hover:bg-blue-100 border border-blue-200 px-3 py-1.5 rounded-md text-xs font-medium transition flex items-center gap-2 justify-center mx-auto">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                    Atur Tarif
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="{{ 4 + count($kelompokUktList) }}" class="px-4 py-8 text-center text-gray-400">Tidak ada program studi ditemukan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($prodiList->hasPages())
                <div class="px-4 py-3 border-t border-gray-200 bg-gray-50">
                    {{ $prodiList->links() }}
                </div>
            @endif
        </div>
    @endif

    {{-- Modal Atur Tarif --}}
    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-3xl mx-4 p-6" wire:click.stop>
            <div class="flex justify-between items-center mb-5 pb-3 border-b border-gray-100">
                <h2 class="text-xl font-bold text-gray-900">
                    Atur Tarif Biaya Kuliah <br>
                    <span class="text-sm font-normal text-blue-600">{{ $selectedProdiName }} ({{ $selectedProdiJenjang }})</span>
                </h2>
                <button wire:click="$set('showModal', false)" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <div class="max-h-[60vh] overflow-y-auto pr-2 space-y-4">
                @if(in_array(strtoupper($selectedProdiJenjang), ['S1', 'D3', 'D4']))
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($kelompokUktList as $kelompok)
                        <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                            <label class="block text-sm font-semibold text-gray-800 mb-2">UKT: {{ $kelompok->kelompok }}</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">Rp</span>
                                </div>
                                <input wire:model="nominals.{{ $kelompok->id }}" type="number" min="0" class="w-full border border-gray-300 rounded-lg pl-10 pr-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="Masukkan besaran (misal: 400000)">
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="bg-purple-50 p-6 rounded-lg border border-purple-200">
                        <label class="block text-sm font-semibold text-purple-900 mb-2">SPP Tunggal (Per Semester)</label>
                        <div class="relative max-w-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm">Rp</span>
                            </div>
                            <input wire:model="nominal_spp" type="number" min="0" class="w-full border border-gray-300 rounded-lg pl-10 pr-3 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="Masukkan besaran SPP">
                        </div>
                        <p class="text-xs text-purple-700 mt-2">Untuk jenjang S2 dan S3, tarif ditetapkan menggunakan SPP Tunggal dan bukan kelompok UKT.</p>
                    </div>
                @endif
            </div>
            
            <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-gray-100">
                <button wire:click="$set('showModal', false)" class="px-4 py-2 text-sm text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50 transition">Batal</button>
                <button wire:click="save" class="px-4 py-2 text-sm text-white bg-blue-700 rounded-lg hover:bg-blue-800 transition">Simpan Tarif</button>
            </div>
        </div>
    </div>
    @endif

    {{-- Modal Import Excel --}}
    @if($showImportModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4 p-6" wire:click.stop>
            <div class="flex justify-between items-center mb-5 pb-3 border-b border-gray-100">
                <h2 class="text-lg font-bold text-gray-900">Import Tarif Biaya Kuliah</h2>
                <button wire:click="$set('showImportModal', false)" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <div class="space-y-4">
                <div class="bg-blue-50 text-blue-800 p-3 rounded-lg text-xs">
                    <p class="font-semibold mb-1">Panduan Import:</p>
                    <ul class="list-disc pl-4 space-y-1">
                        <li>Unduh template terlebih dahulu.</li>
                        <li>Isi nominal SPP untuk S2/S3.</li>
                        <li>Isi rentang UKT (1, 2, KIP, dll) untuk S1. Judul kolom UKT akan otomatis terdaftar sebagai Kelompok UKT!</li>
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
                <button wire:click="importTarif" wire:loading.attr="disabled" wire:target="importTarif,importFile" class="px-4 py-2 text-sm text-white bg-green-600 rounded-lg hover:bg-green-700 transition disabled:opacity-50">Mulai Import</button>
            </div>
        </div>
    </div>
    @endif
</div>
