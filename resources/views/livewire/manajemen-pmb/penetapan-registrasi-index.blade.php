<div class="py-6 px-4 max-w-6xl mx-auto" x-data="{ tab: @entangle('tab') }">
    <div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Penetapan Registrasi</h1>
            <p class="text-sm text-gray-500 mt-1">Tetapkan calon mahasiswa yang lulus seleksi menjadi mahasiswa terdaftar.</p>
        </div>
    </div>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="p-3 mb-4 text-sm text-green-700 bg-green-50 border border-green-200 rounded-lg">✅ {{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="p-3 mb-4 text-sm text-red-700 bg-red-50 border border-red-200 rounded-lg">⚠️ {{ session('error') }}</div>
    @endif

    <!-- Tab Header -->
    <div class="mb-5 border-b border-gray-200">
        <ul class="flex flex-wrap -mb-px text-sm font-medium">
            <li class="mr-2">
                <button @click="tab = 'list'"
                    :class="tab === 'list' ? 'text-blue-700 border-b-2 border-blue-700' : 'text-gray-500 hover:text-gray-700'"
                    class="inline-block p-4 rounded-t-lg transition-colors">
                    📋 Daftar Cama Lulus
                </button>
            </li>
            <li class="mr-2">
                <button @click="tab = 'import'"
                    :class="tab === 'import' ? 'text-green-700 border-b-2 border-green-700' : 'text-gray-500 hover:text-gray-700'"
                    class="inline-block p-4 rounded-t-lg transition-colors">
                    ⬆️ Update Status via Excel
                </button>
            </li>
        </ul>
    </div>

    <!-- TAB 1: Daftar Manual -->
    <div x-show="tab === 'list'">
        <!-- Filter -->
        <div class="flex flex-wrap gap-3 mb-4">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nama / nomor pendaftaran..."
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 w-64">
            <select wire:model.live="tahunId" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                <option value="">Semua Tahun</option>
                @foreach($tahunList as $t)
                <option value="{{ $t->id }}">{{ $t->tahun }}</option>
                @endforeach
            </select>
            <select wire:model.live="jalurId" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                <option value="">Semua Jalur</option>
                @foreach($jalurList as $j)
                <option value="{{ $j->id }}">{{ $j->nama_jalur }}</option>
                @endforeach
            </select>
            <select wire:model.live="fakultasId" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                <option value="">Semua Fakultas</option>
                @foreach($fakultasList as $f)
                <option value="{{ $f->id }}">{{ $f->kode_fakultas }}</option>
                @endforeach
            </select>
            <select wire:model.live="prodiId" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                <option value="">Semua Prodi</option>
                @foreach($prodiList as $p)
                <option value="{{ $p->id }}">{{ $p->kode_prodi }} — {{ $p->nama_prodi }}</option>
                @endforeach
            </select>
            <div class="flex items-center gap-2 ml-auto">
                <label class="text-xs text-gray-500">Per halaman:</label>
                <select wire:model.live="perPage" class="border border-gray-300 rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
        </div>

        {{-- Toolbar Bulk Action --}}
        @if(count($selectedIds) > 0)
        <div class="flex items-center gap-3 mb-3 p-3 bg-red-50 border border-red-200 rounded-lg">
            <span class="text-sm font-medium text-red-700">{{ count($selectedIds) }} data dipilih</span>
            <button wire:click="hapusMassal"
                wire:confirm="PERINGATAN: Yakin ingin menghapus {{ count($selectedIds) }} data yang dipilih? Tindakan ini tidak dapat dibatalkan!"
                class="bg-red-600 hover:bg-red-700 text-white text-xs font-medium px-4 py-1.5 rounded-lg transition flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                Hapus Massal
            </button>
            <button wire:click="$set('selectedIds', [])" class="text-xs text-gray-500 hover:text-gray-700 border border-gray-300 px-3 py-1.5 rounded-lg hover:bg-gray-50 transition">
                Batalkan Pilihan
            </button>
        </div>
        @endif

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-600">
                    <thead class="bg-gray-50 border-b border-gray-200 text-xs uppercase text-gray-500">
                        <tr>
                            <th class="py-3 px-3 w-10">
                                <input type="checkbox" wire:model.live="selectAll"
                                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            </th>
                            <th class="py-3 px-3 w-12 text-center">No</th>
                            <th class="py-3 px-4">Nama</th>
                            <th class="py-3 px-4">No. Pendaftaran</th>
                            <th class="py-3 px-4">Prodi</th>
                            <th class="py-3 px-4">Jalur</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($camaList as $cama)
                        <tr class="hover:bg-gray-50 {{ in_array((string)$cama->id, $selectedIds) ? 'bg-red-50' : '' }}">
                            <td class="py-3 px-3">
                                <input type="checkbox" wire:model.live="selectedIds" value="{{ $cama->id }}"
                                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            </td>
                            <td class="py-3 px-3 text-center text-gray-400 text-xs">{{ $camaList->firstItem() + $loop->index }}</td>
                            <td class="py-3 px-4 font-medium text-gray-900">{{ ucwords(strtolower($cama->nama)) }}</td>
                            <td class="py-3 px-4 font-mono text-xs">{{ $cama->nomor_pendaftaran ?? '-' }}</td>
                            <td class="py-3 px-4 text-xs">
                                <span class="font-medium">{{ $cama->programStudi?->kode_prodi }}</span>
                                <p class="text-gray-400">{{ $cama->programStudi?->nama_prodi }}</p>
                            </td>
                            <td class="py-3 px-4 text-xs">{{ $cama->jalurSeleksi?->nama_jalur ?? '-' }}</td>
                            <td class="py-3 px-4 text-center">
                                @if($cama->mahasiswa)
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-700">✅ Registrasi</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700">☑️ Lulus</span>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                <div class="flex items-center justify-end gap-1.5">
                                    @if(!$cama->mahasiswa)
                                    <button wire:click="tetapkanRegistrasi({{ $cama->id }})"
                                        wire:confirm="Tetapkan {{ ucwords(strtolower($cama->nama)) }} sebagai Registrasi?"
                                        class="text-xs bg-blue-600 hover:bg-blue-700 text-white font-medium py-1.5 px-3 rounded transition">
                                        Set Reg
                                    </button>
                                    @else
                                    <button wire:click="batalkanRegistrasi({{ $cama->id }})"
                                        wire:confirm="Batalkan registrasi {{ ucwords(strtolower($cama->nama)) }}?"
                                        class="text-xs border border-orange-200 hover:border-orange-400 text-orange-600 hover:text-orange-800 py-1.5 px-3 rounded transition">
                                        Batal Reg
                                    </button>
                                    @endif
                                    <button wire:click="hapusCama({{ $cama->id }})"
                                        wire:confirm="Hapus data {{ ucwords(strtolower($cama->nama)) }} secara permanen?"
                                        class="text-xs border border-red-200 hover:border-red-400 text-red-600 hover:text-red-800 py-1.5 px-2 rounded transition">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="py-10 text-center text-gray-400">
                                <p class="text-4xl mb-2">📭</p>
                                <p class="text-sm">Tidak ada data ditemukan.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-gray-100 flex items-center justify-between">
                <p class="text-xs text-gray-400">
                    Menampilkan {{ $camaList->firstItem() ?? 0 }}–{{ $camaList->lastItem() ?? 0 }}
                    dari {{ number_format($camaList->total()) }} data
                </p>
                {{ $camaList->links() }}
            </div>
        </div>
    </div>

    <!-- TAB 2: Import via Excel -->
    <div x-show="tab === 'import'" x-cloak>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 max-w-2xl">
            <h2 class="text-lg font-bold text-gray-900 mb-1">Update Status via Excel (Bulk)</h2>
            <p class="text-sm text-gray-500 mb-5">Upload file Excel untuk memperbarui status banyak peserta sekaligus.</p>

            {{-- Step 1: Download Template --}}
            <div class="mb-6 p-4 bg-blue-50 border border-blue-100 rounded-lg flex items-start gap-4">
                <div class="text-3xl">📥</div>
                <div class="flex-1">
                    <p class="text-sm font-semibold text-blue-800 mb-1">Langkah 1: Download Template</p>
                    <p class="text-xs text-blue-600 mb-3">Download template Excel, isi nomor pendaftaran dan status, lalu upload kembali di bawah.</p>
                    <button wire:click="downloadTemplate"
                        class="inline-flex items-center gap-2 bg-blue-700 hover:bg-blue-800 text-white text-sm font-medium px-4 py-2 rounded-lg transition shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        Download Template (.xlsx)
                    </button>
                </div>
            </div>

            {{-- Step 2: Upload & Import --}}
            <div class="p-4 bg-gray-50 border border-gray-200 rounded-lg">
                <p class="text-sm font-semibold text-gray-800 mb-3">📤 Langkah 2: Upload File yang Sudah Diisi</p>
                <form wire:submit="importStatus" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Pilih File Excel / CSV</label>
                        <input type="file" wire:model="file" accept=".xlsx,.xls,.csv"
                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100 border border-gray-300 rounded-lg p-1">
                        @error('file') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <button type="submit" wire:loading.attr="disabled"
                        class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-6 rounded-lg shadow-sm transition flex items-center gap-2">
                        <span wire:loading.remove wire:target="importStatus">
                            <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                            Upload & Proses
                        </span>
                        <span wire:loading wire:target="importStatus">⏳ Memproses data...</span>
                    </button>
                </form>
            </div>

            {{-- Info Format --}}
            <div class="mt-5 pt-4 border-t border-gray-100">
                <h3 class="text-xs font-bold text-gray-600 mb-2 uppercase tracking-wide">Format Kolom dalam Template:</h3>
                <table class="w-full text-xs text-gray-600 border border-gray-200 rounded-lg overflow-hidden">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-3 py-2 text-left">Kolom</th>
                            <th class="px-3 py-2 text-left">Wajib?</th>
                            <th class="px-3 py-2 text-left">Nilai / Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr><td class="px-3 py-2 font-mono">nomor_pendaftaran</td><td class="px-3 py-2 text-red-600">Wajib</td><td class="px-3 py-2">Nomor tes / pendaftaran peserta</td></tr>
                        <tr class="bg-gray-50"><td class="px-3 py-2 font-mono">status</td><td class="px-3 py-2 text-red-600">Wajib</td><td class="px-3 py-2"><code>registrasi</code> / <code>alumni</code> / <code>stop_out</code> / <code>drop_out</code></td></tr>
                        <tr><td class="px-3 py-2 font-mono">nim</td><td class="px-3 py-2 text-gray-400">Opsional</td><td class="px-3 py-2">Nomor Induk Mahasiswa</td></tr>
                        <tr class="bg-gray-50"><td class="px-3 py-2 font-mono">status_akademik</td><td class="px-3 py-2 text-gray-400">Opsional</td><td class="px-3 py-2">Aktif / Lulus / Stop Out / Drop Out</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
