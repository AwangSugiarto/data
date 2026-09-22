<div class="py-6 px-4 max-w-6xl mx-auto" x-data="{ tab: @entangle('tab') }">

    {{-- Header --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Entri Data Kelulusan PMB</h1>
            <p class="text-sm text-gray-500 mt-1">Input atau import data calon mahasiswa yang lulus seleksi.</p>
        </div>
    </div>

    {{-- Flash --}}
    @if(session('success'))
        <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">✅ {{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm">⚠️ {{ session('error') }}</div>
    @endif

    {{-- Tab Nav --}}
    <div class="mb-5 border-b border-gray-200">
        <ul class="flex flex-wrap -mb-px text-sm font-medium">
            <li class="mr-2">
                <button @click="tab = 'manual'"
                    :class="tab === 'manual' ? 'text-blue-700 border-b-2 border-blue-700' : 'text-gray-500 hover:text-gray-700'"
                    class="inline-flex items-center gap-1.5 p-4 rounded-t-lg transition-colors">
                    👤 Entri Manual (Satu per Satu)
                </button>
            </li>
            <li class="mr-2">
                <button @click="tab = 'import'"
                    :class="tab === 'import' ? 'text-green-700 border-b-2 border-green-700' : 'text-gray-500 hover:text-gray-700'"
                    class="inline-flex items-center gap-1.5 p-4 rounded-t-lg transition-colors">
                    📥 Import Excel (Massal)
                </button>
            </li>
            <li class="mr-2">
                <button @click="tab = 'riwayat'"
                    :class="tab === 'riwayat' ? 'text-purple-700 border-b-2 border-purple-700' : 'text-gray-500 hover:text-gray-700'"
                    class="inline-flex items-center gap-1.5 p-4 rounded-t-lg transition-colors">
                    📋 Riwayat Data
                </button>
            </li>
        </ul>
    </div>

    {{-- ══════════════════ TAB 1: ENTRI MANUAL ══════════════════ --}}
    <div x-show="tab === 'manual'" x-cloak>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-base font-semibold text-gray-800 mb-5">
                {{ $editId ? '✏️ Edit Data Calon Mahasiswa' : '➕ Tambah Data Calon Mahasiswa Lulus' }}
            </h2>

            <form wire:submit="saveManual" class="space-y-6">
                {{-- Baris 1: Tahun & Jalur --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tahun Akademik <span class="text-red-500">*</span></label>
                        <select wire:model="f_tahun_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <option value="">-- Pilih Tahun --</option>
                            @foreach($tahunList as $t)
                            <option value="{{ $t->id }}">{{ $t->tahun }}</option>
                            @endforeach
                        </select>
                        @error('f_tahun_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Jalur Seleksi <span class="text-red-500">*</span></label>
                        <select wire:model="f_jalur_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <option value="">-- Pilih Jalur --</option>
                            @foreach($jalurList as $j)
                            <option value="{{ $j->id }}">{{ $j->nama_jalur }}</option>
                            @endforeach
                        </select>
                        @error('f_jalur_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Baris 2: Fakultas & Prodi --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fakultas <span class="text-red-500">*</span></label>
                        <select wire:model.live="f_fakultas_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <option value="">-- Pilih Fakultas --</option>
                            @foreach($fakultasList as $f)
                            <option value="{{ $f->id }}">{{ $f->kode_fakultas }} — {{ $f->nama_fakultas }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Program Studi <span class="text-red-500">*</span></label>
                        <select wire:model="f_prodi_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" @disabled(!$f_fakultas_id)>
                            <option value="">-- Pilih Prodi --</option>
                            @foreach($prodiList as $p)
                            <option value="{{ $p->id }}">{{ $p->kode_prodi }} — {{ $p->nama_prodi }}</option>
                            @endforeach
                        </select>
                        @error('f_prodi_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Baris 3: Nomor & Nama --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Pendaftaran / Tes <span class="text-red-500">*</span></label>
                        <input wire:model="f_nomor" type="text" placeholder="Contoh: 2605010001"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none font-mono">
                        @error('f_nomor') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input wire:model="f_nama" type="text" placeholder="Nama lengkap sesuai ijazah"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        @error('f_nama') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Baris 4: Wilayah & UKT --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Wilayah / Kabupaten Kota</label>
                        <select wire:model="f_wilayah_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <option value="">-- Pilih Wilayah (Opsional) --</option>
                            @foreach($wilayahList as $w)
                            <option value="{{ $w->id }}">{{ $w->kabupaten_kota }}, {{ $w->provinsi }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kelompok UKT</label>
                        <select wire:model="f_ukt_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <option value="">-- Pilih UKT (Opsional) --</option>
                            @foreach($uktList as $u)
                            <option value="{{ $u->id }}">Kelompok {{ $u->kelompok }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Status Lulus --}}
                <div class="flex items-center gap-3 p-3 bg-amber-50 border border-amber-100 rounded-lg">
                    <input type="checkbox" wire:model="f_is_lulus" id="f_is_lulus"
                        class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                    <label for="f_is_lulus" class="text-sm font-medium text-gray-700">
                        Tandai sebagai <strong>Lulus Seleksi</strong>
                        <span class="text-gray-400 font-normal">(hilangkan centang jika hanya data pendaftar)</span>
                    </label>
                </div>

                {{-- Tombol --}}
                <div class="flex gap-3 pt-2">
                    <button type="submit" wire:loading.attr="disabled"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-2 rounded-lg transition text-sm">
                        <span wire:loading.remove wire:target="saveManual">{{ $editId ? '💾 Update Data' : '✅ Simpan Data' }}</span>
                        <span wire:loading wire:target="saveManual">⏳ Menyimpan...</span>
                    </button>
                    @if($editId)
                    <button type="button" wire:click="$set('editId', null); $set('tab', 'riwayat')"
                        class="border border-gray-300 text-gray-600 hover:bg-gray-50 px-4 py-2 rounded-lg text-sm transition">
                        Batal Edit
                    </button>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- ══════════════════ TAB 2: IMPORT EXCEL ══════════════════ --}}
    <div x-show="tab === 'import'" x-cloak>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 max-w-2xl">
            <h2 class="text-base font-semibold text-gray-800 mb-1">Import Data Kelulusan via Excel</h2>
            <p class="text-sm text-gray-500 mb-5">Upload file Excel berisi daftar peserta yang lulus seleksi.</p>

            {{-- Step 1: Download Template --}}
            <div class="mb-6 p-4 bg-blue-50 border border-blue-100 rounded-lg flex items-start gap-4">
                <div class="text-3xl">📥</div>
                <div>
                    <p class="text-sm font-semibold text-blue-800 mb-1">Langkah 1: Download Template Excel</p>
                    <p class="text-xs text-blue-600 mb-3">Template berisi contoh format dan panduan pengisian kolom.</p>
                    <button wire:click="downloadTemplate"
                        class="inline-flex items-center gap-2 bg-blue-700 hover:bg-blue-800 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        Download Template (.xlsx)
                    </button>
                </div>
            </div>

            {{-- Step 2: Pilih Tahun & Jalur --}}
            <div class="mb-5 p-4 bg-gray-50 border border-gray-200 rounded-lg space-y-4">
                <p class="text-sm font-semibold text-gray-700">Langkah 2: Tentukan Tahun & Jalur (Default)</p>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Tahun Akademik <span class="text-red-500">*</span></label>
                        <select wire:model="i_tahun_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <option value="">-- Pilih Tahun --</option>
                            @foreach($tahunList as $t)
                            <option value="{{ $t->id }}">{{ $t->tahun }}</option>
                            @endforeach
                        </select>
                        @error('i_tahun_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Jalur Seleksi
                            <span class="text-gray-400 font-normal">(bisa diisi di Excel)</span>
                        </label>
                        <select wire:model="i_jalur_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <option value="">-- Tidak ditentukan --</option>
                            @foreach($jalurList as $j)
                            <option value="{{ $j->id }}">{{ $j->nama_jalur }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <p class="text-xs text-amber-600">
                    ⚠️ Jika jalur dipilih di sini, semua baris dalam Excel akan menggunakan jalur tersebut (kecuali Excel punya kolom <code>jalur_seleksi</code>).
                </p>
            </div>

            {{-- Step 3: Upload --}}
            <div class="p-4 bg-gray-50 border border-gray-200 rounded-lg">
                <p class="text-sm font-semibold text-gray-700 mb-3">Langkah 3: Upload File</p>
                <form wire:submit="importExcel" class="space-y-4">
                    <div>
                        <input type="file" wire:model="i_file" accept=".xlsx,.xls,.csv"
                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100 border border-gray-300 rounded-lg p-1">
                        @error('i_file') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <button type="submit" wire:loading.attr="disabled"
                        class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-6 rounded-lg transition text-sm flex items-center gap-2">
                        <span wire:loading.remove wire:target="importExcel">⬆️ Upload & Proses Import</span>
                        <span wire:loading wire:target="importExcel">⏳ Memproses data...</span>
                    </button>
                </form>
            </div>

            {{-- Info: Apa yang terjadi saat import --}}
            <div class="mt-4 p-3 bg-yellow-50 border border-yellow-100 rounded-lg text-xs text-yellow-700">
                <strong>Catatan:</strong> Import hanya menyimpan data calon mahasiswa dengan status <strong>Lulus Seleksi</strong>.
                Penetapan Registrasi (menjadi Mahasiswa Aktif) dilakukan terpisah di menu
                <a href="{{ route('manajemen-pmb.penetapan-registrasi') }}" class="underline font-medium">Penetapan Registrasi</a>.
            </div>
        </div>
    </div>

    {{-- ══════════════════ TAB 3: RIWAYAT DATA ══════════════════ --}}
    <div x-show="tab === 'riwayat'" x-cloak>

        {{-- Filter --}}
        <div class="flex flex-wrap gap-3 mb-4">
            <input wire:model.live.debounce.300ms="fSearch" type="text" placeholder="Cari nama / nomor..."
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none w-60">
            <select wire:model.live="fTahunId" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                <option value="">Semua Tahun</option>
                @foreach($tahunList as $t)
                <option value="{{ $t->id }}">{{ $t->tahun }}</option>
                @endforeach
            </select>
            <select wire:model.live="fJalurId" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                <option value="">Semua Jalur</option>
                @foreach($jalurList as $j)
                <option value="{{ $j->id }}">{{ $j->nama_jalur }}</option>
                @endforeach
            </select>
            <select wire:model.live="fFakultasId" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                <option value="">Semua Fakultas</option>
                @foreach($fakultasList as $f)
                <option value="{{ $f->id }}">{{ $f->kode_fakultas }}</option>
                @endforeach
            </select>
            <select wire:model.live="fProdiId" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                <option value="">Semua Prodi</option>
                @foreach($prodiFilterList as $p)
                <option value="{{ $p->id }}">{{ $p->kode_prodi }} — {{ $p->nama_prodi }}</option>
                @endforeach
            </select>
            <div class="flex items-center gap-2 ml-auto">
                <label class="text-xs text-gray-400">Per halaman:</label>
                <select wire:model.live="perPage" class="border border-gray-300 rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <option value="15">15</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </select>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-gray-600">
                    <thead class="bg-gray-50 border-b border-gray-200 text-xs uppercase text-gray-500">
                        <tr>
                            <th class="px-4 py-3 text-center w-12">No</th>
                            <th class="px-4 py-3 text-left">Nama</th>
                            <th class="px-4 py-3 text-left">No. Pendaftaran</th>
                            <th class="px-4 py-3 text-left">Prodi</th>
                            <th class="px-4 py-3 text-left">Jalur</th>
                            <th class="px-4 py-3 text-center">Lulus</th>
                            <th class="px-4 py-3 text-center">Registrasi</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($riwayat as $c)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-3 text-center text-xs text-gray-400">{{ $riwayat->firstItem() + $loop->index }}</td>
                            <td class="px-4 py-3 font-medium text-gray-900">{{ ucwords(strtolower($c->nama)) }}</td>
                            <td class="px-4 py-3 font-mono text-xs">{{ $c->nomor_pendaftaran }}</td>
                            <td class="px-4 py-3 text-xs">
                                <span class="font-medium">{{ $c->programStudi?->kode_prodi ?? '-' }}</span>
                                <p class="text-gray-400">{{ $c->programStudi?->nama_prodi ?? '' }}</p>
                            </td>
                            <td class="px-4 py-3 text-xs">{{ $c->jalurSeleksi?->nama_jalur ?? '—' }}</td>
                            <td class="px-4 py-3 text-center">
                                @if($c->is_lulus)
                                    <span class="px-2 py-0.5 text-xs rounded-full bg-green-100 text-green-700">✅ Lulus</span>
                                @else
                                    <span class="px-2 py-0.5 text-xs rounded-full bg-gray-100 text-gray-500">Daftar</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($c->mahasiswa)
                                    <span class="px-2 py-0.5 text-xs rounded-full bg-purple-100 text-purple-700">✅ Ya</span>
                                @else
                                    <span class="text-gray-300 text-xs">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button wire:click="editCama({{ $c->id }})"
                                        class="text-xs border border-blue-200 text-blue-600 hover:border-blue-400 hover:text-blue-800 px-2.5 py-1 rounded transition">
                                        ✏️ Edit
                                    </button>
                                    <button wire:click="deleteCama({{ $c->id }})"
                                        wire:confirm="Hapus data {{ $c->nama }} secara permanen?"
                                        class="text-xs border border-red-200 text-red-600 hover:border-red-400 hover:text-red-800 px-2 py-1 rounded transition">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-gray-400">
                                <p class="text-4xl mb-2">📭</p>
                                <p class="text-sm">Belum ada data. Gunakan tab Entri Manual atau Import Excel.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-gray-100 flex items-center justify-between">
                <p class="text-xs text-gray-400">
                    Menampilkan {{ $riwayat->firstItem() ?? 0 }}–{{ $riwayat->lastItem() ?? 0 }}
                    dari {{ number_format($riwayat->total()) }} data
                </p>
                {{ $riwayat->links() }}
            </div>
        </div>
    </div>

</div>
