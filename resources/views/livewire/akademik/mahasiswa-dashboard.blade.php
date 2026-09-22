{{-- ═══════════════════════════════════════════════════════════════════════
     Dashboard Akademik — 4 Tab: Overview | Profil | Wilayah | UKT
     ═══════════════════════════════════════════════════════════════════════ --}}

<script>
function dashboardAkademik(donutData, prodiData, angkatanData, genderData) {
    return {
        charts: {},
        donut: donutData, prodi: prodiData, angkatan: angkatanData, gender: genderData,
        activeTab: 'overview',

        init() {
            this.$nextTick(() => this.renderTab(this.activeTab));
            this._navHandler = () => {
                const el = document.getElementById('akd-chart-data');
                if (el) {
                    this.donut    = JSON.parse(el.dataset.chartDonut    || '{}');
                    this.prodi    = JSON.parse(el.dataset.chartProdi    || '{}');
                    this.angkatan = JSON.parse(el.dataset.chartAngkatan || '{}');
                    this.gender   = JSON.parse(el.dataset.chartGender   || '{}');
                }
                this.destroyAll();
                this.$nextTick(() => this.renderTab(this.activeTab));
            };
            document.addEventListener('livewire:navigated', this._navHandler);
        },

        destroy() {
            if (this._navHandler) document.removeEventListener('livewire:navigated', this._navHandler);
            this.destroyAll();
        },

        destroyAll() { Object.values(this.charts).forEach(c => c?.destroy()); this.charts = {}; },

        refresh(detail) {
            if (detail?.chartDonut)    this.donut    = detail.chartDonut;
            if (detail?.chartProdi)    this.prodi    = detail.chartProdi;
            if (detail?.chartAngkatan) this.angkatan = detail.chartAngkatan;
            if (detail?.chartGender)   this.gender   = detail.chartGender;
            this.destroyAll();
            this.$nextTick(() => this.renderTab(this.activeTab));
        },

        switchTab(tab) {
            this.activeTab = tab;
            setTimeout(() => this.renderTab(tab), 120);
        },

        renderTab(tab) {
            if (tab === 'overview') { this.renderDonut(); this.renderProdi(); this.renderAngkatan(); }
            else if (tab === 'profil') { this.renderGender(); }
        },

        renderDonut() {
            const ctx = document.getElementById('chartStatusDonut');
            if (!ctx || !this.donut?.labels?.length) return;
            const old = Chart.getChart(ctx); if (old) old.destroy();
            this.charts.donut = new Chart(ctx, {
                type: 'doughnut',
                data: { labels: this.donut.labels, datasets: [{ data: this.donut.data, backgroundColor: this.donut.bgColors, borderWidth: 0, hoverOffset: 8 }] },
                options: { responsive: true, maintainAspectRatio: false, cutout: '72%',
                    plugins: { legend: { position: 'bottom', labels: { font: { size: 11 }, padding: 12 } },
                               tooltip: { callbacks: { label: c => ` ${c.label}: ${c.formattedValue} mhs` } } } }
            });
        },

        renderProdi() {
            const ctx = document.getElementById('chartProdi');
            if (!ctx || !this.prodi?.labels?.length) return;
            const old = Chart.getChart(ctx); if (old) old.destroy();
            this.charts.prodi = new Chart(ctx, {
                type: 'bar',
                data: { labels: this.prodi.labels, datasets: [{ label: 'Jumlah', data: this.prodi.data,
                    backgroundColor: 'rgba(99,102,241,0.25)', borderColor: '#6366F1', borderWidth: 2, borderRadius: 4 }] },
                options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { x: { beginAtZero: true, grid: { color: '#f3f4f6' } }, y: { ticks: { font: { size: 10 } } } } }
            });
        },

        renderAngkatan() {
            const ctx = document.getElementById('chartAngkatan');
            if (!ctx || !this.angkatan?.labels?.length) return;
            const old = Chart.getChart(ctx); if (old) old.destroy();
            this.charts.angkatan = new Chart(ctx, {
                type: 'bar',
                data: { labels: this.angkatan.labels, datasets: [
                    { label: 'Aktif',    data: this.angkatan.aktif,    backgroundColor: 'rgba(34,197,94,0.3)',  borderColor: '#22C55E', borderWidth:2, borderRadius:3 },
                    { label: 'Stop Out', data: this.angkatan.stop_out, backgroundColor: 'rgba(245,158,11,0.3)', borderColor: '#F59E0B', borderWidth:2, borderRadius:3 },
                    { label: 'Drop Out', data: this.angkatan.drop_out, backgroundColor: 'rgba(239,68,68,0.3)',  borderColor: '#EF4444', borderWidth:2, borderRadius:3 },
                    { label: 'Lulus',    data: this.angkatan.lulus,    backgroundColor: 'rgba(59,130,246,0.3)', borderColor: '#3B82F6', borderWidth:2, borderRadius:3 },
                    { label: 'Lainnya',  data: this.angkatan.lainnya,  backgroundColor: 'rgba(139,92,246,0.3)', borderColor: '#8B5CF6', borderWidth:2, borderRadius:3 },
                ] },
                options: { responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { position: 'top', labels: { font: { size: 11 }, padding: 12 } } },
                    scales: { x: { stacked: false, grid: { display: false } }, y: { beginAtZero: true } } }
            });
        },

        renderGender() {
            const ctx = document.getElementById('chartGender');
            if (!ctx || !this.gender?.labels?.length) return;
            const old = Chart.getChart(ctx); if (old) old.destroy();
            this.charts.gender = new Chart(ctx, {
                type: 'doughnut',
                data: { labels: this.gender.labels, datasets: [{ data: this.gender.data, backgroundColor: this.gender.bgColors, borderWidth: 0, hoverOffset: 8 }] },
                options: { responsive: true, maintainAspectRatio: false, cutout: '68%',
                    plugins: { legend: { position: 'bottom', labels: { font: { size: 12 }, padding: 14 } } } }
            });
        },
    };
}
</script>

