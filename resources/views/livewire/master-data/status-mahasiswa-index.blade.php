<div class="py-6 px-4 max-w-5xl mx-auto">

    {{-- Header --}}
    <div class="mb-6 flex items-center justify-between gap-4 flex-wrap">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">🎓 Master Status Mahasiswa</h1>
            <p class="text-sm text-gray-500 mt-0.5">Kelola daftar status akademik mahasiswa yang digunakan di seluruh sistem.</p>
        </div>
        <button wire:click="openCreate"
            class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg shadow transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Status
        </button>
    </div>

    {{-- Flash --}}
    @if(session('success'))
    <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm flex gap-2 items-center">
        ✅ {{ session('success') }}
    </div>
    @endif

    {{-- Form Modal --}}
    @if($showForm)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm" x-data>
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-4 p-6" @click.outside="$wire.cancel()">
            <h2 class="text-lg font-bold text-gray-900 mb-5">
                {{ $editingId ? '✏️ Edit Status' : '➕ Tambah Status Baru' }}
            </h2>

            <div class="space-y-4">
                {{-- Nama Status --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Status <span class="text-red-500">*</span></label>
                    <input wire:model.live="nama_status" type="text"
                        placeholder="contoh: Aktif, Stop Out, Drop Out..."
                        class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm @error('nama_status') border-red-400 @enderror">
                    @error('nama_status') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Kode Status --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kode Status <span class="text-red-500">*</span>
                        <span class="text-xs text-gray-400 font-normal ml-1">(digunakan di sistem/database)</span>
                    </label>
                    <input wire:model="kode_status" type="text"
                        placeholder="contoh: Aktif"
                        class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm font-mono @error('kode_status') border-red-400 @enderror">
                    @error('kode_status') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    {{-- Warna --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Warna Badge</label>
                        <select wire:model="warna"
                            class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-blue-500 text-sm">
                            @foreach($warnaOptions as $val => $label)
                            <option value="{{ $val }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Urutan --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Urutan Tampil</label>
                        <input wire:model="urutan" type="number" min="0"
                            class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-blue-500 text-sm">
                    </div>
                </div>

                {{-- Deskripsi --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                    <input wire:model="deskripsi" type="text"
                        placeholder="Keterangan singkat tentang status ini..."
                        class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-blue-500 text-sm">
                </div>

                {{-- Is Aktif Akademik --}}
                <div class="flex items-center gap-3 bg-blue-50 rounded-lg px-4 py-3">
                    <input wire:model="is_aktif_akademik" type="checkbox" id="chk_aktif"
                        class="rounded text-blue-600 focus:ring-blue-500 w-4 h-4">
                    <label for="chk_aktif" class="text-sm text-gray-700 cursor-pointer">
                        <span class="font-medium">Hitung sebagai Mahasiswa Aktif</span>
                        <span class="block text-xs text-gray-400 mt-0.5">Status ini masuk dalam hitungan mahasiswa aktif di dashboard & rekapitulasi</span>
                    </label>
                </div>

                {{-- Preview Badge --}}
                @php
                    $previewClass = match($warna) {
                        'green'  => 'bg-green-100 text-green-700',
                        'amber'  => 'bg-amber-100 text-amber-700',
                        'red'    => 'bg-red-100 text-red-700',
                        'blue'   => 'bg-blue-100 text-blue-700',
                        'purple' => 'bg-purple-100 text-purple-700',
                        default  => 'bg-gray-100 text-gray-600',
                    };
                @endphp
                <div class="flex items-center gap-2 text-sm text-gray-500">
                    <span>Preview:</span>
                    <span class="px-2.5 py-1 rounded-full text-xs font-medium {{ $previewClass }}">
                        {{ $nama_status ?: 'Nama Status' }}
                    </span>
                </div>
            </div>

            {{-- Buttons --}}
            <div class="mt-6 flex justify-end gap-3">
                <button wire:click="cancel"
                    class="px-4 py-2 text-sm border border-gray-300 text-gray-600 hover:bg-gray-50 rounded-lg transition">
                    Batal
                </button>
                <button wire:click="save" wire:loading.attr="disabled"
                    class="px-5 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg shadow transition disabled:opacity-60">
                    <span wire:loading.remove wire:target="save">💾 Simpan</span>
                    <span wire:loading wire:target="save">Menyimpan...</span>
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- Tabel --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="w-full text-sm text-gray-700">
            <thead class="bg-gray-50 border-b border-gray-100 text-xs uppercase text-gray-400">
                <tr>
                    <th class="px-4 py-3 text-center w-10">No</th>
                    <th class="px-4 py-3 text-left">Nama Status</th>
                    <th class="px-4 py-3 text-left">Kode</th>
                    <th class="px-4 py-3 text-center">Badge</th>
                    <th class="px-4 py-3 text-left">Deskripsi</th>
                    <th class="px-4 py-3 text-center">Aktif Akademik</th>
                    <th class="px-4 py-3 text-center w-24">Urutan</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($statusList as $i => $s)
                @php
                    $badgeClass = match($s->warna) {
                        'green'  => 'bg-green-100 text-green-700',
                        'amber'  => 'bg-amber-100 text-amber-700',
                        'red'    => 'bg-red-100 text-red-700',
                        'blue'   => 'bg-blue-100 text-blue-700',
                        'purple' => 'bg-purple-100 text-purple-700',
                        default  => 'bg-gray-100 text-gray-600',
                    };
                @endphp
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-3 text-center text-xs text-gray-400">{{ $i + 1 }}</td>
                    <td class="px-4 py-3 font-semibold text-gray-800">{{ $s->nama_status }}</td>
                    <td class="px-4 py-3 font-mono text-xs text-gray-500">{{ $s->kode_status }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="px-2.5 py-1 rounded-full text-xs font-medium {{ $badgeClass }}">
                            {{ $s->nama_status }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-500 max-w-xs">{{ $s->deskripsi ?? '—' }}</td>
                    <td class="px-4 py-3 text-center">
                        @if($s->is_aktif_akademik)
                        <span class="inline-flex items-center gap-1 text-xs text-green-600 font-medium">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 00-1.414 0L8 12.586 4.707 9.293a1 1 0 00-1.414 1.414l4 4a1 1 0 001.414 0l8-8a1 1 0 000-1.414z" clip-rule="evenodd"/></svg>
                            Ya
                        </span>
                        @else
                        <span class="text-xs text-gray-300">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center text-xs text-gray-400">{{ $s->urutan }}</td>
                    <td class="px-4 py-3 text-right">
                        <button wire:click="openEdit({{ $s->id }})"
                            class="text-xs border border-blue-200 text-blue-600 hover:bg-blue-50 px-2.5 py-1 rounded transition mr-1">
                            ✏️ Edit
                        </button>
                        <button wire:click="delete({{ $s->id }})"
                            wire:confirm="Hapus status '{{ $s->nama_status }}'? Pastikan tidak ada mahasiswa yang menggunakan status ini."
                            class="text-xs border border-red-200 text-red-500 hover:bg-red-50 px-2.5 py-1 rounded transition">
                            🗑️ Hapus
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="py-12 text-center text-gray-400 text-sm">Belum ada data status mahasiswa.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <p class="text-xs text-gray-400 mt-3">
        💡 Status yang ditandai "Aktif Akademik" akan dihitung dalam rekapitulasi mahasiswa aktif di Dashboard dan halaman Mahasiswa Aktif.
    </p>
</div>
