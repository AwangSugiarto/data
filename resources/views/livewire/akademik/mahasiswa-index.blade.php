<div class="py-6 px-4 sm:px-6 lg:px-8 max-w-6xl mx-auto" x-data="{ activeMainTab: 'overview' }">

    {{-- Header --}}
    <div class="mb-5 flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">📈 Dashboard Akademik</h1>
            <p class="text-sm text-gray-500 mt-0.5">Rekapitulasi dan data mahasiswa berdasarkan status akademik.</p>
        </div>
        <div class="flex items-center gap-3">
            {{-- Filter tahun untuk infografis --}}
            <select wire:model.live="filterTahunId"
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none bg-white">
                <option value="">📅 Semua Tahun</option>
                @foreach($tahunList as $t)
                <option value="{{ $t->id }}">{{ $t->tahun }}</option>
                @endforeach
            </select>
            @if(auth()->user()->hasAnyRole(['super-admin', 'admin', 'admin-pmb']))
            <button wire:click="prosesAutoDo"
                wire:confirm="Proses auto Drop Out untuk mahasiswa > 14 semester? Tindakan ini akan mengubah status di database."
                wire:loading.attr="disabled"
                class="text-xs bg-red-50 text-red-600 hover:bg-red-100 border border-red-200 px-3 py-2 rounded-lg font-medium transition flex items-center gap-1.5">
                <span wire:loading.remove wire:target="prosesAutoDo">⚠️ Proses Auto-DO</span>
                <span wire:loading wire:target="prosesAutoDo">⏳ Memproses...</span>
            </button>
            @endif
        </div>
    </div>

    {{-- Flash --}}
    @if(session('success'))
    <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm flex items-center gap-2">
        ✅ {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm flex items-center gap-2">
        ⚠️ {{ session('error') }}
    </div>
    @endif

    {{-- ── Navigation Tabs ─────────────────────────────────────────────── --}}
    <div class="flex space-x-1 p-1.5 bg-slate-200/50 backdrop-blur-md rounded-2xl mb-6 w-max shadow-inner border border-slate-200/60">
        <button @click="activeMainTab = 'overview'; setTimeout(() => renderChartsWhenReady(), 100)" :class="{'bg-white shadow-md text-indigo-700 scale-100 font-semibold': activeMainTab === 'overview', 'text-slate-500 hover:text-slate-700 scale-95 font-medium': activeMainTab !== 'overview'}" class="px-6 py-2.5 rounded-xl text-sm transition-all duration-300">
            Ringkasan (Overview)
        </button>
        <button @click="activeMainTab = 'asing'; setTimeout(() => renderAsingChartsFromDOM(), 100)" :class="{'bg-white shadow-md text-indigo-700 scale-100 font-semibold': activeMainTab === 'asing', 'text-slate-500 hover:text-slate-700 scale-95 font-medium': activeMainTab !== 'asing'}" class="px-6 py-2.5 rounded-xl text-sm transition-all duration-300">
            Mahasiswa Asing
        </button>
        <button @click="activeMainTab = 'tabel'" :class="{'bg-white shadow-md text-indigo-700 scale-100 font-semibold': activeMainTab === 'tabel', 'text-slate-500 hover:text-slate-700 scale-95 font-medium': activeMainTab !== 'tabel'}" class="px-6 py-2.5 rounded-xl text-sm transition-all duration-300">
            Data Mahasiswa
        </button>
    </div>

    {{-- ── TAB 1: OVERVIEW ─────────────────────────────────────────────── --}}
    <div x-show="activeMainTab === 'overview'" x-transition.opacity>

    {{-- ══════════════════ STAT CARDS ══════════════════ --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-4 gap-4 mb-6">
        @php
        $cards = [
            ['label' => 'Mahasiswa Aktif',  'value' => $stats['aktif'],    'color' => 'green',  'icon' => '🎓', 'bg' => 'bg-green-50',  'text' => 'text-green-700',  'border' => 'border-green-100'],
            ['label' => 'Stop Out',         'value' => $stats['stop_out'], 'color' => 'amber',  'icon' => '⏸️', 'bg' => 'bg-amber-50',  'text' => 'text-amber-700',  'border' => 'border-amber-100'],
            ['label' => 'Drop Out',         'value' => $stats['drop_out'], 'color' => 'red',    'icon' => '❌', 'bg' => 'bg-red-50',    'text' => 'text-red-700',    'border' => 'border-red-100'],
            ['label' => 'Lulus / Alumni',   'value' => $stats['lulus'],    'color' => 'blue',   'icon' => '🏆', 'bg' => 'bg-blue-50',   'text' => 'text-blue-700',   'border' => 'border-blue-100'],
            ['label' => 'Mengundurkan Diri','value' => $stats['undur_diri'],'color' => 'purple', 'icon' => '🚪', 'bg' => 'bg-purple-50', 'text' => 'text-purple-700', 'border' => 'border-purple-100'],
            ['label' => 'Meninggal Dunia',  'value' => $stats['meninggal'], 'color' => 'gray',   'icon' => '⚰️', 'bg' => 'bg-gray-100',  'text' => 'text-gray-700',   'border' => 'border-gray-200'],
            ['label' => 'Total Mahasiswa',  'value' => $stats['total'],    'color' => 'indigo', 'icon' => '👥', 'bg' => 'bg-indigo-50', 'text' => 'text-indigo-700', 'border' => 'border-indigo-100'],
        ];
        @endphp
        @foreach($cards as $card)
        <div class="bg-white rounded-xl border {{ $card['border'] }} shadow-sm p-4 flex flex-col gap-1">
            <div class="flex items-center justify-between">
                <span class="text-xl">{{ $card['icon'] }}</span>
                <span class="text-2xl font-bold {{ $card['text'] }}">{{ number_format($card['value']) }}</span>
            </div>
            <p class="text-xs font-medium text-gray-500 mt-1">{{ $card['label'] }}</p>
            @if($stats['total'] > 0)
            <div class="w-full bg-gray-100 rounded-full h-1.5 mt-1">
                <div class="{{ $card['bg'] }} h-1.5 rounded-full transition-all duration-500"
                    style="width: {{ $stats['total'] > 0 ? round($card['value'] / $stats['total'] * 100) : 0 }}%; background-color: var(--color); filter: brightness(0.8);">
                </div>
            </div>
            <p class="text-xs text-gray-400">{{ $stats['total'] > 0 ? round($card['value'] / $stats['total'] * 100, 1) : 0 }}%</p>
            @endif
        </div>
        @endforeach
    </div>

    {{-- ══════════════════ CHARTS ══════════════════ --}}
    <div id="mhs-chart-data"
        data-per-prodi='@json($perProdi)'
        data-status-dist='@json($statusDist)'
        data-tren-tahunan='@json($trenTahunan)'
        data-komposisi-jalur='@json($komposisiJalur)'
        style="display:none"></div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-8">
        {{-- Bar chart: per Prodi --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <h3 class="text-xs font-semibold text-gray-700 mb-3">📊 Mahasiswa Aktif per Prodi</h3>
            @if($perProdi->isEmpty())
            <div class="flex items-center justify-center h-48 text-gray-400 text-xs">Belum ada data.</div>
            @else
            <div class="relative h-48">
                <canvas id="chartProdi"></canvas>
            </div>
            @endif
        </div>

        {{-- Donut: per Status --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <h3 class="text-xs font-semibold text-gray-700 mb-3">🍩 Distribusi Status</h3>
            @if($stats['total'] === 0)
            <div class="flex items-center justify-center h-48 text-gray-400 text-xs">Belum ada data.</div>
            @else
            <div class="relative h-48">
                <canvas id="chartStatus"></canvas>
            </div>
            @endif
        </div>

        {{-- Tren Tahunan --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <h3 class="text-xs font-semibold text-gray-700 mb-3">📈 Tren Tahunan</h3>
            <div class="relative h-48">
                <canvas id="chartTrenTahunan"></canvas>
            </div>
        </div>

        {{-- Komposisi Jalur --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <h3 class="text-xs font-semibold text-gray-700 mb-3">🛤️ Komposisi Jalur</h3>
            <div class="relative h-48">
                <canvas id="chartKomposisiJalur"></canvas>
            </div>
        </div>
    </div>

    </div>

    {{-- ── TAB 2: MAHASISWA ASING ──────────────────────────────────────── --}}
    <div x-show="activeMainTab === 'asing'" x-transition.opacity style="display: none;">

    {{-- ══════════════════ MAHASISWA ASING ══════════════════ --}}
    <div class="mb-8">
        <div class="flex items-center gap-2 mb-4">
            <span class="text-xl">🌏</span>
            <div>
                <h2 class="text-base font-bold text-gray-800">Mahasiswa Asing</h2>
                <p class="text-xs text-gray-500">Mahasiswa dengan kewarganegaraan non-Indonesia</p>
            </div>
        </div>

        {{-- KPI Asing --}}
        <div class="grid gap-3 mb-5" style="grid-template-columns: repeat(3, minmax(0, 1fr));">
            @php
            $asingCards = [
                ['label'=>'Total Mahasiswa Asing', 'value'=>number_format($statsAsing['total']), 'icon'=>'🌍', 'text'=>'text-indigo-700', 'border'=>'border-indigo-100'],
                ['label'=>'Aktif saat ini',         'value'=>number_format($statsAsing['aktif']), 'icon'=>'✅', 'text'=>'text-green-700',  'border'=>'border-green-100'],
                ['label'=>'Asal Negara',            'value'=>number_format($statsAsing['negara']),'icon'=>'🗺️', 'text'=>'text-blue-700',   'border'=>'border-blue-100'],
            ];
            @endphp
            @foreach($asingCards as $ac)
            <div class="bg-white rounded-xl border {{ $ac['border'] }} shadow-sm p-3 flex flex-col gap-1">
                <div class="flex items-center justify-between">
                    <span class="text-lg">{{ $ac['icon'] }}</span>
                    <span class="text-xl font-bold {{ $ac['text'] }}">{{ $ac['value'] }}</span>
                </div>
                <p class="text-xs font-medium text-gray-500 mt-0.5">{{ $ac['label'] }}</p>
            </div>
            @endforeach
        </div>

        @if($statsAsing['total'] === 0)
        <div class="bg-white rounded-xl border border-dashed border-gray-200 p-10 text-center">
            <div class="text-4xl mb-3">🌏</div>
            <p class="text-gray-500 font-medium">Belum ada data mahasiswa asing</p>
            <p class="text-gray-400 text-sm mt-1">Data akan tampil setelah mahasiswa dengan kewarganegaraan non-Indonesia diimport ke sistem.</p>
        </div>
        @else
        <div id="asing-chart-data"
            data-per-negara='@json($asingPerNegara)'
            data-per-prodi='@json($asingPerProdi)'
            data-per-jalur='@json($asingPerJalur)'
            data-per-tahun='@json($asingPerTahun)'
            style="display:none"></div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-5">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                <h3 class="text-xs font-semibold text-gray-700 mb-3">🗺️ Sebaran Asal Negara</h3>
                <div class="relative h-48">
                    <canvas id="chartAsingNegara"></canvas>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4" x-data="{ tab: 'S1' }">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-xs font-semibold text-gray-700">🎓 Sebaran per Program Studi (Top 10)</h3>
                    <div class="flex space-x-1 bg-gray-100 p-0.5 rounded-lg">
                        <button @click="tab = 'S1'; window.renderAsingProdiChart('S1')" :class="tab === 'S1' ? 'bg-white shadow-sm text-gray-800' : 'text-gray-500 hover:text-gray-700'" class="px-2 py-1 text-[10px] font-medium rounded-md transition">S1</button>
                        <button @click="tab = 'S2'; window.renderAsingProdiChart('S2')" :class="tab === 'S2' ? 'bg-white shadow-sm text-gray-800' : 'text-gray-500 hover:text-gray-700'" class="px-2 py-1 text-[10px] font-medium rounded-md transition">S2</button>
                        <button @click="tab = 'S3'; window.renderAsingProdiChart('S3')" :class="tab === 'S3' ? 'bg-white shadow-sm text-gray-800' : 'text-gray-500 hover:text-gray-700'" class="px-2 py-1 text-[10px] font-medium rounded-md transition">S3</button>
                    </div>
                </div>
                <div class="relative h-48">
                    <canvas id="chartAsingProdi"></canvas>
                </div>
            </div>
        </div>
        <div class="grid grid-cols-1 gap-5">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                <h3 class="text-xs font-semibold text-gray-700 mb-3">📈 Tren Mahasiswa Asing per Tahun Masuk</h3>
                <div class="relative h-48">
                    <canvas id="chartAsingTahun"></canvas>
                </div>
            </div>
        </div>
        @endif
    </div>
    </div>

    {{-- ── TAB 3: TABEL MAHASISWA ──────────────────────────────────────── --}}
    <div x-show="activeMainTab === 'tabel'" x-transition.opacity style="display: none;">

    {{-- ══════════════════ TABEL MAHASISWA ══════════════════ --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        {{-- Toolbar --}}
        <div class="p-4 border-b border-gray-100 flex flex-wrap gap-3 items-center">
            <input wire:model.live.debounce.300ms="search" type="text"
                placeholder="🔍 Cari nama / NIM..."
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none w-56">

            <select wire:model.live="filterTahunTbl"
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                <option value="">Semua Tahun Masuk</option>
                @foreach($tahunList as $t)
                <option value="{{ $t->id }}">{{ $t->tahun }}</option>
                @endforeach
            </select>

            <select wire:model.live="filterStatus"
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                <option value="">Semua Status</option>
                @foreach($statusOptions as $s)
                <option value="{{ $s->kode_status }}">{{ $s->nama_status }}</option>
                @endforeach
            </select>

            <select wire:model.live="filterProdiId"
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none max-w-xs truncate">
                <option value="">Semua Prodi</option>
                @foreach($prodiList as $p)
                <option value="{{ $p->id }}">{{ $p->kode_prodi }} — {{ Str::limit($p->nama_prodi, 35) }}</option>
                @endforeach
            </select>

            <select wire:model.live="filterWarganegara"
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                <option value="">Semua Kewarganegaraan</option>
                <option value="lokal">WNI (Indonesia)</option>
                <option value="asing">WNA (Internasional)</option>
            </select>

            <div class="flex items-center gap-2 ml-auto">
                <span class="text-xs text-gray-400">Baris:</span>
                <select wire:model.live="perPage"
                    class="border border-gray-300 rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <option value="15">15</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </select>
            </div>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-gray-700">
                <thead class="bg-gray-50 border-b border-gray-100 text-xs uppercase text-gray-400">
                    @php
                    $sortIcon = fn(string $col) => $sortBy === $col
                        ? ($sortDirection === 'asc' ? '↑' : '↓')
                        : '↕';
                    $sortClass = fn(string $col) => 'cursor-pointer select-none hover:text-gray-700 transition '
                        . ($sortBy === $col ? 'text-blue-600 font-semibold' : '');
                    @endphp
                    <tr>
                        <th class="px-4 py-3 text-center w-10">No</th>
                        <th class="px-4 py-3 text-left {{ $sortClass('nim') }}"
                            wire:click="sort('nim')">
                            NIM <span class="ml-0.5 opacity-60">{{ $sortIcon('nim') }}</span>
                        </th>
                        <th class="px-4 py-3 text-left {{ $sortClass('nama') }}"
                            wire:click="sort('nama')">
                            Nama Mahasiswa <span class="ml-0.5 opacity-60">{{ $sortIcon('nama') }}</span>
                        </th>
                        <th class="px-4 py-3 text-left {{ $sortClass('program_studi') }}"
                            wire:click="sort('program_studi')">
                            Program Studi <span class="ml-0.5 opacity-60">{{ $sortIcon('program_studi') }}</span>
                        </th>
                        <th class="px-4 py-3 text-center {{ $sortClass('tahun_masuk') }}"
                            wire:click="sort('tahun_masuk')">
                            Thn Masuk <span class="ml-0.5 opacity-60">{{ $sortIcon('tahun_masuk') }}</span>
                        </th>
                        <th class="px-4 py-3 text-center {{ $sortClass('semester') }}"
                            wire:click="sort('semester')">
                            Semester <span class="ml-0.5 opacity-60">{{ $sortIcon('semester') }}</span>
                        </th>
                        <th class="px-4 py-3 text-left {{ $sortClass('kelompok_ukt') }}"
                            wire:click="sort('kelompok_ukt')">
                            UKT <span class="ml-0.5 opacity-60">{{ $sortIcon('kelompok_ukt') }}</span>
                        </th>
                        <th class="px-4 py-3 text-right {{ $sortClass('nominal_ukt') }}"
                            wire:click="sort('nominal_ukt')">
                            Nominal UKT <span class="ml-0.5 opacity-60">{{ $sortIcon('nominal_ukt') }}</span>
                        </th>
                        <th class="px-4 py-3 text-center {{ $sortClass('status_akademik') }}"
                            wire:click="sort('status_akademik')">
                            Status <span class="ml-0.5 opacity-60">{{ $sortIcon('status_akademik') }}</span>
                        </th>
                        @if(auth()->user()->hasAnyRole(['super-admin', 'admin', 'admin-pmb']))
                        <th class="px-4 py-3 text-right">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($mahasiswaList as $mhs)
                    @php
                        $statusColor = match($mhs->status_akademik) {
                            'Aktif'               => 'bg-green-100 text-green-700',
                            'Stop Out'            => 'bg-amber-100 text-amber-700',
                            'Drop Out'            => 'bg-red-100 text-red-700',
                            'Lulus', 'Alumni'     => 'bg-blue-100 text-blue-700',
                            'Mengundurkan Diri'   => 'bg-purple-100 text-purple-700',
                            default               => 'bg-gray-100 text-gray-600',
                        };
                    @endphp
                    <tr class="hover:bg-gray-50 transition {{ $mhs->over_semester ? 'bg-red-50/40' : '' }}">
                        <td class="px-4 py-3 text-center text-xs text-gray-400">
                            {{ $mahasiswaList->firstItem() + $loop->index }}
                        </td>
                        <td class="px-4 py-3 font-mono text-xs font-semibold text-gray-700">{{ $mhs->nim }}</td>
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-900">{{ ucwords(strtolower($mhs->calonMahasiswa?->nama ?? '-')) }}</p>
                        </td>
                        <td class="px-4 py-3 text-xs">
                            <p class="font-medium">{{ $mhs->calonMahasiswa?->programStudi?->kode_prodi ?? '-' }}</p>
                            <p class="text-gray-400">{{ Str::limit($mhs->calonMahasiswa?->programStudi?->nama_prodi ?? '', 30) }}</p>
                        </td>
                        <td class="px-4 py-3 text-center text-xs">{{ $mhs->tahunMasuk?->tahun ?? '-' }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($mhs->semester_ke !== null)
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold
                                {{ $mhs->over_semester ? 'bg-red-100 text-red-700 ring-1 ring-red-300' : 'bg-gray-100 text-gray-600' }}">
                                Smt {{ $mhs->semester_ke }}
                                @if($mhs->over_semester) ⚠️ @endif
                            </span>
                            @else
                            <span class="text-gray-300 text-xs">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-500">
                            {{ $mhs->kelompokUkt ? 'UKT '.$mhs->kelompokUkt->kelompok : '—' }}
                        </td>
                        <td class="px-4 py-3 text-right text-xs">
                            @if($mhs->nominal_ukt !== null)
                            <span class="font-semibold text-gray-800">
                                Rp {{ number_format($mhs->nominal_ukt, 0, ',', '.') }}
                            </span>
                            @else
                            <span class="text-gray-300">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2.5 py-1 rounded-full text-xs font-medium {{ $statusColor }}">
                                {{ $mhs->status_akademik }}
                            </span>
                        </td>
                        @if(auth()->user()->hasAnyRole(['super-admin', 'admin', 'admin-pmb']))
                        <td class="px-4 py-3 text-right">
                            <div class="flex justify-end gap-1 flex-wrap">
                                @if($mhs->status_akademik !== 'Aktif')
                                <button wire:click="updateStatus({{ $mhs->id }}, 'Aktif')"
                                    class="text-xs border border-green-200 text-green-600 hover:bg-green-600 hover:text-white px-2 py-1 rounded transition">Aktif</button>
                                @endif
                                @if(!in_array($mhs->status_akademik, ['Stop Out','Lulus','Alumni']))
                                <button wire:click="updateStatus({{ $mhs->id }}, 'Stop Out')"
                                    class="text-xs border border-amber-200 text-amber-600 hover:bg-amber-500 hover:text-white px-2 py-1 rounded transition">SO</button>
                                @endif
                                @if($mhs->status_akademik !== 'Drop Out')
                                <button wire:click="updateStatus({{ $mhs->id }}, 'Drop Out')"
                                    wire:confirm="Ubah status menjadi Drop Out?"
                                    class="text-xs border border-red-200 text-red-600 hover:bg-red-600 hover:text-white px-2 py-1 rounded transition">DO</button>
                                @endif
                                @if(!in_array($mhs->status_akademik, ['Lulus','Alumni']))
                                <button wire:click="updateStatus({{ $mhs->id }}, 'Lulus')"
                                    wire:confirm="Tandai mahasiswa ini sebagai Lulus?"
                                    class="text-xs border border-blue-200 text-blue-600 hover:bg-blue-600 hover:text-white px-2 py-1 rounded transition">Lulus</button>
                                @endif
                            </div>
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ auth()->user()->hasAnyRole(['super-admin', 'admin', 'admin-pmb']) ? '10' : '9' }}" class="py-16 text-center text-gray-400">
                            <p class="text-4xl mb-2">📭</p>
                            <p class="text-sm">Belum ada data mahasiswa.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="p-4 border-t border-gray-100 flex items-center justify-between">
            <p class="text-xs text-gray-400">
                Menampilkan {{ $mahasiswaList->firstItem() ?? 0 }}–{{ $mahasiswaList->lastItem() ?? 0 }}
                dari {{ number_format($mahasiswaList->total()) }} mahasiswa
            </p>
            {{ $mahasiswaList->links() }}
        </div>
    </div>
    </div>

</div>

{{-- Chart.js --}}
<script>
// Helper: baca data chart dari DOM attribute yang di-embed server
function renderChartsFromDOM() {
    const dataEl = document.getElementById('mhs-chart-data');
    if (!dataEl) return;
    const perProdi   = JSON.parse(dataEl.dataset.perProdi   || '{}');
    const statusDist = JSON.parse(dataEl.dataset.statusDist || '[]');
    const trenTahunan = JSON.parse(dataEl.dataset.trenTahunan || '{}');
    const komposisiJalur = JSON.parse(dataEl.dataset.komposisiJalur || '{}');
    renderCharts(perProdi, statusDist, trenTahunan, komposisiJalur);
}

function renderCharts(perProdi, statusDist, trenTahunan, komposisiJalur) {
    // Palette warna agar bar chart tidak monoton
    const barPalette = [
        '#3B82F6', '#10B981', '#F59E0B', '#8B5CF6', '#EF4444',
        '#06B6D4', '#EC4899', '#F97316', '#84CC16', '#6366F1',
        '#14B8A6', '#D946EF', '#Eab308', '#F43F5E', '#A855F7'
    ];

    // ── Bar chart: per Prodi ─────────────────────────────────────────────
    const prodiEl = document.getElementById('chartProdi');
    if (prodiEl) {
        const oldProdi = Chart.getChart(prodiEl);
        if (oldProdi) oldProdi.destroy();

        const labels = Object.keys(perProdi);
        const values = Object.values(perProdi);

        if (labels.length > 0) {
            new Chart(prodiEl, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Mahasiswa Aktif',
                        data: values,
                        backgroundColor: barPalette.slice(0, labels.length).map(c => c + 'CC'),
                        borderColor: barPalette.slice(0, labels.length),
                        borderWidth: 1,
                        borderRadius: 4,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y',
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.04)' }, ticks: { font: { size: 9 } } },
                        y: { ticks: { font: { size: 9 } } }
                    }
                }
            });
        }
    }

    // ── Donut chart: per Status ──────────────────────────────────────────
    const statusEl = document.getElementById('chartStatus');
    if (statusEl) {
        const oldStatus = Chart.getChart(statusEl);
        if (oldStatus) oldStatus.destroy();

        const nonZero = statusDist.filter(d => d[1] > 0);
        if (nonZero.length > 0) {
            new Chart(statusEl, {
                type: 'doughnut',
                data: {
                    labels: nonZero.map(d => d[0]),
                    datasets: [{
                        data: nonZero.map(d => d[1]),
                        backgroundColor: nonZero.map(d => d[2]),
                        borderWidth: 2,
                        borderColor: '#fff',
                        hoverOffset: 4,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'right', labels: { font: { size: 9 }, boxWidth: 10 } },
                    }
                }
            });
        }
    }

    // ── Chart 3: Tren Tahunan ─────────────────────────────────────────────
    const trenEl = document.getElementById('chartTrenTahunan');
    if (trenEl && trenTahunan.labels) {
        const oldTren = Chart.getChart(trenEl);
        if (oldTren) oldTren.destroy();

        new Chart(trenEl, {
            type: 'line',
            data: {
                labels: trenTahunan.labels,
                datasets: [
                    { label: 'Aktif', data: trenTahunan.aktif, borderColor: '#10B981', backgroundColor: 'rgba(16,185,129,0.1)', fill: true, tension: 0.3 },
                    { label: 'Lulus', data: trenTahunan.lulus, borderColor: '#3B82F6', backgroundColor: 'rgba(59,130,246,0.1)', fill: true, tension: 0.3 },
                    { label: 'Drop Out', data: trenTahunan.do, borderColor: '#EF4444', backgroundColor: 'rgba(239,68,68,0.1)', fill: true, tension: 0.3 },
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'top', labels: { font: { size: 9 }, boxWidth: 10 } } },
                scales: {
                    x: { ticks: { font: { size: 9 } } },
                    y: { beginAtZero: true, ticks: { font: { size: 9 } } }
                }
            }
        });
    }

    // ── Chart 4: Komposisi Jalur (Donut) ──────────────────────────────────
    const jalurEl = document.getElementById('chartKomposisiJalur');
    if (jalurEl && komposisiJalur.labels) {
        const oldJalur = Chart.getChart(jalurEl);
        if (oldJalur) oldJalur.destroy();

        if (komposisiJalur.labels.length > 0) {
            new Chart(jalurEl, {
                type: 'doughnut',
                data: {
                    labels: komposisiJalur.labels,
                    datasets: [{
                        data: komposisiJalur.data,
                        backgroundColor: barPalette.slice(0, komposisiJalur.labels.length).map(c => c + 'CC'),
                        borderColor: barPalette.slice(0, komposisiJalur.labels.length),
                        borderWidth: 1,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'right', labels: { font: { size: 9 }, boxWidth: 10 } }
                    }
                }
            });
        }
    }
}

// ── Render via DOM data (tidak bergantung pada timing event) ─────────────
function renderChartsWhenReady() {
    requestAnimationFrame(() => {
        renderChartsFromDOM();
    });
}

// Selalu render dari #mhs-chart-data setiap kali navigasi selesai
document.addEventListener('livewire:navigated', renderChartsWhenReady);

// Backup: filter berubah → Livewire dispatch chartsUpdated → re-render
window.addEventListener('chartsUpdated', event => {
    const d = event.detail;
    const payload = Array.isArray(d) ? d[0] : d;
    requestAnimationFrame(() => {
        renderCharts(payload.perProdi ?? {}, payload.statusDist ?? [], payload.trenTahunan ?? {}, payload.komposisiJalur ?? {});
        renderAsingCharts(
            payload.asingPerNegara ?? {},
            payload.asingPerProdi  ?? {},
            payload.asingPerJalur  ?? {},
            payload.asingPerTahun  ?? {}
        );
    });
});

// ── Mahasiswa Asing Charts ────────────────────────────────────────────────
function renderAsingChartsFromDOM() {
    const el = document.getElementById('asing-chart-data');
    if (!el) return;
    renderAsingCharts(
        JSON.parse(el.dataset.perNegara || '{}'),
        JSON.parse(el.dataset.perProdi  || '{}'),
        JSON.parse(el.dataset.perJalur  || '{}'),
        JSON.parse(el.dataset.perTahun  || '{}')
    );
}

function renderAsingCharts(perNegara, perProdi, perJalur, perTahun) {
    const palette = [
        '#6366F1','#8B5CF6','#EC4899','#EF4444','#F59E0B',
        '#10B981','#3B82F6','#14B8A6','#F97316','#84CC16',
        '#06B6D4','#A855F7','#E11D48','#D97706','#059669'
    ];

    // Doughnut: Per Negara
    const negaraEl = document.getElementById('chartAsingNegara');
    if (negaraEl && Object.keys(perNegara).length) {
        const old = Chart.getChart(negaraEl); if (old) old.destroy();
        new Chart(negaraEl, {
            type: 'doughnut',
            data: {
                labels: Object.keys(perNegara),
                datasets: [{ data: Object.values(perNegara), backgroundColor: palette, borderWidth: 2 }]
            },
            options: { 
                responsive: true, 
                maintainAspectRatio: false,
                plugins: { legend: { position: 'right', labels: { font: { size: 9 }, boxWidth: 10 } } } 
            }
        });
    }

    // Horizontal Bar: Per Prodi
    window.asingProdiData = perProdi;
    if(!window.currentAsingProdiTab) window.currentAsingProdiTab = 'S1';
    window.renderAsingProdiChart = function(tab) {
        window.currentAsingProdiTab = tab;
        const prodiEl = document.getElementById('chartAsingProdi');
        if (!prodiEl) return;
        
        const old = Chart.getChart(prodiEl); if (old) old.destroy();
        
        const dataForTab = window.asingProdiData[tab] || {};
        const prodiLabels = Object.keys(dataForTab);
        
        new Chart(prodiEl, {
            type: 'bar',
            data: {
                labels: prodiLabels,
                datasets: [{ 
                    label: 'Mahasiswa Asing', 
                    data: Object.values(dataForTab),
                    backgroundColor: palette.slice(0, prodiLabels.length).map(c => c + 'CC'), 
                    borderColor: palette.slice(0, prodiLabels.length),
                    borderWidth: 1, 
                    borderRadius: 4 
                }]
            },
            options: {
                responsive: true, 
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: { legend: { display: false } },
                scales: {
                    x: { beginAtZero: true, ticks: { font: { size: 9 }, stepSize: 1 }, grid: { color: 'rgba(0,0,0,0.04)' } },
                    y: { ticks: { font: { size: 9 } } }
                }
            }
        });
    };
    window.renderAsingProdiChart(window.currentAsingProdiTab);

    // Line: Tren per Tahun
    const tahunEl = document.getElementById('chartAsingTahun');
    if (tahunEl && Object.keys(perTahun).length) {
        const old = Chart.getChart(tahunEl); if (old) old.destroy();
        new Chart(tahunEl, {
            type: 'line',
            data: {
                labels: Object.keys(perTahun),
                datasets: [{ label: 'Mahasiswa Asing', data: Object.values(perTahun),
                    borderColor: '#6366F1', backgroundColor: 'rgba(99,102,241,0.1)',
                    fill: true, tension: 0.4, pointRadius: 5, pointBackgroundColor: '#6366F1' }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { ticks: { font: { size: 9 } } },
                    y: { beginAtZero: true, ticks: { precision: 0, font: { size: 9 } } }
                }
            }
        });
    }
}

// Render saat script ini dieksekusi (SPA navigation & full page load)
renderChartsWhenReady();
renderAsingChartsFromDOM();
</script>
