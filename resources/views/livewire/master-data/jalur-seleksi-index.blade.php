<div class="py-8 px-4 max-w-6xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Manajemen Jalur Seleksi</h1>
            <p class="text-sm text-gray-500 mt-1">Kelola jalur seleksi PMB beserta alias historis</p>
        </div>
        <button wire:click="openCreate" class="bg-blue-700 hover:bg-blue-800 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Jalur
        </button>
    </div>
    @if(session('success'))<div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm">{{ session('error') }}</div>@endif
    <div class="mb-4">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari nama jalur..."
            class="w-full md:w-80 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-4 py-3 text-center font-semibold text-gray-700 w-12">No</th>
                    @php
                    $thClass = 'px-4 py-3 text-left font-semibold text-gray-700 cursor-pointer hover:bg-gray-100 select-none transition';
                    $icon = fn($col) => $sortColumn === $col ? ($sortDirection === 'asc' ? ' ↑' : ' ↓') : ' ↕';
                    @endphp
                    <th wire:click="sortBy('nama_jalur')" class="{{ $thClass }}">
                        Nama Jalur{{ $icon('nama_jalur') }}
                    </th>
                    <th wire:click="sortBy('lembaga')" class="{{ $thClass }}">
                        Lembaga{{ $icon('lembaga') }}
                    </th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Kelompok</th>
                    <th wire:click="sortBy('alias_count')" class="{{ $thClass }} text-center">
                        Alias{{ $icon('alias_count') }}
                    </th>
                    <th wire:click="sortBy('status_aktif')" class="{{ $thClass }} text-center">
                        Status{{ $icon('status_aktif') }}
                    </th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-700">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($jalur as $j)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-3 text-center text-gray-500">{{ $jalur->firstItem() + $loop->index }}</td>
                    <td class="px-4 py-3 font-medium text-gray-900">{{ $j->nama_jalur }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $j->lembaga ?? '-' }}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">{{ $j->kelompokJalur->nama_kelompok ?? '-' }}</span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="bg-gray-100 text-gray-600 text-xs px-2 py-0.5 rounded-full">{{ $j->alias_count }}</span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <button wire:click="toggleStatus({{ $j->id }})"
                            class="px-2 py-1 rounded-full text-xs font-medium {{ $j->status_aktif ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-500' }}">
                            {{ $j->status_aktif ? 'Aktif' : 'Non-aktif' }}
                        </button>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <button wire:click="openAlias({{ $j->id }})" class="text-purple-600 hover:text-purple-800 text-xs border border-purple-200 hover:border-purple-400 px-2 py-1 rounded transition">Alias</button>
                            <button wire:click="openEdit({{ $j->id }})" class="text-blue-600 hover:text-blue-800 text-xs border border-blue-200 hover:border-blue-400 px-2 py-1 rounded transition">Edit</button>
                            <button wire:confirm="Yakin ingin menghapus Jalur Seleksi ini beserta seluruh data aliasnya?" wire:click="deleteJalur({{ $j->id }})" class="text-red-600 hover:text-red-800 text-xs border border-red-200 hover:border-red-400 px-2 py-1 rounded transition">Hapus</button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-4 py-8 text-center text-gray-400">Tidak ada data jalur seleksi.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-100">{{ $jalur->links() }}</div>
    </div>

    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4 p-6" wire:click.stop>
            <h2 class="text-lg font-bold text-gray-900 mb-4">{{ $editMode ? 'Edit Jalur Seleksi' : 'Tambah Jalur Seleksi' }}</h2>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Jalur <span class="text-red-500">*</span></label>
                    <input wire:model="nama_jalur" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="SNBP">
                    @error('nama_jalur') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Lembaga (Penyelenggara)</label>
                    <select wire:model="lembaga" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none bg-white">
                        <option value="">— Pilih Lembaga —</option>
                        @foreach($lembagaList as $lb)
                        <option value="{{ $lb->nama_lembaga }}">
                            {{ $lb->nama_lembaga }}{{ $lb->singkatan ? ' (' . $lb->singkatan . ')' : '' }}
                        </option>
                        @endforeach
                    </select>
                    @error('lembaga') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    @if($lembagaList->isEmpty())
                        <p class="text-xs text-amber-600 mt-1">⚠ Belum ada data lembaga. Tambahkan di <a href="{{ route('master.lembaga') }}" class="underline">Master Lembaga</a>.</p>
                    @endif
                </div>

                <div class="flex items-center gap-2">
                    <input wire:model="status_aktif" type="checkbox" id="status_aktif_jalur" class="rounded border-gray-300">
                    <label for="status_aktif_jalur" class="text-sm text-gray-700">Jalur masih aktif digunakan</label>
                </div>
            </div>
            <div class="flex justify-end gap-3 mt-6">
                <button wire:click="$set('showModal', false)" class="px-4 py-2 text-sm text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50 transition">Batal</button>
                <button wire:click="save" class="px-4 py-2 text-sm text-white bg-blue-700 rounded-lg hover:bg-blue-800 transition">Simpan</button>
            </div>
        </div>
    </div>
    @endif

    @if($showAliasModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-lg mx-4 p-6" wire:click.stop>
            <h2 class="text-lg font-bold text-gray-900 mb-1">Alias Jalur Seleksi</h2>
            <p class="text-sm text-gray-500 mb-4">
                Jalur: <strong>{{ $aliasForJalurNama }}</strong>
                <span class="text-xs text-gray-400 ml-1">— Alias digunakan untuk mencocokkan nama jalur yang berbeda saat import data</span>
            </p>

            {{-- Daftar alias yang sudah ada --}}
            <div class="mb-4 space-y-2 max-h-52 overflow-y-auto border border-gray-100 rounded-lg p-2 bg-gray-50">
                @forelse($existingAliases as $a)
                <div class="flex items-center justify-between bg-white px-3 py-2 rounded-lg border border-gray-100 shadow-sm gap-2">
                    <span class="text-sm text-gray-700 flex-1">{{ $a['nama_alias'] }}</span>
                    <button wire:click="deleteAlias({{ $a['id'] }})"
                        class="text-red-500 hover:text-red-700 text-xs border border-red-100 hover:border-red-300 px-2 py-0.5 rounded transition">
                        Hapus
                    </button>
                </div>
                @empty
                <p class="text-sm text-gray-400 italic text-center py-3">Belum ada alias untuk jalur ini.</p>
                @endforelse
            </div>

            {{-- Form tambah alias baru --}}
            <div class="border-t border-gray-100 pt-4">
                <label class="block text-xs font-medium text-gray-600 mb-1">Tambah Alias Baru</label>
                <div class="flex gap-2">
                    <input wire:model="nama_alias" wire:keydown.enter="saveAlias" type="text"
                        placeholder="Contoh: Mandiri 1, M-I, Jalur Mandiri..."
                        class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:outline-none">
                    <button wire:click="saveAlias" class="px-4 py-2 text-sm text-white bg-purple-600 rounded-lg hover:bg-purple-700 transition whitespace-nowrap">
                        + Tambah
                    </button>
                </div>
                @error('nama_alias') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                <p class="text-xs text-gray-400 mt-1">Tekan Enter atau klik tombol untuk menyimpan alias.</p>
            </div>

            <div class="flex justify-end mt-4">
                <button wire:click="$set('showAliasModal', false)" class="px-4 py-2 text-sm text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50">Tutup</button>
            </div>
        </div>
    </div>
    @endif
</div>
