<div class="py-12">
    <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
        
        <!-- Header & Cetak Button -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4 print:hidden">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Cetak Laporan PMB</h1>
                <p class="text-sm text-gray-500 mt-1">Rekapitulasi Daya Tampung, Pendaftar, Lulus, dan Registrasi per Program Studi.</p>
            </div>
            
            <div>
                <button onclick="window.print()" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Cetak Laporan
                </button>
            </div>
        </div>

        <!-- Filter Area -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6 print:hidden">
            <h3 class="text-sm font-semibold text-gray-800 mb-4 flex items-center gap-2">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                Filter Laporan
            </h3>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Tahun Akademik</label>
                    <select wire:model.live="tahunId" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                        <option value="">-- Semua Tahun --</option>
                        @foreach($tahunList as $t)
                        <option value="{{ $t->id }}">{{ $t->tahun }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Jalur Seleksi</label>
                    <select wire:model.live="jalurId" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                        <option value="">-- Semua Jalur --</option>
                        @foreach($jalurList as $j)
                        <option value="{{ $j->id }}">{{ $j->nama_jalur }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Fakultas</label>
                    <select wire:model.live="fakultasId" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                        <option value="">-- Semua Fakultas --</option>
                        @foreach($fakultasListDropdown as $f)
                        <option value="{{ $f->id }}">{{ $f->nama_fakultas }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Program Studi</label>
                    <select wire:model.live="prodiId" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none {{ empty($prodiList) ? 'bg-gray-100 text-gray-400' : '' }}" {{ empty($prodiList) ? 'disabled' : '' }}>
                        <option value="">-- Semua Prodi --</option>
                        @foreach($prodiList as $p)
                        <option value="{{ $p->id }}">{{ $p->nama_prodi }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- Print Title (Only visible when printing) -->
        <div class="hidden print:block mb-6 text-center">
            <h1 class="text-2xl font-bold text-gray-900">LAPORAN REKAPITULASI PENERIMAAN MAHASISWA BARU</h1>
            <p class="text-gray-600 mt-1">
                Tahun Akademik: {{ $tahunId ? $tahunList->firstWhere('id', $tahunId)->tahun : 'Semua Tahun' }} |
                Jalur Seleksi: {{ $jalurId ? $jalurList->firstWhere('id', $jalurId)->nama_jalur : 'Semua Jalur' }}
            </p>
            <p class="text-gray-500 text-sm mt-1">Dicetak pada: {{ now()->format('d F Y H:i') }}</p>
            <hr class="mt-4 border-2 border-gray-800">
        </div>

        <!-- Report Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden print:shadow-none print:border-none print:rounded-none">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left print:text-xs">
                    <thead class="bg-indigo-50 text-indigo-900 border-b-2 border-indigo-100 print:bg-gray-100 print:text-gray-900 print:border-gray-800">
                        <tr>
                            <th class="px-6 py-4 font-bold w-1/3">Program Studi</th>
                            <th class="px-6 py-4 font-bold text-center">Jenjang</th>
                            <th class="px-6 py-4 font-bold text-right text-blue-700 print:text-gray-900">Daya Tampung</th>
                            <th class="px-6 py-4 font-bold text-right text-orange-600 print:text-gray-900">Pendaftar</th>
                            <th class="px-6 py-4 font-bold text-right text-green-600 print:text-gray-900">Lulus</th>
                            <th class="px-6 py-4 font-bold text-right text-purple-700 print:text-gray-900">Registrasi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 print:divide-gray-400">
                        @forelse($laporanData as $fakultas)
                            <!-- Fakultas Group Header -->
                            <tr class="bg-gray-50 print:bg-gray-100 border-t-2 border-gray-200">
                                <td colspan="2" class="px-6 py-3 font-bold text-gray-800 uppercase tracking-wider text-xs">
                                    {{ $fakultas['nama_fakultas'] }}
                                </td>
                                <td class="px-6 py-3 font-bold text-right text-blue-700 print:text-gray-800">{{ number_format($fakultas['total_kuota'], 0, ',', '.') }}</td>
                                <td class="px-6 py-3 font-bold text-right text-orange-600 print:text-gray-800">{{ number_format($fakultas['total_daftar'], 0, ',', '.') }}</td>
                                <td class="px-6 py-3 font-bold text-right text-green-600 print:text-gray-800">{{ number_format($fakultas['total_lulus'], 0, ',', '.') }}</td>
                                <td class="px-6 py-3 font-bold text-right text-purple-700 print:text-gray-800">{{ number_format($fakultas['total_registrasi'], 0, ',', '.') }}</td>
                            </tr>
                            
                            <!-- Prodi Rows -->
                            @foreach($fakultas['prodi'] as $prodi)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-3 text-gray-700 pl-10">&bull; {{ $prodi['nama_prodi'] }}</td>
                                <td class="px-6 py-3 text-center text-gray-500">{{ $prodi['jenjang'] }}</td>
                                <td class="px-6 py-3 text-right text-gray-900">{{ number_format($prodi['kuota'], 0, ',', '.') }}</td>
                                <td class="px-6 py-3 text-right text-gray-900">{{ number_format($prodi['daftar'], 0, ',', '.') }}</td>
                                <td class="px-6 py-3 text-right text-gray-900">{{ number_format($prodi['lulus'], 0, ',', '.') }}</td>
                                <td class="px-6 py-3 text-right text-gray-900">{{ number_format($prodi['registrasi'], 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                    <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
                                    Tidak ada data yang tersedia untuk kriteria yang dipilih.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if(count($laporanData) > 0)
                    <tfoot class="bg-gray-800 text-white print:bg-gray-200 print:text-black">
                        <tr>
                            <td colspan="2" class="px-6 py-4 font-bold uppercase text-right">Total Keseluruhan</td>
                            <td class="px-6 py-4 font-bold text-right">{{ number_format(collect($laporanData)->sum('total_kuota'), 0, ',', '.') }}</td>
                            <td class="px-6 py-4 font-bold text-right">{{ number_format(collect($laporanData)->sum('total_daftar'), 0, ',', '.') }}</td>
                            <td class="px-6 py-4 font-bold text-right">{{ number_format(collect($laporanData)->sum('total_lulus'), 0, ',', '.') }}</td>
                            <td class="px-6 py-4 font-bold text-right">{{ number_format(collect($laporanData)->sum('total_registrasi'), 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
            
            <div class="px-6 py-4 text-xs text-gray-500 bg-gray-50 border-t border-gray-100 print:hidden">
                Menampilkan total {{ count($laporanData) }} Fakultas. Gunakan tombol "Cetak Laporan" untuk menyimpan sebagai PDF atau mencetak ke kertas.
            </div>
        </div>
    </div>
</div>
