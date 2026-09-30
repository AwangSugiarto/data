<div class="py-6 px-4 max-w-6xl mx-auto">
        <!-- Header & Action -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Manajemen Pengguna</h1>
                <p class="text-sm text-gray-500 mt-1">Kelola akun pengguna, email, dan hak akses (role) di dalam sistem.</p>
            </div>
            
            <div class="flex gap-2 w-full sm:w-auto">
                <div class="relative w-full sm:w-64">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari nama atau email..." class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
                @if(auth()->user()->hasAnyRole(['super-admin', 'admin', 'admin-pmb']))
                <button wire:click="openCreate" class="inline-flex items-center px-4 py-2 bg-blue-700 hover:bg-blue-800 text-white text-sm font-medium rounded-lg transition-colors shadow-sm whitespace-nowrap">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tambah User
                </button>
                @endif
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

        <!-- Users Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-4 py-3 font-semibold text-gray-600 text-[11px] uppercase tracking-wide">Nama</th>
                            <th class="px-4 py-3 font-semibold text-gray-600 text-[11px] uppercase tracking-wide">Email</th>
                            <th class="px-4 py-3 font-semibold text-gray-600 text-[11px] uppercase tracking-wide">Role</th>
                            @if(auth()->user()->hasAnyRole(['super-admin', 'admin', 'admin-pmb']))
                            <th class="px-4 py-3 font-semibold text-gray-600 text-[11px] uppercase tracking-wide text-center">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($users as $user)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900 text-[13px]">{{ ucwords(strtolower($user->name)) }}</div>
                            </td>
                            <td class="px-4 py-3 text-gray-600 text-[13px]">{{ $user->email }}</td>
                            <td class="px-4 py-3">
                                @if($user->roles->count() > 0)
                                    @foreach($user->roles as $r)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium 
                                            {{ $r->name === 'super-admin' ? 'bg-purple-100 text-purple-800' : 
                                               ($r->name === 'admin-pmb' ? 'bg-blue-100 text-blue-800' : 
                                               ($r->name === 'pimpinan' ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-800')) }}">
                                            {{ $r->name }}
                                        </span>
                                    @endforeach
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-gray-100 text-gray-500">
                                        No Role
                                    </span>
                                @endif
                            </td>
                            @if(auth()->user()->hasAnyRole(['super-admin', 'admin', 'admin-pmb']))
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button wire:click="openEdit({{ $user->id }})" class="text-blue-600 hover:text-blue-900 border border-blue-200 hover:border-blue-400 bg-white px-2 py-1 rounded transition-colors text-[11px] font-medium shadow-sm">
                                        Edit
                                    </button>
                                    @if(auth()->id() !== $user->id)
                                    <button wire:confirm="Yakin ingin menghapus pengguna {{ ucwords(strtolower($user->name)) }}?" wire:click="deleteUser({{ $user->id }})" class="text-red-600 hover:text-red-900 border border-red-200 hover:border-red-400 bg-white px-2 py-1 rounded transition-colors text-[11px] font-medium shadow-sm">
                                        Hapus
                                    </button>
                                    @endif
                                </div>
                            </td>
                            @endif
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ auth()->user()->hasAnyRole(['super-admin', 'admin', 'admin-pmb']) ? '4' : '3' }}" class="px-4 py-8 text-center text-gray-500">
                                Tidak ada data pengguna yang cocok.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($users->hasPages())
            <div class="px-4 py-3 border-t border-gray-100 bg-white">
                {{ $users->links() }}
            </div>
            @endif
        </div>

    <!-- Modal CRUD -->
    @if($showModal)
    <div wire:key="user-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg mx-4 overflow-hidden transform transition-all" @click.stop>
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                <h2 class="text-lg font-bold text-gray-900">{{ $editMode ? 'Edit Pengguna' : 'Tambah Pengguna Baru' }}</h2>
                <button wire:click="$set('showModal', false)" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input wire:model="name" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow outline-none" placeholder="Masukkan nama lengkap">
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Alamat Email <span class="text-red-500">*</span></label>
                    <input wire:model="email" type="email" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow outline-none" placeholder="email@contoh.com">
                    @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Role/Hak Akses <span class="text-red-500">*</span></label>
                    <select wire:model="role" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow outline-none">
                        <option value="">-- Pilih Role --</option>
                        @foreach($roles as $r)
                        <option value="{{ $r->name }}">{{ $r->name }}</option>
                        @endforeach
                    </select>
                    @error('role') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kata Sandi {!! $editMode ? '<span class="text-gray-400 font-normal">(Kosongkan jika tidak ingin diubah)</span>' : '<span class="text-red-500">*</span>' !!}</label>
                    <input wire:model="password" type="password" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow outline-none" placeholder="Minimal 8 karakter">
                    @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex justify-end gap-3">
                <button wire:click="$set('showModal', false)" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors shadow-sm">
                    Batal
                </button>
                <button wire:click="save" class="px-4 py-2 text-sm font-medium text-white bg-blue-700 border border-transparent rounded-lg hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors shadow-sm inline-flex items-center">
                    <svg wire:loading wire:target="save" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Simpan
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
