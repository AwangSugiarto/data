<div class="py-8 px-4 max-w-6xl mx-auto">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">🏛️ Master Lembaga Penyelenggara</h1>
            <p class="text-sm text-gray-500 mt-1">Kelola data lembaga yang menyelenggarakan seleksi PMB (contoh: Kemdikbud/SNPMB, Kementerian Agama/BPPP, Mandiri Institusi).</p>
        </div>
        <button wire:click="openCreate"
            class="bg-blue-700 hover:bg-blue-800 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Lembaga
        </button>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            {{ session('error') }}
        </div>
    @endif

    {{-- Search --}}
    <div class="mb-4">
        <input wire:model.live.debounce.300ms="search" type="text"
            placeholder="🔍 Cari nama lembaga atau singkatan..."
            class="w-full md:w-96 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-4 py-3 text-center font-semibold text-gray-600 w-12">No</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600 cursor-pointer select-none" wire:click="sortBy('nama_lembaga')">
                        <div class="flex items-center gap-1">
                            Nama Lembaga
                            @if($sortColumn === 'nama_lembaga')
                                <span class="text-blue-500">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </div>
                    </th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-600 w-32">Singkatan</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Keterangan</th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-600 w-24">Status</th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-600 w-32">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($lembaga as $l)
                <tr class="hover:bg-gray-50 transition" wire:key="lembaga-{{ $l->id }}">
                    <td class="px-4 py-3 text-center text-gray-400">{{ $lembaga->firstItem() + $loop->index }}</td>
                    <td class="px-4 py-3">
                        <span class="font-medium text-gray-900">{{ $l->nama_lembaga }}</span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($l->singkatan)
                            <span class="inline-flex items-center px-2 py-0.5 rounded bg-indigo-50 text-indigo-700 text-xs font-semibold">{{ $l->singkatan }}</span>
                        @else
                            <span class="text-gray-300 text-xs">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-gray-500 text-xs">{{ $l->keterangan ?? '—' }}</td>
                    <td class="px-4 py-3 text-center">
                        <button wire:click="toggleStatus({{ $l->id }})"
                            class="{{ $l->status_aktif ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }} text-xs px-2 py-1 rounded-full font-medium transition">
                            {{ $l->status_aktif ? 'Aktif' : 'Nonaktif' }}
                        </button>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-center gap-2">
                            <button wire:click="openEdit({{ $l->id }})"
                                class="text-blue-600 hover:text-blue-800 text-xs border border-blue-200 hover:border-blue-400 px-2 py-1 rounded transition">
                                Edit
                            </button>
                            <button wire:confirm="Yakin ingin menghapus lembaga '{{ $l->nama_lembaga }}'?"
                                wire:click="delete({{ $l->id }})"
                                class="text-red-600 hover:text-red-800 text-xs border border-red-200 hover:border-red-400 px-2 py-1 rounded transition">
                                Hapus
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-12 text-center text-gray-400">
                        <div class="flex flex-col items-center gap-2">
                            <span class="text-3xl">🏛️</span>
                            <p class="font-medium">Belum ada data lembaga.</p>
                            <p class="text-xs">Klik "Tambah Lembaga" untuk menambahkan lembaga pertama.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-100">{{ $lembaga->links() }}</div>
    </div>

    {{-- Modal Tambah / Edit --}}
    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm"
        wire:click.self="$set('showModal', false)">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-lg mx-4 p-6" wire:click.stop>
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-lg font-bold text-gray-900">
                    {{ $editMode ? '✏️ Edit Lembaga' : '🏛️ Tambah Lembaga' }}
                </h2>
                <button wire:click="$set('showModal', false)" class="text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="space-y-4">
                {{-- Nama Lembaga --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Nama Lembaga <span class="text-red-500">*</span>
                    </label>
                    <input wire:model="nama_lembaga" type="text"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        placeholder="Kementerian Agama / BPPP">
                    @error('nama_lembaga') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Singkatan --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Singkatan / Akronim</label>
                    <input wire:model="singkatan" type="text"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        placeholder="Kemenag">
                    @error('singkatan') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Keterangan --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label>
                    <textarea wire:model="keterangan" rows="2"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none resize-none"
                        placeholder="Keterangan tambahan tentang lembaga ini (opsional)"></textarea>
                    @error('keterangan') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Status Aktif --}}
                <div class="flex items-center gap-3 pt-1">
                    <input wire:model="status_aktif" type="checkbox" id="status_aktif_lembaga"
                        class="w-4 h-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                    <label for="status_aktif_lembaga" class="text-sm text-gray-700">Lembaga masih aktif digunakan</label>
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-6 pt-5 border-t border-gray-100">
                <button wire:click="$set('showModal', false)"
                    class="px-4 py-2 text-sm text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                    Batal
                </button>
                <button wire:click="save"
                    class="px-4 py-2 text-sm text-white bg-blue-700 rounded-lg hover:bg-blue-800 transition">
                    Simpan
                </button>
            </div>
        </div>
    </div>
    @endif

</div>
