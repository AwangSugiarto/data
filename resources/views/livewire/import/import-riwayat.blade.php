<div class="py-8 px-4 max-w-6xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Riwayat Import</h1>
            <p class="text-sm text-gray-500 mt-1">Semua batch import yang pernah dilakukan</p>
        </div>
        <a href="{{ route('import.wizard') }}" class="bg-blue-700 hover:bg-blue-800 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2 transition">
            ➕ Import Baru
        </a>
    </div>

    <div class="mb-4">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari nama file..."
            class="w-full md:w-80 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold text-gray-700">File</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Diupload oleh</th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-700">Status</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Catatan</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Waktu</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($batches as $b)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium text-gray-900">📄 {{ $b->nama_file_asal }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $b->uploader?->name ?? '-' }}</td>
                    <td class="px-4 py-3 text-center">
                        @php $statusMap = ['staged'=>['bg-gray-100','text-gray-600','⏳'],'validated'=>['bg-yellow-100','text-yellow-700','🔍'],'loaded'=>['bg-green-100','text-green-700','✅'],'failed'=>['bg-red-100','text-red-700','❌']]; [$bg,$tc,$icon] = $statusMap[$b->status] ?? ['bg-gray-100','text-gray-600','?']; @endphp
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $bg }} {{ $tc }}">{{ $icon }} {{ ucfirst($b->status) }}</span>
                    </td>
                    <td class="px-4 py-3 text-gray-500 text-xs max-w-xs truncate">{{ $b->catatan ?? '-' }}</td>
                    <td class="px-4 py-3 text-gray-400 text-xs">{{ $b->created_at->diffForHumans() }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-4 py-8 text-center text-gray-400">Belum ada riwayat import.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-100">{{ $batches->links() }}</div>
    </div>
</div>