<div class="py-8 px-4 sm:px-6 lg:px-8 max-w-6xl mx-auto"
    x-data='dashboardAkademik(@json($chartDonut), @json($chartProdi), @json($chartAngkatan), @json($chartGender))'
    x-init="init()"
    @dashboard-akademik-updated.window="refresh($event.detail)"
    wire:key="dashboard-akademik">

    <div id="akd-chart-data"
        data-chart-donut='@json($chartDonut)'
        data-chart-prodi='@json($chartProdi)'
        data-chart-angkatan='@json($chartAngkatan)'
        data-chart-gender='@json($chartGender)'
        style="display:none"></div>

    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">📈 Dashboard Akademik</h1>
        <p class="text-sm text-gray-500 mt-1">Kondisi &amp; Distribusi Mahasiswa</p>
    </div>

    {{-- Filter --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 mb-6">
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Tahun Masuk</label>
                <select wire:model.live="filterTahunId" class="w-full border-gray-300 rounded-lg text-sm shadow-sm focus:ring-indigo-500">
                    <option value="">— Semua Angkatan —</option>
                    @foreach($tahunList as $t)
                    <option value="{{ $t->id }}">{{ $t->tahun }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Fakultas</label>
                <select wire:model.live="filterFakultasId" class="w-full border-gray-300 rounded-lg text-sm shadow-sm focus:ring-indigo-500">
                    <option value="">— Semua Fakultas —</option>
                    @foreach($fakultasList as $f)
                    <option value="{{ $f->id }}">{{ $f->nama_fakultas }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Program Studi</label>
                <select wire:model.live="filterProdiId" class="w-full border-gray-300 rounded-lg text-sm shadow-sm {{ $prodiList->isEmpty() ? 'bg-gray-50 text-gray-400' : '' }}" {{ $prodiList->isEmpty() ? 'disabled' : '' }}>
                    <option value="">— Semua Prodi —</option>
                    @foreach($prodiList as $p)
                    <option value="{{ $p->id }}">{{ $p->nama_prodi }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Status</label>
                <select wire:model.live="filterStatus" class="w-full border-gray-300 rounded-lg text-sm shadow-sm focus:ring-indigo-500">
                    <option value="">— Semua Status —</option>
                    <option value="Aktif">Aktif</option>
                    <option value="Stop Out">Stop Out</option>
                    <option value="Drop Out">Drop Out</option>
                    <option value="Lulus">Lulus</option>
                    <option value="Mengundurkan Diri">Mengundurkan Diri</option>
                </select>
            </div>
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
        @php
        $kpis = [
            ['label' => 'Total Mahasiswa', 'value' => $stats['total'],      'color' => 'bg-slate-50 border-slate-200',  'text' => 'text-slate-700',  'icon' => '👨‍🎓'],
            ['label' => 'Aktif',           'value' => $stats['aktif'],      'color' => 'bg-green-50 border-green-200',  'text' => 'text-green-700',  'icon' => '✅'],
            ['label' => 'Stop Out',        'value' => $stats['stop_out'],   'color' => 'bg-amber-50 border-amber-200',  'text' => 'text-amber-700',  'icon' => '⏸️'],
            ['label' => 'Drop Out',        'value' => $stats['drop_out'],   'color' => 'bg-red-50 border-red-200',      'text' => 'text-red-700',    'icon' => '❌'],
            ['label' => 'Lulus',           'value' => $stats['lulus'],      'color' => 'bg-blue-50 border-blue-200',    'text' => 'text-blue-700',   'icon' => '🎓'],
            ['label' => 'Undur Diri',      'value' => $stats['undur_diri'], 'color' => 'bg-purple-50 border-purple-200','text' => 'text-purple-700', 'icon' => '🚪'],
        ];
        @endphp
        @foreach($kpis as $kpi)
        <div class="rounded-xl border {{ $kpi['color'] }} p-4 flex flex-col items-start shadow-sm">
            <span class="text-xl mb-1">{{ $kpi['icon'] }}</span>
            <span class="text-2xl font-bold {{ $kpi['text'] }}">{{ number_format($kpi['value']) }}</span>
            <span class="text-xs text-gray-500 mt-0.5">{{ $kpi['label'] }}</span>
        </div>
        @endforeach
    </div>

    {{-- Tab selector --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-1.5 mb-6 inline-flex gap-1 flex-wrap">
        @php
        $mainTabs = [
            ['id' => 'overview', 'label' => 'Ringkasan (Overview)',  'icon' => '📊'],
            ['id' => 'profil',   'label' => 'Profil Mahasiswa',      'icon' => '👤'],
            ['id' => 'wilayah',  'label' => 'Sebaran Wilayah',       'icon' => '🗺️'],
            ['id' => 'ukt',      'label' => 'Sebaran Ekonomi (UKT)', 'icon' => '💰'],
        ];
        @endphp
        @foreach($mainTabs as $t)
        <button @click="switchTab('{{ $t['id'] }}')"
            :class="activeTab === '{{ $t['id'] }}' ? 'bg-indigo-600 text-white shadow-md' : 'text-gray-600 hover:bg-gray-100'"
            class="px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 flex items-center gap-1.5">
            <span>{{ $t['icon'] }}</span> {{ $t['label'] }}
        </button>
        @endforeach
    </div>

    {{-- ══ TAB 1: OVERVIEW ══ --}}
    <div x-show="activeTab === 'overview'" x-transition.opacity>

        {{-- Row 1: 3 chart cards sejajar --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">

            {{-- Donut: Status --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                <p class="text-xs font-semibold text-gray-700 mb-0.5">🍩 Distribusi Status</p>
                <p class="text-xs text-gray-400 mb-3">Proporsi per status akademik</p>
                <div class="relative h-44"><canvas id="chartStatusDonut"></canvas></div>
            </div>

            {{-- Bar: Top 10 Prodi --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                <p class="text-xs font-semibold text-gray-700 mb-0.5">🏫 Top 10 Program Studi</p>
                <p class="text-xs text-gray-400 mb-3">Berdasarkan jumlah mahasiswa</p>
                <div class="relative h-44"><canvas id="chartProdi"></canvas></div>
            </div>

            {{-- Bar: Angkatan --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                <p class="text-xs font-semibold text-gray-700 mb-0.5">📊 Tren per Angkatan</p>
                <p class="text-xs text-gray-400 mb-3">Jumlah mahasiswa per tahun masuk</p>
                <div class="relative h-44"><canvas id="chartAngkatan"></canvas></div>
            </div>

        </div>
    </div>

    {{-- ══ TAB 2: PROFIL ══ --}}
    <div x-show="activeTab === 'profil'" x-transition.opacity style="display:none">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <h2 class="text-sm font-semibold text-gray-800 mb-1">⚥ Distribusi Gender</h2>
                <p class="text-xs text-gray-400 mb-4">Komposisi jenis kelamin</p>
                <div class="relative h-56"><canvas id="chartGender"></canvas></div>
            </div>
            <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <h2 class="text-sm font-semibold text-gray-800 mb-3">📋 Ringkasan Gender</h2>
                <div class="mt-4 space-y-4">
                    @foreach($chartGender['labels'] as $i => $label)
                    @php
                        $val = $chartGender['data'][$i] ?? 0;
                        $pct = $stats['total'] > 0 ? round($val / $stats['total'] * 100, 1) : 0;
                        $barCls = ['bg-blue-500','bg-pink-500','bg-gray-400'][$i % 3];
                    @endphp
                    <div>
                        <div class="flex justify-between text-sm mb-1.5">
                            <span class="font-medium text-gray-700">{{ $label }}</span>
                            <span class="text-gray-500">{{ number_format($val) }} mahasiswa ({{ $pct }}%)</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-3">
                            <div class="{{ $barCls }} h-3 rounded-full transition-all duration-500" style="width:{{ $pct }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Tabel mahasiswa --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-gray-100 flex flex-col sm:flex-row gap-3 items-start sm:items-center justify-between">
                <h2 class="text-sm font-semibold text-gray-800">🔍 Data Mahasiswa</h2>
                <div class="flex gap-2 flex-wrap">
                    <input type="text" wire:model.live.debounce.300ms="search"
                        placeholder="Cari NIM / Nama..."
                        class="border-gray-300 rounded-lg text-sm shadow-sm w-52 focus:ring-indigo-500">
                    <select wire:model.live="perPage" class="border-gray-300 rounded-lg text-sm shadow-sm">
                        <option value="15">15 baris</option>
                        <option value="25">25 baris</option>
                        <option value="50">50 baris</option>
                    </select>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-gray-700">
                    @php
                    $si = fn(string $col) => $sortBy === $col ? ($sortDirection === 'asc' ? '↑' : '↓') : '↕';
                    $sc = fn(string $col) => 'cursor-pointer select-none hover:text-gray-800 transition ' . ($sortBy === $col ? 'text-blue-600 font-semibold' : '');
                    @endphp
                    <thead class="bg-gray-50 border-b border-gray-100 text-xs uppercase text-gray-400">
                        <tr>
                            <th class="px-4 py-3 text-center w-10">No</th>
                            <th class="px-4 py-3 text-left {{ $sc('nim') }}" wire:click="sort('nim')">NIM <span class="opacity-60">{{ $si('nim') }}</span></th>
                            <th class="px-4 py-3 text-left {{ $sc('nama') }}" wire:click="sort('nama')">Nama <span class="opacity-60">{{ $si('nama') }}</span></th>
                            <th class="px-4 py-3 text-left {{ $sc('program_studi') }}" wire:click="sort('program_studi')">Program Studi <span class="opacity-60">{{ $si('program_studi') }}</span></th>
                            <th class="px-4 py-3 text-center">Thn Masuk</th>
                            <th class="px-4 py-3 text-center">Smt</th>
                            <th class="px-4 py-3 text-left">UKT</th>
                            <th class="px-4 py-3 text-right">Nominal UKT</th>
                            <th class="px-4 py-3 text-center {{ $sc('status_akademik') }}" wire:click="sort('status_akademik')">Status <span class="opacity-60">{{ $si('status_akademik') }}</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($mahasiswaList as $mhs)
                        @php
                        $cama  = $mhs->calonMahasiswa;
                        $prodi = $cama?->programStudi;
                        $statusColor = match($mhs->status_akademik ?? 'Aktif') {
                            'Aktif'           => 'bg-green-100 text-green-700',
                            'Stop Out','SO'   => 'bg-amber-100 text-amber-700',
                            'Drop Out','DO'   => 'bg-red-100 text-red-700',
                            'Lulus','Alumni'  => 'bg-blue-100 text-blue-700',
                            default           => 'bg-gray-100 text-gray-600',
                        };
                        @endphp
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 text-center text-gray-400 text-xs">{{ $mahasiswaList->firstItem() + $loop->index }}</td>
                            <td class="px-4 py-3 font-mono text-xs text-gray-500">{{ $mhs->nim }}</td>
                            <td class="px-4 py-3 font-medium text-gray-800">{{ $cama?->nama ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-700 text-xs">{{ $prodi?->kode_prodi ?? '' }}</div>
                                <div class="text-gray-400 text-xs">{{ $prodi?->nama_prodi ?? '—' }}</div>
                            </td>
                            <td class="px-4 py-3 text-center text-xs">{{ $mhs->tahunMasuk?->tahun ?? '—' }}</td>
                            <td class="px-4 py-3 text-center">
                                @if($mhs->semester_ke)
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-50 text-indigo-600">Smt {{ $mhs->semester_ke }}</span>
                                @else —
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-500">{{ $mhs->kelompokUkt ? 'UKT '.$mhs->kelompokUkt->kelompok : '—' }}</td>
                            <td class="px-4 py-3 text-right text-xs font-medium text-gray-700">
                                {{ $mhs->nominal_ukt ? 'Rp '.number_format($mhs->nominal_ukt, 0, ',', '.') : '—' }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColor }}">{{ $mhs->status_akademik ?? 'Aktif' }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="px-4 py-12 text-center text-gray-400">
                                <div class="text-3xl mb-2">🎓</div>
                                <div>Tidak ada data mahasiswa ditemukan.</div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($mahasiswaList->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">{{ $mahasiswaList->links() }}</div>
            @endif
        </div>
    </div>

    {{-- ══ TAB 3: SEBARAN WILAYAH ══ --}}
    <div x-show="activeTab === 'wilayah'" x-transition.opacity style="display:none">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Peta --}}
            <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <h2 class="text-sm font-semibold text-gray-800 mb-1">🗺️ Peta Sebaran Provinsi</h2>
                <p class="text-xs text-gray-400 mb-3">Klik provinsi untuk detail</p>
                <div id="mapMahasiswa" style="height:400px; border-radius:12px; overflow:hidden;"
                    x-init="$nextTick(() => {
                        if (typeof L === 'undefined') return;
                        const mapW = L.map('mapMahasiswa', { zoomControl:true, scrollWheelZoom:false });
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap' }).addTo(mapW);
                        mapW.setView([-2.5, 118], 4);
                        const mapData = @json($dataWilayah['mapData']);
                        const max = Math.max(...Object.values(mapData), 1);
                        fetch('/indonesia-provinces.json').catch(() => fetch('/vendor/leaflet/indonesia-provinces.json')).then(r=>r.json()).then(geo => {
                            L.geoJSON(geo, {
                                style: f => {
                                    const name = f.properties?.Propinsi || f.properties?.name || '';
                                    const cnt = Object.entries(mapData).find(([k]) => name.toLowerCase().includes(k.toLowerCase()))?.[1] || 0;
                                    return { fillColor: cnt > 0 ? `rgba(79,70,229,${0.1+cnt/max*0.8})` : '#f9fafb', weight:1, color:'#e5e7eb', fillOpacity:1 };
                                },
                                onEachFeature: (f, l) => {
                                    const name = f.properties?.Propinsi || f.properties?.name || '';
                                    const cnt = Object.entries(mapData).find(([k]) => name.toLowerCase().includes(k.toLowerCase()))?.[1] || 0;
                                    l.bindTooltip(`<b>${name}</b><br/>${cnt} mahasiswa`);
                                }
                            }).addTo(mapW);
                        }).catch(()=>{});
                    })">
                </div>
            </div>

            {{-- Top Kabupaten --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden flex flex-col">
                <div class="p-4 border-b border-gray-100 bg-gray-50">
                    <h2 class="text-sm font-semibold text-gray-800">📍 Top 25 Kabupaten/Kota</h2>
                </div>
                <div class="overflow-y-auto flex-1" style="max-height:420px;">
                    <table class="w-full text-xs text-gray-600">
                        <thead class="bg-gray-50 sticky top-0 border-b border-gray-100">
                            <tr>
                                <th class="py-2 px-3 text-center w-8">#</th>
                                <th class="py-2 px-3 text-left">Kabupaten/Kota</th>
                                <th class="py-2 px-3 text-right">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($dataWilayah['topKabupaten'] as $i => $kab)
                            <tr class="hover:bg-gray-50">
                                <td class="py-2 px-3 text-center text-gray-400">{{ $i + 1 }}</td>
                                <td class="py-2 px-3 font-medium">{{ $kab['nama'] }}</td>
                                <td class="py-2 px-3 text-right font-semibold text-indigo-700">{{ number_format($kab['total']) }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="py-6 text-center text-gray-400">Belum ada data wilayah.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Tabel per Provinsi --}}
        <div class="mt-6 bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-gray-100 bg-gray-50">
                <h2 class="text-sm font-semibold text-gray-800">📊 Distribusi per Provinsi</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-gray-600">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="py-2.5 px-3 text-center w-8">#</th>
                            <th class="py-2.5 px-4 text-left font-semibold">Provinsi</th>
                            <th class="py-2.5 px-4 text-right font-semibold">Jumlah Mahasiswa</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($dataWilayah['perProvinsi'] as $i => $prov)
                        <tr class="hover:bg-indigo-50/40">
                            <td class="py-2 px-3 text-center text-gray-400">{{ $i + 1 }}</td>
                            <td class="py-2 px-4 font-medium text-gray-800">{{ $prov['provinsi'] }}</td>
                            <td class="py-2 px-4 text-right font-semibold text-indigo-700">{{ number_format($prov['total']) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="py-8 text-center text-gray-400">Belum ada data wilayah.</td></tr>
                        @endforelse
                    </tbody>
                    @if(count($dataWilayah['perProvinsi']) > 1)
                    <tfoot class="bg-gray-50 font-bold border-t-2 border-gray-200">
                        <tr>
                            <td class="py-2 px-3"></td>
                            <td class="py-2 px-4">TOTAL</td>
                            <td class="py-2 px-4 text-right text-blue-700">{{ number_format(collect($dataWilayah['perProvinsi'])->sum('total')) }}</td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

    {{-- ══ TAB 4: SEBARAN EKONOMI (UKT) ══ --}}
    <div x-show="activeTab === 'ukt'" x-transition.opacity style="display:none">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Pie UKT --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 flex flex-col">
                <div class="mb-4">
                    <h2 class="text-sm font-semibold text-gray-800">🥧 Distribusi Kelompok UKT</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Proporsi mahasiswa per kelompok UKT</p>
                </div>
                <div style="position:relative; height:240px;">
                    <canvas id="chartUktAkademik"
                        x-init="$nextTick(() => {
                            const ctx = document.getElementById('chartUktAkademik');
                            if (!ctx) return;
                            const pie = @json($dataUkt['chartPie']);
                            if (!pie?.labels?.length) return;
                            const old = Chart.getChart(ctx); if (old) old.destroy();
                            new Chart(ctx, {
                                type: 'doughnut',
                                data: { labels: pie.labels, datasets: [{ data: pie.data,
                                    backgroundColor: ['#4F46E5','#06B6D4','#10B981','#F59E0B','#F43F5E','#8B5CF6','#EC4899','#6366F1','#84CC16','#F97316'],
                                    borderWidth:0, hoverOffset:8 }] },
                                options: { responsive:true, maintainAspectRatio:false, cutout:'68%',
                                    plugins: { legend:{ position:'bottom', labels:{ font:{size:11}, padding:10 } } } }
                            });
                        })">
                    </canvas>
                </div>
                @php $colors = ['#4F46E5','#06B6D4','#10B981','#F59E0B','#F43F5E','#8B5CF6','#EC4899','#6366F1','#84CC16','#F97316']; @endphp
                <div class="mt-4 pt-3 border-t border-gray-100 grid grid-cols-2 gap-1">
                    @foreach($dataUkt['chartPie']['labels'] as $i => $label)
                    <div class="flex items-center gap-1.5 text-xs text-gray-600">
                        <span class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background:{{ $colors[$i % 10] }}"></span>
                        <span>{{ $label }}: <b>{{ number_format($dataUkt['chartPie']['data'][$i] ?? 0) }}</b></span>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Sub-tabs tabel --}}
            <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 shadow-sm flex flex-col" x-data="{uktTabAkd:'angkatan'}">
                <div class="flex gap-1 flex-wrap p-3 border-b border-gray-100 bg-gray-50 rounded-t-xl">
                    @foreach([['id'=>'angkatan','label'=>'Per Angkatan'],['id'=>'prodi','label'=>'Per Prodi'],['id'=>'provinsi','label'=>'Per Provinsi']] as $t)
                    <button @click="uktTabAkd='{{ $t['id'] }}'"
                        :class="uktTabAkd==='{{ $t['id'] }}' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200'"
                        class="px-3 py-1.5 rounded-lg text-xs font-medium transition">
                        {{ $t['label'] }}
                    </button>
                    @endforeach
                </div>
                <div class="overflow-auto flex-1">
                    <div x-show="uktTabAkd==='angkatan'">
                        @include('livewire.partials.ukt-table', [
                            'title'    => 'Distribusi UKT per Angkatan',
                            'rows'     => $dataUkt['perAngkatan'],
                            'labelKey' => 'label',
                            'cols'     => $dataUkt['uktList']->map(fn($u) => $u->kelompok)->all(),
                            'colPrefix'=> 'UKT ',
                        ])
                    </div>
                    <div x-show="uktTabAkd==='prodi'" style="display:none;">
                        @include('livewire.partials.ukt-table', [
                            'title'    => 'Distribusi UKT per Program Studi',
                            'rows'     => $dataUkt['perProdi'],
                            'labelKey' => 'label',
                            'cols'     => $dataUkt['uktList']->map(fn($u) => $u->kelompok)->all(),
                            'colPrefix'=> 'UKT ',
                        ])
                    </div>
                    <div x-show="uktTabAkd==='provinsi'" style="display:none;">
                        @include('livewire.partials.ukt-table', [
                            'title'    => 'Distribusi UKT per Provinsi',
                            'rows'     => $dataUkt['perProvinsi'],
                            'labelKey' => 'label',
                            'cols'     => $dataUkt['uktList']->map(fn($u) => $u->kelompok)->all(),
                            'colPrefix'=> 'UKT ',
                        ])
                    </div>
                </div>
            </div>

        </div>
    </div>

</div>
