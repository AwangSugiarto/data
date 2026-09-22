<div class="py-8 px-4 max-w-6xl mx-auto">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">🌍 Master Negara / Kewarganegaraan</h1>
            <p class="text-sm text-gray-500 mt-1">Daftar negara untuk data kewarganegaraan mahasiswa asing.</p>
        </div>
        <button wire:click="openCreate"
            class="bg-blue-700 hover:bg-blue-800 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Negara
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

    {{-- Search + Info --}}
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <input wire:model.live.debounce.300ms="search" type="text"
            placeholder="🔍 Cari nama negara atau kode..."
            class="w-full md:w-80 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        <span class="text-xs text-gray-400">Total: {{ $negara->total() }} negara</span>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-4 py-3 text-center font-semibold text-gray-600 w-12">No</th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-600 w-20">Kode</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600 cursor-pointer select-none"
                        wire:click="sortBy('nama_negara')">
                        <div class="flex items-center gap-1">
                            Nama Negara
                            @if($sortColumn === 'nama_negara')
                                <span class="text-blue-500">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </div>
                    </th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-600 w-32">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($negara as $n)
                <tr class="hover:bg-gray-50 transition" wire:key="negara-{{ $n->id }}">
                    <td class="px-4 py-3 text-center text-gray-400">{{ $negara->firstItem() + $loop->index }}</td>
                    <td class="px-4 py-3 text-center">
                        @if($n->kode_negara)
                            <span class="inline-flex items-center px-2 py-0.5 rounded bg-slate-100 text-slate-600 text-xs font-mono font-semibold tracking-wider">
                                {{ $n->kode_negara }}
                            </span>
                        @else
                            <span class="text-gray-300 text-xs">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 font-medium text-gray-900">
                        @if($n->nama_negara === 'Indonesia')
                            <span>🇮🇩 {{ $n->nama_negara }}</span>
                        @else
                            <span>🌐 {{ $n->nama_negara }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-center gap-2">
                            <button wire:click="openEdit({{ $n->id }})"
                                class="text-blue-600 hover:text-blue-800 text-xs border border-blue-200 hover:border-blue-400 px-2 py-1 rounded transition">
                                Edit
                            </button>
                            @if($n->nama_negara !== 'Indonesia')
                            <button wire:confirm="Yakin ingin menghapus negara '{{ $n->nama_negara }}'?"
                                wire:click="delete({{ $n->id }})"
                                class="text-red-600 hover:text-red-800 text-xs border border-red-200 hover:border-red-400 px-2 py-1 rounded transition">
                                Hapus
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-4 py-12 text-center text-gray-400">
                        <div class="flex flex-col items-center gap-2">
                            <span class="text-3xl">🌍</span>
                            <p class="font-medium">Belum ada data negara.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-100">{{ $negara->links() }}</div>
    </div>

    {{-- Info box --}}
    <div class="mt-4 p-4 bg-blue-50 border border-blue-100 rounded-xl text-sm text-blue-700">
        <p class="font-medium mb-1">ℹ️ Cara Penggunaan</p>
        <ul class="list-disc list-inside text-xs text-blue-600 space-y-0.5">
            <li>Data negara ini digunakan untuk menentukan kewarganegaraan mahasiswa.</li>
            <li>Kode negara mengikuti standar ISO 3166-1 alpha-2 (contoh: <strong>MY</strong> = Malaysia, <strong>SA</strong> = Arab Saudi).</li>
            <li>Mahasiswa dengan kewarganegaraan non-Indonesia akan tampil di grafis Mahasiswa Asing pada Dashboard Akademik.</li>
            <li>Indonesia tidak dapat dihapus karena merupakan data default sistem.</li>
        </ul>
    </div>

    {{-- Modal Tambah / Edit --}}
    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm"
        wire:click.self="$set('showModal', false)">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4 p-6" wire:click.stop>
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-lg font-bold text-gray-900">
                    {{ $editMode ? '✏️ Edit Negara' : '🌍 Tambah Negara' }}
                </h2>
                <button wire:click="$set('showModal', false)" class="text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="space-y-4">
                {{-- Nama Negara --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Nama Negara <span class="text-red-500">*</span>
                    </label>
                    <input wire:model="nama_negara" type="text"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        placeholder="Malaysia">
                    @error('nama_negara') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Kode Negara --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Kode Negara
                        <span class="text-xs text-gray-400 font-normal">(ISO 3166-1 alpha-2)</span>
                    </label>
                    <input wire:model="kode_negara" type="text" maxlength="5"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono uppercase focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        placeholder="MY">
                    @error('kode_negara') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    <p class="text-xs text-gray-400 mt-1">Contoh: MY (Malaysia), SA (Arab Saudi), EG (Mesir), CN (China)</p>
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
