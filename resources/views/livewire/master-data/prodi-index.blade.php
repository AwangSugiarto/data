<div class="py-8 px-4 max-w-6xl mx-auto">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Manajemen Program Studi</h1>
            <p class="text-sm text-gray-500 mt-1">Kelola data program studi beserta alias historis</p>
        </div>
        <button wire:click="openCreate"
            class="bg-blue-700 hover:bg-blue-800 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Prodi
        </button>
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm">{{ session('error') }}</div>
    @endif

    {{-- Filter Bar --}}
    <div class="flex flex-wrap gap-3 mb-4">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari nama prodi..."
            class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 w-64">
        <select wire:model.live="filterFakultas" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Semua Fakultas</option>
            @foreach($fakultasList as $f)
            <option value="{{ $f->id }}">{{ $f->kode_fakultas ?? $f->nama_fakultas }}</option>
            @endforeach
        </select>
        <select wire:model.live="filterJenjang" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Semua Jenjang</option>
            <option value="D3">D3</option>
            <option value="S1">S1</option>
            <option value="S2">S2</option>
            <option value="S3">S3</option>
        </select>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-4 py-3 text-center font-semibold text-gray-700 w-12">No</th>
                    <th wire:click="sortBy('kode_prodi')" class="px-4 py-3 text-left font-semibold text-gray-700 cursor-pointer hover:bg-gray-100 transition">
                        <div class="flex items-center gap-1">Kode Prodi @if($sortColumn === 'kode_prodi') <span class="text-xs">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif</div>
                    </th>
                    <th wire:click="sortBy('nama_prodi')" class="px-4 py-3 text-left font-semibold text-gray-700 cursor-pointer hover:bg-gray-100 transition">
                        <div class="flex items-center gap-1">Nama Prodi @if($sortColumn === 'nama_prodi') <span class="text-xs">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif</div>
                    </th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Kode Fakultas</th>
                    <th wire:click="sortBy('fakultas.nama_fakultas')" class="px-4 py-3 text-left font-semibold text-gray-700 cursor-pointer hover:bg-gray-100 transition">
                        <div class="flex items-center gap-1">Fakultas @if($sortColumn === 'fakultas.nama_fakultas') <span class="text-xs">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif</div>
                    </th>
                    <th wire:click="sortBy('jenjang')" class="px-4 py-3 text-center font-semibold text-gray-700 cursor-pointer hover:bg-gray-100 transition">
                        <div class="flex items-center justify-center gap-1">Jenjang @if($sortColumn === 'jenjang') <span class="text-xs">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif</div>
                    </th>
                    <th wire:click="sortBy('status_terkini')" class="px-4 py-3 text-center font-semibold text-gray-700 cursor-pointer hover:bg-gray-100 transition">
                        <div class="flex items-center justify-center gap-1">Status @if($sortColumn === 'status_terkini') <span class="text-xs">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif</div>
                    </th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-700">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($prodi as $p)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-3 text-center text-gray-500 text-xs">{{ $prodi->firstItem() + $loop->index }}</td>
                    <td class="px-4 py-3 text-gray-600 text-xs font-mono">{{ $p->kode_prodi ?? '-' }}</td>
                    <td class="px-4 py-3 font-medium text-gray-900">{{ $p->nama_prodi }}</td>
                    <td class="px-4 py-3 text-gray-600 text-xs font-mono">{{ $p->fakultas?->kode_fakultas ?? '-' }}</td>
                    <td class="px-4 py-3 text-gray-500 text-xs">{{ $p->fakultas?->nama_fakultas ?? '-' }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium
                            {{ $p->jenjang === 'S1' ? 'bg-blue-100 text-blue-800' : ($p->jenjang === 'S2' ? 'bg-purple-100 text-purple-800' : 'bg-orange-100 text-orange-800') }}">
                            {{ $p->jenjang }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <button wire:click="toggleStatus({{ $p->id }})"
                            class="px-2 py-1 rounded-full text-xs font-medium {{ $p->status_terkini ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-500' }}">
                            {{ $p->status_terkini ? 'Aktif' : 'Non-aktif' }}
                        </button>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex justify-center gap-2">
                            <button wire:click="openAlias({{ $p->id }})" class="px-2 py-1 bg-purple-50 text-purple-600 text-xs rounded border border-purple-200 hover:bg-purple-100 transition">
                                Alias
                            </button>
                            <button wire:click="openEdit({{ $p->id }})" class="px-2 py-1 bg-blue-50 text-blue-600 text-xs rounded border border-blue-200 hover:bg-blue-100 transition">
                                Edit
                            </button>
                            <button wire:click="deleteProdi({{ $p->id }})" wire:confirm="Yakin ingin menghapus prodi ini beserta seluruh aliasnya?" class="px-2 py-1 bg-red-50 text-red-600 text-xs rounded border border-red-200 hover:bg-red-100 transition">
                                Hapus
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="px-4 py-8 text-center text-gray-400">Tidak ada data program studi.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-100">{{ $prodi->links() }}</div>
    </div>

    {{-- Modal CRUD --}}
    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-4xl mx-4 p-6 max-h-[90vh] overflow-y-auto" @click.stop>
            <h2 class="text-lg font-bold text-gray-900 mb-4">{{ $editMode ? 'Edit Program Studi' : 'Tambah Program Studi' }}</h2>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fakultas <span class="text-red-500">*</span></label>
                    <select wire:model="fakultas_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <option value="">-- Pilih Fakultas --</option>
                        @foreach($fakultasList as $f)
                        <option value="{{ $f->id }}">{{ $f->nama_fakultas }}</option>
                        @endforeach
                    </select>
                    @error('fakultas_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Program Studi <span class="text-red-500">*</span></label>
                    <input wire:model="nama_prodi" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="Hukum Ekonomi Syariah">
                    @error('nama_prodi') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kode Prodi</label>
                        <input wire:model="kode_prodi" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="HES">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Jenjang <span class="text-red-500">*</span></label>
                        <select wire:model="jenjang" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <option value="D3">D3</option>
                            <option value="S1">S1</option>
                            <option value="S2">S2</option>
                            <option value="S3">S3</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Berlaku Dari</label>
                        <input wire:model="berlaku_dari" type="date" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Berlaku Sampai</label>
                        <input wire:model="berlaku_sampai" type="date" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>
                </div>

                <div class="mt-4 pt-4 border-t border-gray-100">
                    <h3 class="text-md font-semibold text-gray-800 mb-3">Informasi Akademik</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Kode Dikti</label>
                            <input wire:model="kode_dikti" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Nama Jenjang (e.g. Sarjana)</label>
                            <input wire:model="nama_jenjang_pendidikan" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Gelar (e.g. S.Kom.)</label>
                            <input wire:model="gelar" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Akreditasi</label>
                            <input wire:model="akreditasi" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Tgl Pendirian</label>
                            <input wire:model="tanggal_pendirian" type="date" class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm">
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4 pt-4 border-t border-gray-100">
                    <div>
                        <h3 class="text-md font-semibold text-gray-800 mb-2">SK Dikti</h3>
                        <div class="space-y-2">
                            <div>
                                <label class="block text-xs font-medium text-gray-700">No SK Dikti</label>
                                <input wire:model="no_sk_dikti" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm">
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700">Tgl SK</label>
                                    <input wire:model="tgl_sk_dikti" type="date" class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700">Tgl Akhir SK</label>
                                    <input wire:model="tgl_akhir_sk_dikti" type="date" class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-md font-semibold text-gray-800 mb-2">SK BAN-PT</h3>
                        <div class="space-y-2">
                            <div>
                                <label class="block text-xs font-medium text-gray-700">No SK BAN-PT</label>
                                <input wire:model="no_sk_ban" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm">
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700">Tgl SK</label>
                                    <input wire:model="tgl_sk_ban" type="date" class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700">Tgl Akhir SK</label>
                                    <input wire:model="tgl_akhir_sk_ban" type="date" class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 pt-4 border-t border-gray-100">
                    <h3 class="text-md font-semibold text-gray-800 mb-3">Pejabat & Kontak</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Nama Pejabat (Kaprodi)</label>
                            <input wire:model="pejabat" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Jabatan</label>
                            <input wire:model="jabatan" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Nama Sekprodi</label>
                            <input wire:model="nama_sekprodi" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">NIP Sekprodi</label>
                            <input wire:model="nip_sekprodi" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Telepon</label>
                            <input wire:model="telepon" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Email</label>
                            <input wire:model="email" type="email" class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm">
                        </div>
                    </div>
                </div>

                <div class="mt-4 pt-4 border-t border-gray-100">
                    <h3 class="text-md font-semibold text-gray-800 mb-3">Jalur Seleksi Tersedia</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        @foreach($jalurOptions as $jalur)
                        <label class="flex items-center space-x-2 text-sm cursor-pointer hover:bg-gray-50 p-2 rounded-lg border border-transparent hover:border-gray-200 transition">
                            <input type="checkbox" wire:model="selectedJalurs" value="{{ $jalur->id }}" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span class="text-gray-700">{{ $jalur->nama_jalur }}</span>
                        </label>
                        @endforeach
                    </div>
                    @error('selectedJalurs') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div class="mt-4 pt-4 border-t border-gray-100">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label>
                    <textarea wire:model="keterangan" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none"></textarea>
                </div>
            </div>
            <div class="flex justify-end gap-3 mt-6">
                <button wire:click="$set('showModal', false)" class="px-4 py-2 text-sm text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50 transition">Batal</button>
                <button wire:click="save" class="px-4 py-2 text-sm text-white bg-blue-700 rounded-lg hover:bg-blue-800 transition">Simpan</button>
            </div>
        </div>
    </div>
    @endif

    {{-- Modal Alias --}}
    @if($showAliasModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-lg mx-4 p-6" @click.stop>
            <h2 class="text-lg font-bold text-gray-900 mb-1">Alias Program Studi</h2>
            <p class="text-sm text-gray-500 mb-4">{{ $aliasForProdiNama }}</p>
            <div class="mb-4 space-y-2 max-h-40 overflow-y-auto">
                @forelse($existingAliases as $a)
                <div class="flex items-center justify-between bg-gray-50 px-3 py-2 rounded-lg">
                    <span class="text-sm text-gray-700">{{ $a['nama_alias'] }}</span>
                    <button wire:click="deleteAlias({{ $a['id'] }})" class="text-red-500 hover:text-red-700 text-xs">Hapus</button>
                </div>
                @empty
                <p class="text-sm text-gray-400 italic">Belum ada alias.</p>
                @endforelse
            </div>
            <div class="flex gap-2">
                <input wire:model="nama_alias" type="text" placeholder="Tambah alias baru..."
                    class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:outline-none">
                <button wire:click="saveAlias" class="px-4 py-2 text-sm text-white bg-purple-600 rounded-lg hover:bg-purple-700 transition">Tambah</button>
            </div>
            <div class="flex justify-end mt-4">
                <button wire:click="$set('showAliasModal', false)" class="px-4 py-2 text-sm text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50 transition">Tutup</button>
            </div>
        </div>
    </div>
    @endif
</div>
