<div class="py-12">
    <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
        
        <!-- Header & Action -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Manajemen Role & Hak Akses</h1>
                <p class="text-sm text-gray-500 mt-1">Buat peran (role) baru dan atur izin akses (permissions) apa saja yang dimilikinya.</p>
            </div>
            
            <div class="flex gap-2 w-full sm:w-auto">
                <div class="relative w-full sm:w-64">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari role..." class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>
                <button wire:click="openCreate" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm whitespace-nowrap">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tambah Role
                </button>
            </div>
        </div>

        <!-- Flash Messages -->
        @if (session()->has('success'))
            <div class="mb-4 bg-green-50 border-l-4 border-green-500 p-4 rounded-r-lg">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-green-700 font-medium">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif
        @if (session()->has('error'))
            <div class="mb-4 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-red-700 font-medium">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Roles Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-4 font-semibold text-gray-700">Nama Role</th>
                            <th class="px-6 py-4 font-semibold text-gray-700">Permissions</th>
                            <th class="px-6 py-4 font-semibold text-gray-700 text-center w-32">Pengguna Aktif</th>
                            <th class="px-6 py-4 font-semibold text-gray-700 text-center w-32">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($roles as $role)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <span class="font-bold text-gray-900">{{ $role->name }}</span>
                                @if($role->name === 'super-admin')
                                <span class="ml-2 bg-red-100 text-red-800 text-xs font-semibold px-2 py-0.5 rounded-full">Root</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1">
                                    @if($role->name === 'super-admin')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-100 text-indigo-800">
                                            all_permissions
                                        </span>
                                    @elseif($role->permissions->count() > 0)
                                        @foreach($role->permissions as $perm)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700 border border-gray-200">
                                                {{ $perm->name }}
                                            </span>
                                        @endforeach
                                    @else
                                        <span class="text-gray-400 italic text-xs">Tidak ada izin</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white bg-gray-600 rounded-full">{{ $role->users->count() }}</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button wire:click="openEdit({{ $role->id }})" class="text-indigo-600 hover:text-indigo-900 border border-indigo-200 hover:border-indigo-400 bg-white px-2 py-1 rounded transition-colors text-xs font-medium shadow-sm">
                                        Edit
                                    </button>
                                    @if($role->id !== 1)
                                    <button wire:confirm="Yakin ingin menghapus role {{ $role->name }}?" wire:click="deleteRole({{ $role->id }})" class="text-red-600 hover:text-red-900 border border-red-200 hover:border-red-400 bg-white px-2 py-1 rounded transition-colors text-xs font-medium shadow-sm">
                                        Hapus
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                                Tidak ada data role yang cocok.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($roles->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 bg-white">
                {{ $roles->links() }}
            </div>
            @endif
        </div>
    </div>

    <!-- Modal CRUD -->
    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm transition-opacity">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl mx-4 overflow-hidden transform transition-all flex flex-col max-h-[90vh]" @click.stop>
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center flex-shrink-0">
                <h2 class="text-lg font-bold text-gray-900">{{ $editMode ? 'Edit Role & Akses' : 'Tambah Role Baru' }}</h2>
                <button wire:click="$set('showModal', false)" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            
            <div class="p-6 space-y-6 overflow-y-auto flex-grow">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Role <span class="text-red-500">*</span></label>
                    <input wire:model="name" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow outline-none font-bold text-gray-900" placeholder="Contoh: staf-keuangan" {{ $editId === 1 ? 'disabled' : '' }}>
                    <p class="text-xs text-gray-500 mt-1">Gunakan huruf kecil tanpa spasi (gunakan tanda hubung jika perlu).</p>
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                @if($editId !== 1)
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <label class="block text-sm font-medium text-gray-900">Hak Akses (Permissions)</label>
                        <span class="text-xs text-gray-500">Centang kotak untuk memberikan izin akses.</span>
                    </div>
                    
                    <div class="bg-gray-50 rounded-xl border border-gray-200 p-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-3 gap-x-6">
                            @foreach($permissions as $perm)
                            <label class="inline-flex items-center cursor-pointer group">
                                <input type="checkbox" wire:model="selectedPermissions" value="{{ $perm->name }}" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 w-4 h-4 transition-colors">
                                <span class="ml-2 text-sm text-gray-700 group-hover:text-gray-900 font-mono">{{ $perm->name }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>
                @else
                <div class="bg-indigo-50 text-indigo-800 p-4 rounded-lg border border-indigo-100 flex items-start">
                    <svg class="w-5 h-5 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p class="text-sm text-indigo-700">Role <strong>super-admin</strong> secara default memiliki semua akses (bypass) dalam sistem dan permission tidak perlu didefinisikan secara eksplisit.</p>
                </div>
                @endif
            </div>

            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex justify-end gap-3 flex-shrink-0">
                <button wire:click="$set('showModal', false)" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors shadow-sm">
                    Batal
                </button>
                <button wire:click="save" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors shadow-sm inline-flex items-center">
                    Simpan Perubahan
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
