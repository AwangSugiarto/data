<div class="py-8 px-4 max-w-6xl mx-auto">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Manajemen Fakultas</h1>
            <p class="text-sm text-gray-500 mt-1">Kelola data fakultas beserta alias historis</p>
        </div>
        <button wire:click="openCreate"
            class="bg-blue-700 hover:bg-blue-800 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Fakultas
        </button>
    </div>

    {{-- Flash --}}
    @if(session('success'))
        <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">{{ session('success') }}</div>
    @endif

    {{-- Search --}}
    <div class="mb-4">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari nama fakultas..."
            class="w-full md:w-80 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-4 py-3 text-center font-semibold text-gray-700 w-12">No</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Nama Fakultas</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Kode</th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-700">Prodi</th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-700">Status</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Berlaku</th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-700">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @php $totalProdi = 0; @endphp
                @forelse($fakultas as $f)
                @php $totalProdi += $f->program_studi_count; @endphp
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-3 text-center text-gray-500">{{ $fakultas->firstItem() + $loop->index }}</td>
                    <td class="px-4 py-3 font-medium text-gray-900">{{ $f->nama_fakultas }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $f->kode_fakultas ?? '-' }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2 py-0.5 rounded-full">{{ $f->program_studi_count }}</span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <button wire:click="toggleStatus({{ $f->id }})"
                            class="px-2 py-1 rounded-full text-xs font-medium {{ $f->status_terkini ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-500' }}">
                            {{ $f->status_terkini ? 'Aktif' : 'Non-aktif' }}
                        </button>
                    </td>
                    <td class="px-4 py-3 text-gray-500 text-xs">
                        {{ $f->berlaku_dari ? $f->berlaku_dari->format('Y') : '-' }}
                        {{ $f->berlaku_sampai ? '– ' . $f->berlaku_sampai->format('Y') : '' }}
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <button wire:click="openAlias({{ $f->id }})" title="Kelola Alias"
                                class="text-purple-600 hover:text-purple-800 text-xs border border-purple-200 hover:border-purple-400 px-2 py-1 rounded transition">
                                Alias
                            </button>
                            <button wire:click="openEdit({{ $f->id }})" title="Edit"
                                class="text-blue-600 hover:text-blue-800 text-xs border border-blue-200 hover:border-blue-400 px-2 py-1 rounded transition">
                                Edit
                            </button>
                            <button wire:confirm="Yakin ingin menghapus Fakultas ini beserta seluruh data aliasnya?" wire:click="deleteFakultas({{ $f->id }})" title="Hapus"
                                class="text-red-600 hover:text-red-800 text-xs border border-red-200 hover:border-red-400 px-2 py-1 rounded transition">
                                Hapus
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-4 py-8 text-center text-gray-400">Tidak ada data fakultas.</td></tr>
                @endforelse
            </tbody>
            @if(count($fakultas) > 0)
            <tfoot class="bg-gray-100 border-t border-gray-200">
                <tr>
                    <td colspan="3" class="px-4 py-3 text-right font-bold text-gray-800">Total Prodi</td>
                    <td class="px-4 py-3 text-center">
                        <span class="bg-blue-600 text-white text-xs font-bold px-2 py-1 rounded-lg shadow-sm">{{ $totalProdi }}</span>
                    </td>
                    <td colspan="3"></td>
                </tr>
            </tfoot>
            @endif
        </table>
        <div class="px-4 py-3 border-t border-gray-100">{{ $fakultas->links() }}</div>
    </div>

    {{-- Modal CRUD --}}
    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-lg mx-4 p-6" wire:click.stop>
            <h2 class="text-lg font-bold text-gray-900 mb-4">{{ $editMode ? 'Edit Fakultas' : 'Tambah Fakultas Baru' }}</h2>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Fakultas <span class="text-red-500">*</span></label>
                    <input wire:model="nama_fakultas" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="Fakultas Syariah dan Hukum">
                    @error('nama_fakultas') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kode</label>
                        <input wire:model="kode_fakultas" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="FSH">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select wire:model="status_terkini" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <option value="1">Aktif</option>
                            <option value="0">Non-aktif</option>
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

                {{-- Kolom Tambahan Fakultas --}}
                <div class="grid grid-cols-2 gap-3 mt-4 pt-4 border-t border-gray-100">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Dekan / Pejabat</label>
                        <input wire:model="pejabat" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="Nama Dekan">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">NIP Pejabat</label>
                        <input wire:model="nip_pejabat" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="19800101...">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Telepon</label>
                        <input wire:model="telepon" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="0711-xxxxxx">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input wire:model="email" type="email" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="fakultas@uinrf.ac.id">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label>
                    <textarea wire:model="keterangan" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="Informasi tambahan..."></textarea>
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
        <div class="bg-white rounded-xl shadow-xl w-full max-w-lg mx-4 p-6" wire:click.stop>
            <h2 class="text-lg font-bold text-gray-900 mb-1">Alias Fakultas</h2>
            <p class="text-sm text-gray-500 mb-4">{{ $aliasForFakultasNama }}</p>

            {{-- Existing aliases --}}
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

            {{-- Add new alias --}}
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
