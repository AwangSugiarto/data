<div class="py-6 px-4 max-w-6xl mx-auto">

    {{-- ── Notifikasi ─────────────────────────────────────────────────── --}}
    @if(session('success'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3500)"
         x-transition class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm flex items-center gap-2">
        ✅ {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
         x-transition class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm flex items-center gap-2">
        ❌ {{ session('error') }}
    </div>
    @endif

    {{-- ── Header ─────────────────────────────────────────────────────── --}}
    <div class="flex flex-wrap items-center justify-between gap-4 mb-5">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Data Individu Mahasiswa</h1>
            <p class="text-sm text-gray-500 mt-0.5">Rincian data peserta PMB per individu</p>
        </div>
        @if(!auth()->user()->hasRole('fakultas'))
        <button wire:click="create"
            class="bg-blue-700 hover:bg-blue-800 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2 transition shadow-sm">
            ➕ Tambah Data
        </button>
        @endif
    </div>

    {{-- ── Stat Mini Cards ─────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-5">
        {{-- Total Cama Lulus Seleksi --}}
        <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
            <div class="flex items-center gap-2 mb-1">
                <span class="w-2.5 h-2.5 rounded-full bg-green-400 flex-shrink-0"></span>
                <p class="text-xs text-gray-500">Total Cama (Lulus Seleksi)</p>
            </div>
            <p class="text-xl font-bold text-gray-800">{{ number_format($cntCama) }}</p>
            <p class="text-xs text-gray-400 mt-0.5">Data individu di sistem</p>
        </div>

        {{-- Pendaftar dari Agregat --}}
        <div class="bg-white rounded-xl border border-blue-100 p-4 shadow-sm">
            <div class="flex items-center gap-2 mb-1">
                <span class="w-2.5 h-2.5 rounded-full bg-blue-400 flex-shrink-0"></span>
                <p class="text-xs text-gray-500">Total Pendaftar</p>
            </div>
            <p class="text-xl font-bold text-blue-700">{{ number_format($cntPendaftar) }}</p>
            <p class="text-xs text-gray-400 mt-0.5">Dari tabel agregat peminat</p>
        </div>

        {{-- Lulus + Registrasi --}}
        <div class="bg-white rounded-xl border border-purple-100 p-4 shadow-sm">
            <div class="flex items-center gap-2 mb-1">
                <span class="w-2.5 h-2.5 rounded-full bg-purple-400 flex-shrink-0"></span>
                <p class="text-xs text-gray-500">Lulus + Registrasi</p>
            </div>
            <p class="text-xl font-bold text-purple-700">{{ number_format($cntLulusReg) }}</p>
            <p class="text-xs text-gray-400 mt-0.5">Sudah daftar ulang (Mhs Aktif)</p>
        </div>

        {{-- Lulus Tidak Registrasi --}}
        <div class="bg-white rounded-xl border border-orange-100 p-4 shadow-sm">
            <div class="flex items-center gap-2 mb-1">
                <span class="w-2.5 h-2.5 rounded-full bg-orange-400 flex-shrink-0"></span>
                <p class="text-xs text-gray-500">Lulus Tidak Registrasi</p>
            </div>
            <p class="text-xl font-bold text-orange-600">{{ number_format($cntLulusTidakReg) }}</p>
            <p class="text-xs text-gray-400 mt-0.5">Lulus namun belum/tidak daftar ulang</p>
        </div>
    </div>

    {{-- ── Filter Bar ──────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 mb-4">
        <div class="flex flex-wrap gap-3 items-end">
            {{-- Search --}}
            <div class="flex-1 min-w-48">
                <label class="block text-xs font-medium text-gray-600 mb-1">Cari Nama / No. Tes</label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">🔍</span>
                    <input wire:model.live.debounce.400ms="search" type="text" placeholder="Cari..."
                        class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
            </div>

            {{-- Tahun --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Tahun</label>
                <select wire:model.live="tahunId" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    @foreach($tahunList as $t)
                    <option value="{{ $t->id }}">{{ $t->tahun }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Fakultas --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Fakultas</label>
                <select wire:model.live="fakultasId" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <option value="">Semua Fakultas</option>
                    @foreach($fakultasList as $f)
                    <option value="{{ $f->id }}">{{ $f->kode_fakultas }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Prodi --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Prodi</label>
                <select wire:model.live="prodiId" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <option value="">Semua Prodi</option>
                    @foreach($prodiList as $p)
                    <option value="{{ $p->id }}">{{ $p->kode_prodi }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Jalur --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Jalur</label>
                <select wire:model.live="jalurId" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <option value="">Semua Jalur</option>
                    @foreach($jalurList as $j)
                    <option value="{{ $j->id }}">{{ $j->nama_jalur }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Status --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                <select wire:model.live="status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    @foreach($statusOptions as $val => $label)
                    <option value="{{ $val }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Per Page --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Per halaman</label>
                <select wire:model.live="perPage" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button wire:click="resetFilters"
                    class="text-xs text-gray-500 hover:text-gray-700 border border-gray-200 px-3 py-2 rounded-lg hover:bg-gray-50 transition">
                    ✕ Reset
                </button>
                <button wire:click="deleteFiltered" wire:confirm="PERINGATAN: Yakin ingin menghapus seluruh data yang sedang tampil/sesuai filter ini secara massal?"
                    class="text-xs text-red-500 hover:text-red-700 border border-red-200 px-3 py-2 rounded-lg hover:bg-red-50 transition flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Hapus Filter
                </button>
            </div>
        </div>
    </div>

    {{-- ── Tabel ───────────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        @php
                        $th = 'px-3 py-2 text-left font-semibold text-gray-600 text-[11px] uppercase tracking-wide cursor-pointer hover:bg-gray-100 select-none transition';
                        $icon = fn($col) => $sortColumn === $col ? ($sortDirection === 'asc' ? ' ↑' : ' ↓') : ' ↕';
                        @endphp
                        <th class="px-3 py-2 text-left font-semibold text-gray-600 text-[11px] uppercase tracking-wide w-10">#</th>
                        <th wire:click="sortBy('nomor_pendaftaran')" class="{{ $th }}">No. Tes{{ $icon('nomor_pendaftaran') }}</th>
                        <th wire:click="sortBy('nama')" class="{{ $th }}">Nama{{ $icon('nama') }}</th>
                        <th wire:click="sortBy('program_studi_id')" class="{{ $th }}">Prodi{{ $icon('program_studi_id') }}</th>
                        <th wire:click="sortBy('jalur_seleksi_id')" class="{{ $th }}">Jalur{{ $icon('jalur_seleksi_id') }}</th>
                        <th wire:click="sortBy('wilayah_id')" class="{{ $th }}">Wilayah{{ $icon('wilayah_id') }}</th>
                        <th wire:click="sortBy('kelompok_ukt_id')" class="{{ $th }}">UKT{{ $icon('kelompok_ukt_id') }}</th>
                        <th class="px-3 py-2 text-center font-semibold text-gray-600 text-[11px] uppercase tracking-wide">Status</th>
                        @if(!auth()->user()->hasRole('fakultas'))
                        <th class="px-3 py-2 text-center font-semibold text-gray-600 text-[11px] uppercase tracking-wide">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($rows as $row)
                    @php
                    $statusCode  = $row->status_code;
                    $statusLabel = $row->status_label;
                    $badgeClass = match($statusCode) {
                        'alumni'            => 'bg-indigo-100 text-indigo-700',
                        'registrasi'        => 'bg-purple-100 text-purple-700',
                        'lulus'             => 'bg-green-100 text-green-700',
                        'stop_out'          => 'bg-yellow-100 text-yellow-700',
                        'drop_out'          => 'bg-red-100 text-red-700',
                        'mengundurkan_diri' => 'bg-orange-100 text-orange-700',
                        'meninggal'         => 'bg-gray-100 text-gray-600',
                        default             => 'bg-blue-100 text-blue-700',
                    };
                    $statusIcon = match($statusCode) {
                        'alumni'     => '🎓',
                        'registrasi' => '✅',
                        'lulus'      => '☑️',
                        'stop_out'   => '⏸️',
                        'drop_out'   => '❌',
                        default      => '📝',
                    };
                    @endphp
                    <tr class="hover:bg-gray-50 transition border-b border-gray-100 last:border-0">
                        <td class="px-3 py-2 text-gray-400 text-xs">{{ $rows->firstItem() + $loop->index }}</td>
                        <td class="px-3 py-2 font-mono text-xs text-gray-600">{{ $row->nomor_pendaftaran ?? '—' }}</td>
                        <td class="px-3 py-2 font-medium text-gray-900 max-w-xs truncate text-[13px]">{{ ucwords(strtolower($row->nama ?? '—')) }}</td>
                        <td class="px-3 py-2">
                            <span class="text-gray-800 text-[11px] font-medium leading-none">{{ $row->programStudi?->kode_prodi }}</span>
                            <p class="text-gray-400 text-[11px] truncate max-w-[140px] leading-tight">{{ $row->programStudi?->nama_prodi }}</p>
                        </td>
                        <td class="px-3 py-2 text-[11px] text-gray-600">{{ $row->jalurSeleksi?->nama_jalur ?? '—' }}</td>
                        <td class="px-3 py-2 text-[11px] text-gray-500">{{ $row->wilayah ? $row->wilayah->kabupaten_kota . ', ' . $row->wilayah->provinsi : '—' }}</td>
                        <td class="px-3 py-2 text-[11px] text-gray-500">
                            {{ $row->kelompokUkt ? 'UKT ' . $row->kelompokUkt->kelompok : '—' }}
                        </td>
                        <td class="px-3 py-2 text-center">
                            <span class="px-2 py-0.5 rounded text-[10px] font-medium {{ $badgeClass }}">{{ $statusIcon }} {{ $statusLabel }}</span>
                        </td>
                        @if(!auth()->user()->hasRole('fakultas'))
                        <td class="px-3 py-2 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <button wire:click="edit({{ $row->id }})"
                                    class="text-[11px] text-blue-600 hover:text-blue-800 hover:bg-blue-50 px-1.5 py-0.5 rounded transition">
                                    ✏️ Edit
                                </button>
                                <button wire:click="confirmDelete({{ $row->id }})"
                                    class="text-[11px] text-red-500 hover:text-red-700 hover:bg-red-50 px-1.5 py-0.5 rounded transition">
                                    🗑️
                                </button>
                            </div>
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-4 py-12 text-center text-gray-400">
                            <div class="flex flex-col items-center gap-2">
                                <span class="text-4xl">📭</span>
                                <span class="text-sm">Tidak ada data ditemukan</span>
                                <span class="text-xs text-gray-300">Coba ubah filter atau tambah data baru</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="px-4 py-3 border-t border-gray-100 flex items-center justify-between">
            <p class="text-xs text-gray-400">
                Menampilkan {{ $rows->firstItem() ?? 0 }}–{{ $rows->lastItem() ?? 0 }} dari {{ number_format($rows->total()) }} data
            </p>
            {{ $rows->links() }}
        </div>
    </div>

    {{-- ════════ MODAL FORM CRUD ════════ --}}
    @if($showModal)
    <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4" wire:click.self="closeModal">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto" wire:key="modal-{{ $editId }}">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h2 class="text-lg font-bold text-gray-900">
                    {{ $editId ? '✏️ Edit Data Individu' : '➕ Tambah Data Individu' }}
                </h2>
                <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600 text-xl leading-none">×</button>
            </div>

            <form wire:submit="save" class="px-6 py-5 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                        {{-- No. Pendaftaran --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">No. Pendaftaran / No. Tes</label>
                        <input wire:model="f_nomor_pendaftaran" type="text" placeholder="Opsional"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none @error('f_nomor_pendaftaran') border-red-400 @enderror">
                        @error('f_nomor_pendaftaran') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Nama --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Nama</label>
                        <input wire:model="f_nama" type="text" placeholder="Nama peserta"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        @error('f_nama') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- ── Tahun Akademik ───────────────────────────────────────────────── --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Tahun Akademik <span class="text-red-500">*</span></label>
                        <div x-data="ss({ wp:'f_tahun_id', ph:'Pilih tahun...', val:'{{ $f_tahun_id }}',
                            opts: @js($tahunList->map(fn($t)=>['v'=>$t->id,'l'=>(string)$t->tahun])->values()) })"
                            @click.outside="cls()" class="relative">
                            @include('components.ss-trigger')
                            @include('components.ss-dropdown')
                        </div>
                        @error('f_tahun_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- ── Program Studi ────────────────────────────────────────────────── --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Program Studi <span class="text-red-500">*</span></label>
                        <div x-data="ss({ wp:'f_prodi_id', ph:'Pilih program studi...', val:'{{ $f_prodi_id }}',
                            opts: @js($prodiFormList->map(fn($p)=>['v'=>$p->id,'l'=>$p->kode_prodi.' — '.$p->nama_prodi])->values()) })"
                            @click.outside="cls()" class="relative">
                            @include('components.ss-trigger')
                            @include('components.ss-dropdown')
                        </div>
                        @error('f_prodi_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- ── Jalur Seleksi ────────────────────────────────────────────────── --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Jalur Seleksi</label>
                        <div x-data="ss({ wp:'f_jalur_id', ph:'-- Semua Jalur --', val:'{{ $f_jalur_id }}',
                            opts: @js($jalurList->map(fn($j)=>['v'=>$j->id,'l'=>$j->nama_jalur])->values()) })"
                            @click.outside="cls()" class="relative">
                            @include('components.ss-trigger')
                            @include('components.ss-dropdown')
                        </div>
                        @error('f_jalur_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- ── Status Lulus ──────────────────────────────────────────────────── --}}
                    <div class="flex items-center gap-2 pt-2">
                        <input wire:model="f_is_lulus" type="checkbox" id="f_is_lulus" class="rounded border-gray-300">
                        <label for="f_is_lulus" class="text-sm font-medium text-gray-700">Lulus Seleksi</label>
                    </div>

                    {{-- ── NIM ──────────────────────────────────────────────────────────── --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">NIM <span class="text-gray-400 font-normal">(opsional, isi jika sudah Registrasi)</span></label>
                        <input wire:model="f_nim" type="text" placeholder="Nomor Induk Mahasiswa"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>

                    {{-- ── Status Akademik ──────────────────────────────────────────────── --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Status Akademik</label>
                        <div x-data="ss({ wp:'f_status_akademik', ph:'-- Tidak Ada / Masih Daftar/Lulus --', val:'{{ $f_status_akademik }}', clr:false,
                            opts: @js(collect($statusAkademikOptions)->map(fn($l,$v)=>['v'=>$v,'l'=>$l])->values()) })"
                            @click.outside="cls()" class="relative">
                            @include('components.ss-trigger')
                            @include('components.ss-dropdown')
                        </div>
                        <p class="text-xs text-gray-400 mt-1">Isi ini jika mahasiswa sudah melakukan registrasi atau memiliki status khusus (SO/DO/Alumni).</p>
                    </div>

                    {{-- ── Wilayah Asal ──────────────────────────────────────────────────── --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Wilayah Asal <span class="text-gray-400 font-normal">(opsional)</span></label>
                        <div x-data="ss({ wp:'f_wilayah_id', ph:'Pilih wilayah...', val:'{{ $f_wilayah_id }}',
                            opts: @js($wilayahList->map(fn($w)=>['v'=>$w->id,'l'=>$w->kabupaten_kota.', '.$w->provinsi])->values()) })"
                            @click.outside="cls()" class="relative">
                            @include('components.ss-trigger')
                            @include('components.ss-dropdown')
                        </div>
                    </div>

                    {{-- ── Negara Asal ──────────────────────────────────────────────────── --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Negara Asal <span class="text-gray-400 font-normal">(opsional, isi jika mahasiswa asing)</span></label>
                        <div x-data="ss({ wp:'f_warganegara_id', ph:'🌏 Pilih negara (WNI kosongkan)...', val:'{{ $f_warganegara_id }}',
                            opts: @js($negaraList->map(fn($n)=>['v'=>$n->id,'l'=>($n->kode_negara?'['.$n->kode_negara.'] ':'').$n->nama_negara])->values()) })"
                            @click.outside="cls()" class="relative">
                            @include('components.ss-trigger')
                            @include('components.ss-dropdown')
                        </div>
                        <p class="text-xs text-gray-400 mt-1">Mahasiswa asing akan tampil di grafis Dashboard Akademik.</p>
                    </div>

                    {{-- ── Kelompok UKT ──────────────────────────────────────────────────── --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Kelompok UKT <span class="text-gray-400 font-normal">(opsional)</span></label>
                        <div x-data="ss({ wp:'f_kelompok_ukt_id', ph:'Pilih kelompok UKT...', val:'{{ $f_kelompok_ukt_id }}',
                            opts: @js($uktList->map(fn($u)=>['v'=>$u->id,'l'=>'UKT '.$u->kelompok.' ('.number_format($u->nominal_min).($u->nominal_max?' – '.number_format($u->nominal_max):'+').')'])->values()) })"
                            @click.outside="cls()" class="relative">
                            @include('components.ss-trigger')
                            @include('components.ss-dropdown')
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-2 border-t border-gray-100">
                    <button type="button" wire:click="closeModal"
                        class="px-4 py-2 text-sm border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50 transition">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-5 py-2 text-sm bg-blue-700 hover:bg-blue-800 text-white rounded-lg font-medium transition shadow-sm">
                        <span wire:loading.remove wire:target="save">💾 Simpan</span>
                        <span wire:loading wire:target="save">⏳ Menyimpan...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- ════════ MODAL KONFIRMASI DELETE ════════ --}}
    @if($showConfirm)
    <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6 text-center">
            <div class="text-5xl mb-3">🗑️</div>
            <h3 class="text-lg font-bold text-gray-900 mb-2">Hapus Data?</h3>
            <p class="text-sm text-gray-500 mb-5">Data yang dihapus tidak bisa dikembalikan.</p>
            <div class="flex gap-3 justify-center">
                <button wire:click="closeModal"
                    class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-600 hover:bg-gray-50 transition">
                    Batal
                </button>
                <button wire:click="delete"
                    class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-medium transition">
                    <span wire:loading.remove wire:target="delete">Ya, Hapus</span>
                    <span wire:loading wire:target="delete">Menghapus...</span>
                </button>
            </div>
        </div>
    </div>
    @endif

</div>

@push('head-scripts')
<script>
/* ── Searchable Select (ss) ────────────────────────────────────────────────
   Dropdown pakai position:fixed agar tidak terpotong oleh overflow modal.
   Posisi dihitung dari getBoundingClientRect() tombol trigger.
*/
window.ss = function(cfg) {
    cfg = cfg || {};
    return {
        open:    false,
        openUp:  false,
        search:  '',
        sel:     (cfg.val !== null && cfg.val !== undefined) ? String(cfg.val) : '',
        opts:    cfg.opts || [],
        ph:      cfg.ph   || 'Pilih...',
        wp:      cfg.wp   || '',
        clr:     cfg.clr  !== false,
        /* posisi dropdown (px) */
        _top:  0, _left: 0, _width: 0,

        get lbl() {
            if (!this.sel && this.sel !== 0) return this.ph;
            const o = this.opts.find(o => String(o.v) === String(this.sel));
            return o ? o.l : this.ph;
        },

        get fil() {
            if (!this.search) return this.opts;
            const s = this.search.toLowerCase();
            return this.opts.filter(o => o.l.toLowerCase().includes(s));
        },

        tog() {
            if (!this.open) {
                /* Hitung posisi tombol trigger */
                const btn = this.$refs.trigger;
                if (btn) {
                    const r      = btn.getBoundingClientRect();
                    const below  = window.innerHeight - r.bottom;
                    this.openUp  = below < 260;
                    this._width  = r.width;
                    this._left   = r.left;
                    this._top    = this.openUp ? r.top : r.bottom + 4;
                }
                this.open = true;
                this.$nextTick(() => this.$refs.si && this.$refs.si.focus());
            } else {
                this.cls();
            }
        },

        cls() { this.open = false; this.search = ''; },

        pick(v) {
            this.sel = (v !== '' && v !== null && v !== undefined) ? String(v) : '';
            const num = (this.sel !== '' && !isNaN(this.sel)) ? Number(this.sel) : this.sel;
            this.$wire.set(this.wp, this.sel === '' ? '' : num);
            this.cls();
        }
    };
};
</script>
@endpush
