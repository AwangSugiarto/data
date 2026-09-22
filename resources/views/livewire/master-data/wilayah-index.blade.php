<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="sm:flex sm:items-center sm:justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Master Wilayah</h1>
            <p class="mt-2 text-sm text-gray-600">Telusuri data Provinsi, Kabupaten, Kecamatan, hingga Kelurahan.</p>
        </div>
    </div>

    <!-- Breadcrumbs -->
    @if(count($breadcrumbs) > 0)
    <nav class="flex mb-6 text-sm text-gray-600 space-x-2 bg-white px-4 py-3 rounded-lg border border-gray-200 shadow-sm">
        <button wire:click="goBackTo('propinsi')" class="hover:text-blue-600 hover:underline">Semua Provinsi</button>
        @foreach($breadcrumbs as $bc)
            <span class="text-gray-400">/</span>
            <button wire:click="goBackTo('{{ $bc['tab'] }}')" class="hover:text-blue-600 hover:underline font-medium text-gray-800">{{ $bc['label'] }}</button>
        @endforeach
    </nav>
    @endif

    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        @if (session()->has('message'))
            <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-4 mx-4 mt-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm leading-5 font-medium text-green-800">
                            {{ session('message') }}
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <div class="p-4 border-b border-gray-200 flex justify-between items-center bg-gray-50/50">
            <div class="relative max-w-sm w-full">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari {{ ucfirst($activeTab) }}..." class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition duration-150 ease-in-out">
            </div>
            
            @if($activeTab !== 'kelurahan')
            <button wire:click="openAddModal" class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm leading-5 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-500 focus:outline-none focus:border-blue-700 focus:shadow-outline-blue active:bg-blue-700 transition ease-in-out duration-150">
                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                Tambah {{ ucfirst($activeTab) }}
            </button>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-16">No</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-32">Kode</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            Nama {{ ucfirst($activeTab) }}
                        </th>
                        @if($activeTab !== 'kelurahan')
                        <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider w-40">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @php
                        $prefix = $activeTab === 'propinsi' ? 'prop' : substr($activeTab, 0, 3);
                    @endphp
                    @forelse ($data as $index => $item)
                    <tr class="hover:bg-blue-50/50 transition duration-150">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $data->firstItem() + $index }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-mono">
                            {{ $item->{'kode_' . $prefix} }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $item->{'nama_' . $prefix} }}</div>
                        </td>
                        @if($activeTab !== 'kelurahan')
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button wire:click="select{{ ucfirst($activeTab) }}('{{ $item->{'kode_' . $prefix} }}')" class="text-blue-600 hover:text-blue-900 bg-blue-50 hover:bg-blue-100 px-3 py-1 rounded-md transition flex items-center justify-center gap-1 ml-auto">
                                Lihat Detail
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                            </button>
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ $activeTab !== 'kelurahan' ? 4 : 3 }}" class="px-6 py-10 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada data</h3>
                            <p class="mt-1 text-sm text-gray-500">Silakan gunakan kata kunci pencarian yang lain.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($data->hasPages())
        <div class="bg-gray-50 px-4 py-3 border-t border-gray-200 sm:px-6">
            {{ $data->links() }}
        </div>
        @endif
    </div>

    <!-- Modal Tambah Wilayah -->
    @if($isAddModalOpen)
    <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto overflow-x-hidden bg-gray-900 bg-opacity-50 backdrop-blur-sm transition-opacity">
        <div class="relative p-4 w-full max-w-md h-full md:h-auto">
            <div class="relative bg-white rounded-2xl shadow-xl">
                <div class="flex justify-between items-center p-5 rounded-t-2xl border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-bold text-gray-900">
                        Tambah {{ ucfirst($activeTab) }} Baru
                    </h3>
                    <button wire:click="closeAddModal" type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-2 ml-auto inline-flex items-center">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>  
                    </button>
                </div>
                <form wire:submit.prevent="saveWilayah">
                    <div class="p-6 space-y-6">
                        @if($activeTab === 'kabupaten')
                            <div class="bg-blue-50 p-3 rounded-lg text-sm text-blue-800 mb-4">
                                Menambahkan Kabupaten ke dalam Provinsi aktif.
                            </div>
                        @elseif($activeTab === 'kecamatan')
                            <div class="bg-blue-50 p-3 rounded-lg text-sm text-blue-800 mb-4">
                                Menambahkan Kecamatan ke dalam Kabupaten aktif.
                            </div>
                        @endif

                        <div>
                            <label for="kode" class="block text-sm font-medium text-gray-700">Kode {{ ucfirst($activeTab) }}</label>
                            <input wire:model="newWilayahKode" type="text" id="kode" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="Contoh: 16" required>
                            @error('newWilayahKode') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Nama {{ ucfirst($activeTab) }}</label>
                            <input wire:model="newWilayahName" type="text" id="name" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="Contoh: JAKARTA SELATAN" required>
                            @error('newWilayahName') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="flex items-center justify-end p-5 rounded-b-2xl border-t border-gray-200 bg-gray-50 gap-2">
                        <button wire:click="closeAddModal" type="button" class="text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 focus:ring-4 focus:outline-none focus:ring-gray-200 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                            Batal
                        </button>
                        <button type="submit" class="text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
