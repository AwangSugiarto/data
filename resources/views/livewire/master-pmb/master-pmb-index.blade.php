<div class="py-6 px-4 max-w-6xl mx-auto">
    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-4 mb-5">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Master PMB</h1>
            <p class="text-sm text-gray-500 mt-0.5">Entri individu data Daya Tampung dan Pendaftar per Prodi</p>
        </div>
        <button wire:click="create"
            class="bg-blue-700 hover:bg-blue-800 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2 transition shadow-sm">
            ➕ Tambah Data
        </button>
    </div>

    {{-- Notifikasi --}}
    @if(session('success'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3500)"
         x-transition class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm flex items-center gap-2">
        ✅ {{ session('success') }}
    </div>
    @endif

    {{-- Filter --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 mb-4">
        <div class="flex gap-4 items-center">
            <div class="flex-1">
                <input wire:model.live.debounce.400ms="search" type="text" placeholder="Cari Fakultas atau Prodi..."
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>
            <div>
                <select wire:model.live="perPage" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <option value="25">25 per halaman</option>
                    <option value="50">50 per halaman</option>
                    <option value="100">100 per halaman</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Tabel --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600 text-xs uppercase">No</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600 text-xs uppercase">Tahun Akademik</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600 text-xs uppercase">Jalur Seleksi</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600 text-xs uppercase">Fakultas</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600 text-xs uppercase">Program Studi</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-600 text-xs uppercase">Daya Tampung</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-600 text-xs uppercase">Pendaftar</th>
                        <th class="px-4 py-3 text-center font-semibold text-gray-600 text-xs uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($rows as $row)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3 text-gray-500">{{ $rows->firstItem() + $loop->index }}</td>
                        <td class="px-4 py-3">{{ $row->tahunAkademik->tahun ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $row->jalurSeleksi->nama_jalur ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $row->fakultas->nama_fakultas ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $row->programStudi->nama_prodi ?? '-' }}</td>
                        <td class="px-4 py-3 text-right font-medium">{{ number_format($row->daya_tampung) }}</td>
                        <td class="px-4 py-3 text-right font-medium">{{ number_format($row->pendaftar) }}</td>
                        <td class="px-4 py-3 text-center">
                            <button wire:click="edit({{ $row->id }})" class="text-blue-600 hover:text-blue-800 px-2 py-1">✏️</button>
                            <button wire:click="confirmDelete({{ $row->id }})" class="text-red-500 hover:text-red-700 px-2 py-1">🗑️</button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-gray-500">Data Master PMB belum ada</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $rows->links() }}
        </div>
    </div>

    {{-- Modal Form --}}
    @if($showModal)
    <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4" wire:click.self="closeModal">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg">
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                <h2 class="text-lg font-bold text-gray-900">{{ $editId ? 'Edit' : 'Tambah' }} Master PMB</h2>
                <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600 text-xl">&times;</button>
            </div>
            <form wire:submit="save" class="p-6 space-y-4">
                
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Tahun Akademik</label>
                    <select wire:model="f_tahun_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                        <option value="">Pilih Tahun</option>
                        @foreach($tahunList as $t)
                        <option value="{{ $t->id }}">{{ $t->tahun }}</option>
                        @endforeach
                    </select>
                    @error('f_tahun_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Jalur Seleksi</label>
                    <select wire:model="f_jalur_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                        <option value="">Pilih Jalur</option>
                        @foreach($jalurList as $j)
                        <option value="{{ $j->id }}">{{ $j->nama_jalur }}</option>
                        @endforeach
                    </select>
                    @error('f_jalur_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Fakultas</label>
                    <select wire:model.live="f_fakultas_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                        <option value="">Pilih Fakultas</option>
                        @foreach($fakultasList as $f)
                        <option value="{{ $f->id }}">{{ $f->nama_fakultas }}</option>
                        @endforeach
                    </select>
                    @error('f_fakultas_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Program Studi</label>
                    <select wire:model="f_prodi_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                        <option value="">Pilih Prodi</option>
                        @foreach($prodiFormList as $p)
                        <option value="{{ $p->id }}">{{ $p->nama_prodi }}</option>
                        @endforeach
                    </select>
                    @error('f_prodi_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Daya Tampung</label>
                        <input type="number" wire:model="f_daya_tampung" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                        @error('f_daya_tampung') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Pendaftar</label>
                        <input type="number" wire:model="f_pendaftar" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                        @error('f_pendaftar') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" wire:click="closeModal" class="px-4 py-2 text-sm border rounded-lg text-gray-600 hover:bg-gray-50">Batal</button>
                    <button type="submit" class="px-5 py-2 text-sm bg-blue-700 text-white rounded-lg hover:bg-blue-800">Simpan</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- Modal Hapus --}}
    @if($showConfirm)
    <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-xl p-6 text-center w-full max-w-sm">
            <h3 class="text-lg font-bold text-gray-900 mb-2">Hapus Data?</h3>
            <p class="text-sm text-gray-500 mb-5">Data yang dihapus tidak bisa dikembalikan.</p>
            <div class="flex justify-center gap-3">
                <button wire:click="closeModal" class="px-4 py-2 border rounded-lg text-sm text-gray-600 hover:bg-gray-50">Batal</button>
                <button wire:click="delete" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm hover:bg-red-700">Hapus</button>
            </div>
        </div>
    </div>
    @endif
</div>
