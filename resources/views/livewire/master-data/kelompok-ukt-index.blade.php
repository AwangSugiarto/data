<div class="py-8 px-4 max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Kelompok UKT</h1>
            <p class="text-sm text-gray-500 mt-1">Kelola rentang nominal per kelompok UKT</p>
        </div>
        <div class="flex items-center gap-3">
            <select wire:model.live="filterTahunAkademik" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                <option value="">-- Pilih Tahun Akademik --</option>
                @foreach($tahunList as $th)
                    <option value="{{ $th->id }}">{{ $th->tahun }}</option>
                @endforeach
            </select>
            <button wire:click="openCreate" class="bg-blue-700 hover:bg-blue-800 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Kelompok
            </button>
        </div>
    </div>
    @if(session('success'))<div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">{{ session('success') }}</div>@endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-4 py-3 text-center font-semibold text-gray-700">Tahun</th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-700">Kelompok</th>
                    <th class="px-4 py-3 text-right font-semibold text-gray-700">Nominal Minimum</th>
                    <th class="px-4 py-3 text-right font-semibold text-gray-700">Nominal Maksimum</th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-700">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($uktList as $u)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-3 text-center text-gray-600 text-xs font-mono">{{ $u->tahunAkademik->tahun ?? '-' }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="px-3 py-1 rounded bg-blue-100 text-blue-800 font-bold inline-flex items-center justify-center">{{ $u->kelompok }}</span>
                    </td>
                    <td class="px-4 py-3 text-right text-gray-700">{{ $u->nominal_min ? 'Rp ' . number_format($u->nominal_min, 0, ',', '.') : '-' }}</td>
                    <td class="px-4 py-3 text-right text-gray-700">{{ $u->nominal_max ? 'Rp ' . number_format($u->nominal_max, 0, ',', '.') : 'Tidak terbatas' }}</td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <button wire:click="openEdit({{ $u->id }})" class="text-blue-600 hover:text-blue-800 text-xs border border-blue-200 hover:border-blue-400 px-2 py-1 rounded transition">Edit</button>
                            <button wire:confirm="Yakin ingin menghapus Kelompok UKT ini?" wire:click="deleteUkt({{ $u->id }})" class="text-red-600 hover:text-red-800 text-xs border border-red-200 hover:border-red-400 px-2 py-1 rounded transition">Hapus</button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-4 py-8 text-center text-gray-400">Belum ada data kelompok UKT.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4 p-6" wire:click.stop>
            <h2 class="text-lg font-bold text-gray-900 mb-4">{{ $editMode ? 'Edit Kelompok UKT' : 'Tambah Kelompok UKT' }}</h2>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tahun Akademik</label>
                    <select wire:model="tahun_akademik_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <option value="">-- Pilih --</option>
                        @foreach($tahunList as $th)
                            <option value="{{ $th->id }}">{{ $th->tahun }}</option>
                        @endforeach
                    </select>
                    @error('tahun_akademik_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Kelompok (1-7, KIP, dll)</label>
                    <input wire:model="kelompok" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="Misal: KIP">
                    @error('kelompok') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nominal Min (Rp)</label>
                        <input wire:model="nominal_min" type="number" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="0">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nominal Max (Rp)</label>
                        <input wire:model="nominal_max" type="number" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="Kosongkan = tak terbatas">
                    </div>
                </div>
            </div>
            <div class="flex justify-end gap-3 mt-6">
                <button wire:click="$set('showModal', false)" class="px-4 py-2 text-sm text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50 transition">Batal</button>
                <button wire:click="save" class="px-4 py-2 text-sm text-white bg-blue-700 rounded-lg hover:bg-blue-800 transition">Simpan</button>
            </div>
        </div>
    </div>
    @endif
</div>
