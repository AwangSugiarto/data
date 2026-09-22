<div>
    {{-- Local assets to avoid CDN latency --}}
    <link rel="stylesheet" href="{{ asset('css/leaflet.min.css') }}" />
    {{-- Chart.js sudah di-load di <head> layout --}}
    <script src="{{ asset('js/leaflet.min.js') }}"></script>

    <script>
    window.dashboardCharts = function($wire, tren, jalur, topProdi, fakultas, wilayah, ukt, profil, asing) {
        const palette = {
            blue:   '#4F46E5', blueLight: 'rgba(79, 70, 229, 0.15)', // Indigo
            green:  '#10B981', greenLight: 'rgba(16, 185, 129, 0.15)', // Emerald
            orange: '#F59E0B', orangeLight: 'rgba(245, 158, 11, 0.15)', // Amber
            purple: '#8B5CF6', purpleLight: 'rgba(139, 92, 246, 0.15)', // Violet
            donut: ['#4F46E5','#06B6D4','#10B981','#F59E0B','#F43F5E','#8B5CF6','#EC4899','#6366F1','#84CC16','#F97316'],
        };

        const baseOptions = {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { labels: { font: { size: 11, family: "'Outfit', 'Inter', sans-serif" }, usePointStyle: true, boxWidth: 8, padding: 20 } } },
        };

        let charts = {};
        let mapInstance = null;

        return {
            activeTab: 'overview',
            mapData: wilayah.mapData,

            init() {
                this.$nextTick(() => {
                    this.renderAll();
                    this.renderAsing();
                });

                // Re-render saat Livewire update (filter berubah)
                document.addEventListener('livewire:update', () => {
                    setTimeout(() => { this.renderAll(); this.renderAsing(); }, 150);
                });

                // Re-render saat navigate kembali ke halaman ini (wire:navigate)
                document.addEventListener('livewire:navigated', () => {
                    setTimeout(() => { this.renderAll(); this.renderAsing(); }, 200);
                });
            },

            switchTab(tab) {
                this.activeTab = tab;
                this.$nextTick(() => {
                    this.renderAll();
                });
            },

            destroyAll() {
                Object.values(charts).forEach(c => c?.destroy());
                charts = {};
            },

            renderAll() {
                this.destroyAll();
                if (this.activeTab === 'overview') {
                    this.renderTren();
                    this.renderJalur();
                    this.renderTopProdi();
                    this.renderFakultas();
                } else if (this.activeTab === 'wilayah') {
                    this.renderMap();
                } else if (this.activeTab === 'ukt') {
                    this.renderUkt();
                }
            },

            renderTren() {
                const ctx = document.getElementById('chartTren');
                if (!ctx) return;
                charts.tren = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: tren.labels,
                        datasets: [
                            { label: 'Pendaftar', data: tren.daftar, borderColor: palette.blue, backgroundColor: palette.blueLight, fill: true, tension: 0.4 },
                            { label: 'Lulus', data: tren.lulus, borderColor: palette.green, backgroundColor: palette.greenLight, fill: true, tension: 0.4 },
                            { label: 'Registrasi', data: tren.registrasi, borderColor: palette.orange, backgroundColor: palette.orangeLight, fill: true, tension: 0.4 },
                        ],
                    },
                    options: { ...baseOptions, scales: { x: { grid: { display: false } }, y: { beginAtZero: true } } },
                });
            },
            renderJalur() {
                const ctx = document.getElementById('chartJalur');
                if (!ctx || !jalur.labels.length) return;
                charts.jalur = new Chart(ctx, {
                    type: 'doughnut',
                    data: { labels: jalur.labels, datasets: [{ data: jalur.data, backgroundColor: palette.donut, borderWidth: 0, hoverOffset: 8 }] },
                    options: { ...baseOptions, cutout: '75%', plugins: { ...baseOptions.plugins, legend: { position: 'bottom' } } },
                });
            },
            renderTopProdi() {
                const ctx = document.getElementById('chartTopProdi');
                if (!ctx || !topProdi.labels.length) return;
                charts.topProdi = new Chart(ctx, {
                    type: 'bar',
                    data: { labels: topProdi.labels, datasets: [{ data: topProdi.data, backgroundColor: palette.donut, borderRadius: 6 }] },
                    options: { ...baseOptions, indexAxis: 'y', plugins: { ...baseOptions.plugins, legend: { display: false } } },
                });
            },
            renderFakultas() {
                const ctx = document.getElementById('chartFakultas');
                if (!ctx || !fakultas.labels.length) return;
                charts.fakultas = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: fakultas.labels,
                        datasets: [
                            { label: 'Kuota',      data: fakultas.kuota,      backgroundColor: palette.blueLight,   borderColor: palette.blue,   borderWidth: 2, borderRadius: 4 },
                            { label: 'Pendaftar',  data: fakultas.daftar,     backgroundColor: palette.purpleLight, borderColor: palette.purple, borderWidth: 2, borderRadius: 4 },
                            { label: 'Lulus',      data: fakultas.lulus,      backgroundColor: palette.greenLight,  borderColor: palette.green,  borderWidth: 2, borderRadius: 4 },
                            { label: 'Registrasi', data: fakultas.registrasi, backgroundColor: 'rgba(249,115,22,0.2)', borderColor: '#F97316',    borderWidth: 2, borderRadius: 4 },
                        ],
                    },
                    options: { ...baseOptions, scales: { x: { grid: { display: false } }, y: { beginAtZero: true } } },
                });
            },
            renderUkt() {
                const ctx = document.getElementById('chartUkt');
                if (!ctx || !ukt.chartPie.labels.length) return;
                if (charts.ukt) charts.ukt.destroy();
                charts.ukt = new Chart(ctx, {
                    type: 'doughnut',
                    data: { labels: ukt.chartPie.labels, datasets: [{ data: ukt.chartPie.data, backgroundColor: palette.donut, borderWidth: 0, hoverOffset: 8 }] },
                    options: { ...baseOptions, cutout: '75%', plugins: { ...baseOptions.plugins, legend: { position: 'bottom' } } },
                });
            },
            renderProfil() {
                const ctxGender = document.getElementById('chartGender');
                if (ctxGender && profil.gender.labels.length) {
                    if (charts.gender) charts.gender.destroy();
                    charts.gender = new Chart(ctxGender, {
                        type: 'pie',
                        data: { labels: profil.gender.labels, datasets: [{ data: profil.gender.data, backgroundColor: [palette.blue, palette.pink || '#EC4899'], borderWidth: 1 }] },
                        options: { ...baseOptions, plugins: { ...baseOptions.plugins, legend: { position: 'bottom' } } },
                    });
                }
                const ctxSekolah = document.getElementById('chartSekolah');
                if (ctxSekolah && profil.jenis_sekolah.labels.length) {
                    if (charts.sekolah) charts.sekolah.destroy();
                    charts.sekolah = new Chart(ctxSekolah, {
                        type: 'bar',
                        data: { labels: profil.jenis_sekolah.labels, datasets: [{ label: 'Jumlah Pendaftar', data: profil.jenis_sekolah.data, backgroundColor: palette.orange, borderRadius: 4 }] },
                        options: { ...baseOptions, scales: { y: { beginAtZero: true } }, indexAxis: 'y' },
                    });
                }
                const ctxTopSekolah = document.getElementById('chartTopSekolah');
                if (ctxTopSekolah && profil.top_sekolah.length) {
                    if (charts.topSekolah) charts.topSekolah.destroy();
                    charts.topSekolah = new Chart(ctxTopSekolah, {
                        type: 'bar',
                        data: { 
                            labels: profil.top_sekolah.map(s => s.asal_sekolah), 
                            datasets: [{ label: 'Jumlah Pendaftar', data: profil.top_sekolah.map(s => s.total), backgroundColor: palette.blueLight, borderColor: palette.blue, borderWidth: 1, borderRadius: 4 }] 
                        },
                        options: { ...baseOptions, scales: { y: { beginAtZero: true } } },
                    });
                }
            },

            // Leaflet Map Logic
            getColor(d, max) {
                return d > max * 0.8 ? '#800026' :
                       d > max * 0.6 ? '#BD0026' :
                       d > max * 0.4 ? '#E31A1C' :
                       d > max * 0.2 ? '#FC4E2A' :
                       d > 0         ? '#FD8D3C' :
                                       '#FFEDA0';
            },
            renderMap() {
                if (mapInstance) {
                    mapInstance.invalidateSize();
                    return;
                }
                const mapContainer = document.getElementById('mapWilayah');
                if (!mapContainer) return;

                mapInstance = L.map('mapWilayah').setView([-2.5489, 118.0149], 5);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                }).addTo(mapInstance);

                const mapDataLocal = this.mapData;
                let maxCount = Math.max(...Object.values(mapDataLocal).concat([0]));

                fetch('/indonesia-prov.geojson')
                    .then(res => res.json())
                    .then(data => {
                        L.geoJson(data, {
                            style: (feature) => {
                                let propinsi = feature.properties.Propinsi;
                                if (propinsi) propinsi = propinsi.toUpperCase();
                                let count = mapDataLocal[propinsi] || 0;
                                return {
                                    fillColor: this.getColor(count, maxCount),
                                    weight: 1,
                                    opacity: 1,
                                    color: 'white',
                                    fillOpacity: count > 0 ? 0.7 : 0.3
                                };
                            },
                            onEachFeature: (feature, layer) => {
                                let propinsi = feature.properties.Propinsi;
                                if (propinsi) propinsi = propinsi.toUpperCase();
                                let count = mapDataLocal[propinsi] || 0;
                                layer.bindTooltip(`<b>${propinsi}</b><br/>${count} Pendaftar`);
                                
                                layer.on('click', function(e) {
                                    mapInstance.fitBounds(layer.getBounds());
                                    $wire.selectProvinsi(propinsi);
                                });
                            }
                        }).addTo(mapInstance);
                    });
            },
            initProfil() {
                this.$watch('activeTab', value => {
                    setTimeout(() => {
                        if (value === 'overview') {
                            this.destroyAll();
                            this.renderTren(); this.renderJalur();
                            this.renderTopProdi(); this.renderFakultas();
                            this.renderAsing();
                        } else if (value === 'wilayah') {
                            this.renderMap();
                        } else if (value === 'ukt') {
                            if (charts.ukt) { charts.ukt.destroy(); delete charts.ukt; }
                            this.renderUkt();
                        } else if (value === 'profil') {
                            this.renderProfil();
                        }
                    }, 120);
                });
                if (this.activeTab === 'profil') this.renderProfil();
            },
            renderAsing() {
                if (!asing || !asing.perNegara || asing.perNegara.length === 0) return;

                const colors14 = ['#10B981','#3B82F6','#8B5CF6','#F59E0B','#EF4444',
                                  '#06B6D4','#EC4899','#84CC16','#F97316','#6366F1',
                                  '#14B8A6','#FB923C','#A855F7','#22C55E'];

                const asingOpts = {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: baseOptions.plugins,
                };

                // Chart Asal Negara (horizontal bar — bedakan warna per negara)
                const ctxNegara = document.getElementById('chartAsingNegara');
                if (ctxNegara) {
                    if (charts.asingNegara) charts.asingNegara.destroy();
                    const negaraLabels = asing.perNegara.map(n => (n.kode_negara ? '['+n.kode_negara+'] ' : '') + n.nama_negara);
                    const negaraData   = asing.perNegara.map(n => n.total);
                    const negaraBg     = colors14.slice(0, negaraData.length);
                    charts.asingNegara = new Chart(ctxNegara, {
                        type: 'bar',
                        data: {
                            labels: negaraLabels,
                            datasets: [{
                                label: 'Mahasiswa',
                                data: negaraData,
                                backgroundColor: negaraBg.map(c => c + 'CC'),
                                borderColor: negaraBg,
                                borderWidth: 1.5,
                                borderRadius: 6,
                                borderSkipped: false,
                            }]
                        },
                        options: {
                            ...asingOpts,
                            indexAxis: 'y',
                            scales: {
                                x: { beginAtZero: true, ticks: { stepSize: 1, precision: 0 }, grid: { display: false } },
                                y: { ticks: { font: { size: 11 }, color: '#374151' } }
                            },
                            plugins: {
                                ...asingOpts.plugins,
                                legend: { display: false },
                                tooltip: {
                                    callbacks: {
                                        label: ctx => ` ${ctx.raw} mahasiswa`
                                    }
                                }
                            }
                        }
                    });
                }

                // Chart Per Prodi (horizontal bar)
                const ctxProdi = document.getElementById('chartAsingProdi');
                if (ctxProdi && asing.perProdi.length > 0) {
                    if (charts.asingProdi) charts.asingProdi.destroy();
                    charts.asingProdi = new Chart(ctxProdi, {
                        type: 'bar',
                        data: {
                            labels: asing.perProdi.map(p => p.nama_prodi.length > 24 ? p.nama_prodi.substring(0,24)+'…' : p.nama_prodi),
                            datasets: [{ label: 'Mahasiswa Asing', data: asing.perProdi.map(p => p.total), backgroundColor: 'rgba(16,185,129,0.75)', borderColor: '#10B981', borderWidth: 1, borderRadius: 4 }]
                        },
                        options: { ...asingOpts, indexAxis: 'y', scales: { x: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { display: false } }, y: { ticks: { font: { size: 10 } } } }, plugins: { ...asingOpts.plugins, legend: { display: false } } }
                    });
                }

                // Chart Tren Tahunan (line)
                const ctxTren = document.getElementById('chartAsingTren');
                if (ctxTren && asing.trenLabels && asing.trenLabels.length > 0) {
                    if (charts.asingTren) charts.asingTren.destroy();
                    charts.asingTren = new Chart(ctxTren, {
                        type: 'line',
                        data: {
                            labels: asing.trenLabels,
                            datasets: [{ label: 'Mahasiswa Asing', data: asing.trenData, borderColor: '#10B981', backgroundColor: 'rgba(16,185,129,0.15)', borderWidth: 2.5, tension: 0.4, fill: true, pointBackgroundColor: '#10B981', pointRadius: 5, pointHoverRadius: 7 }]
                        },
                        options: { ...asingOpts, scales: { x: { grid: { display: false } }, y: { beginAtZero: true, ticks: { stepSize: 1, precision: 0 } } } }
                    });
                }
            }
        };
    }
    </script>

    <div
        class="py-6 px-4 max-w-6xl mx-auto min-h-screen bg-gradient-to-br from-slate-50 via-white to-blue-50"
        x-data='dashboardCharts($wire, @json($chartTren), @json($chartJalur), @json($chartTopProdi), @json($chartFakultas), @json($chartWilayah), @json($chartUkt), @json($chartProfil), @json($chartAsing))'
        x-init="init(); initProfil(); $nextTick(() => renderAsing())"
        wire:key="dashboard-{{ $tahunId }}-{{ $fakultasId }}-{{ $prodiId }}-{{ $jalurId }}"
    >

        {{-- ── Header ─────────────────────────────────────────────────────── --}}
        <div class="flex flex-wrap items-center justify-between gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-blue-700 to-indigo-600 drop-shadow-sm font-['Outfit']">Dashboard PMB</h1>
                <p class="text-sm text-gray-500 mt-1 font-medium">Penerimaan Mahasiswa Baru — UIN Raden Fatah Palembang</p>
            </div>
            <div class="text-xs text-slate-500 bg-white/60 backdrop-blur-md px-4 py-2 rounded-full border border-white shadow-sm">
                Data per: <strong class="text-slate-800 font-semibold">{{ $tahunLabel }}</strong>
            </div>
        </div>

        {{-- ── Filter Bar ──────────────────────────────────────────────────── --}}
        <div class="bg-white/70 backdrop-blur-lg rounded-2xl border border-white/60 shadow-lg shadow-slate-200/50 p-5 mb-8 flex flex-wrap gap-4 items-end transition-all hover:shadow-xl">
            <div class="flex-1 min-w-[150px]">
                <label class="block text-xs font-semibold text-slate-500 mb-1.5 uppercase tracking-wider">Tahun Akademik</label>
                <select wire:model.live="tahunId" class="w-full bg-white/80 border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-medium text-slate-700 focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 focus:outline-none transition-all shadow-sm">
                    @foreach($tahunList as $t)
                    <option value="{{ $t->id }}">{{ $t->tahun }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1 min-w-[150px]">
                <label class="block text-xs font-semibold text-slate-500 mb-1.5 uppercase tracking-wider">Fakultas</label>
                <select wire:model.live="fakultasId" class="w-full bg-white/80 border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-medium text-slate-700 focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 focus:outline-none transition-all shadow-sm">
                    <option value="">Semua Fakultas</option>
                    @foreach($fakultasList as $f)
                    <option value="{{ $f->id }}">{{ $f->kode_fakultas }} — {{ $f->nama_fakultas }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1 min-w-[150px]">
                <label class="block text-xs font-semibold text-slate-500 mb-1.5 uppercase tracking-wider">Program Studi</label>
                <select wire:model.live="prodiId" class="w-full bg-white/80 border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-medium text-slate-700 focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 focus:outline-none transition-all shadow-sm">
                    <option value="">Semua Prodi</option>
                    @foreach($prodiList as $p)
                    <option value="{{ $p->id }}">{{ $p->nama_prodi }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1 min-w-[150px]">
                <label class="block text-xs font-semibold text-slate-500 mb-1.5 uppercase tracking-wider">Jalur Seleksi</label>
                <select wire:model.live="jalurId" class="w-full bg-white/80 border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-medium text-slate-700 focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 focus:outline-none transition-all shadow-sm">
                    <option value="">Semua Jalur</option>
                    @foreach($jalurList as $j)
                    <option value="{{ $j->id }}">{{ $j->nama_jalur }}</option>
                    @endforeach
                </select>
            </div>
            <button wire:click="resetFilters"
                class="h-[42px] flex items-center justify-center text-xs font-semibold text-slate-500 hover:text-indigo-600 bg-white/50 border border-slate-200 px-5 rounded-xl hover:bg-white hover:border-indigo-200 transition-all shadow-sm">
                ✕ Reset
            </button>
        </div>

        {{-- ── Navigation Tabs ─────────────────────────────────────────────── --}}
        <div class="flex space-x-1 p-1.5 bg-slate-200/50 backdrop-blur-md rounded-2xl mb-8 w-max shadow-inner border border-slate-200/60">
            <button @click="activeTab = 'overview'" :class="{'bg-white shadow-md text-indigo-700 scale-100 font-semibold': activeTab === 'overview', 'text-slate-500 hover:text-slate-700 scale-95 font-medium': activeTab !== 'overview'}" class="px-6 py-2.5 rounded-xl text-sm transition-all duration-300">
                Ringkasan (Overview)
            </button>
            <button @click="activeTab = 'profil'" :class="{'bg-white shadow-md text-indigo-700 scale-100 font-semibold': activeTab === 'profil', 'text-slate-500 hover:text-slate-700 scale-95 font-medium': activeTab !== 'profil'}" class="px-6 py-2.5 rounded-xl text-sm transition-all duration-300">
                Profil Pendaftar
            </button>
            <button @click="activeTab = 'wilayah'; setTimeout(() => renderMap(), 100)" :class="{'bg-white shadow-md text-indigo-700 scale-100 font-semibold': activeTab === 'wilayah', 'text-slate-500 hover:text-slate-700 scale-95 font-medium': activeTab !== 'wilayah'}" class="px-6 py-2.5 rounded-xl text-sm transition-all duration-300">
                Sebaran Wilayah
            </button>
            <button @click="activeTab = 'ukt'" :class="{'bg-white shadow-md text-indigo-700 scale-100 font-semibold': activeTab === 'ukt', 'text-slate-500 hover:text-slate-700 scale-95 font-medium': activeTab !== 'ukt'}" class="px-6 py-2.5 rounded-xl text-sm transition-all duration-300">
                Sebaran Ekonomi (UKT)
            </button>
        </div>

        {{-- ── TAB 1: OVERVIEW ─────────────────────────────────────────────── --}}
        <div x-show="activeTab === 'overview'" x-transition.opacity>
            {{-- KPI Cards --}}
            <div class="grid gap-3 mb-8" style="grid-template-columns: repeat(6, minmax(0, 1fr));">
                @php
                $totalPendaftar = max($kpi['pendaftar'], 1);
                $cards = [
                    ['label'=>'Total Kuota',      'value'=>number_format($kpi['kuota']),        'sub'=>$ketatanSaing.'x dari kuota',    'pct'=>null,                                                           'icon'=>'🎯', 'text'=>'text-blue-700',   'border'=>'border-blue-100',   'bar'=>'bg-blue-400'],
                    ['label'=>'Total Pendaftar',  'value'=>number_format($kpi['pendaftar']),    'sub'=>'Rasio pendaftar',               'pct'=>null,                                                           'icon'=>'📝', 'text'=>'text-purple-700', 'border'=>'border-purple-100', 'bar'=>'bg-purple-400'],
                    ['label'=>'Total Lulus',      'value'=>number_format($kpi['lulus']),        'sub'=>$rasioLulus.'% kelulusan',       'pct'=>round($kpi['lulus']   / $totalPendaftar * 100, 1),             'icon'=>'✅', 'text'=>'text-emerald-700','border'=>'border-emerald-100','bar'=>'bg-emerald-400'],
                    ['label'=>'Total Registrasi', 'value'=>number_format($kpi['registrasi']),  'sub'=>'Konversi '.$rasioReg.'%',       'pct'=>round($kpi['registrasi']/ $totalPendaftar * 100, 1),            'icon'=>'🎓', 'text'=>'text-amber-700',  'border'=>'border-amber-100',  'bar'=>'bg-amber-400'],
                    ['label'=>'Penerima Beasiswa','value'=>number_format($kpi['beasiswa']),    'sub'=>'Dari registrasi',               'pct'=>$kpi['registrasi']>0 ? round($kpi['beasiswa']/$kpi['registrasi']*100,1) : 0, 'icon'=>'📜', 'text'=>'text-cyan-700',   'border'=>'border-cyan-100',   'bar'=>'bg-cyan-400'],
                    ['label'=>'Total Alumni',     'value'=>number_format($kpi['alumni'] ?? 0), 'sub'=>'Berstatus alumni',             'pct'=>round(($kpi['alumni'] ?? 0) / $totalPendaftar * 100, 1),        'icon'=>'🎖️', 'text'=>'text-rose-700',   'border'=>'border-rose-100',   'bar'=>'bg-rose-400'],
                ];
                @endphp
                @foreach($cards as $card)
                <div class="bg-white rounded-xl border {{ $card['border'] }} shadow-sm p-3 flex flex-col gap-1 hover:-translate-y-0.5 hover:shadow-md transition-all duration-200">
                    <div class="flex items-center justify-between">
                        <span class="text-lg">{{ $card['icon'] }}</span>
                        <span class="text-xl font-bold {{ $card['text'] }}">{{ $card['value'] }}</span>
                    </div>
                    <p class="text-xs font-medium text-gray-500 mt-0.5">{{ $card['label'] }}</p>
                    @if($card['pct'] !== null)
                    <div class="w-full bg-gray-100 rounded-full h-1 mt-1">
                        <div class="{{ $card['bar'] }} h-1 rounded-full" style="width: {{ min($card['pct'], 100) }}%"></div>
                    </div>
                    @endif
                    <p class="text-[10px] text-gray-400">{{ $card['sub'] }}</p>
                </div>
                @endforeach
            </div>

            {{-- Baris Chart 1: Tren + Jalur --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                <div class="lg:col-span-2 bg-white/80 backdrop-blur-xl rounded-2xl border border-white/60 shadow-lg shadow-slate-200/50 p-6 transition-all hover:shadow-xl">
                    <div class="flex items-center justify-between mb-5">
                        <div>
                            <h2 class="text-base font-bold text-slate-800 font-['Outfit']">📈 Tren Tahunan</h2>
                            <p class="text-xs text-slate-400 mt-0.5 font-medium">Pendaftar, Lulus, dan Registrasi per tahun</p>
                        </div>
                    </div>
                    <div class="relative h-72">
                        <canvas id="chartTren" x-ref="chartTren"></canvas>
                    </div>
                </div>
                <div class="bg-white/80 backdrop-blur-xl rounded-2xl border border-white/60 shadow-lg shadow-slate-200/50 p-6 transition-all hover:shadow-xl">
                    <div class="mb-5 text-center">
                        <h2 class="text-base font-bold text-slate-800 font-['Outfit']">🥧 Komposisi Jalur</h2>
                        <p class="text-xs text-slate-400 mt-0.5 font-medium">Distribusi pendaftar</p>
                    </div>
                    <div class="relative h-72">
                        <canvas id="chartJalur" x-ref="chartJalur"></canvas>
                    </div>
                </div>
            </div>

            {{-- Baris Chart 2: Top Prodi + Fakultas --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <div class="bg-white/80 backdrop-blur-xl rounded-2xl border border-white/60 shadow-lg shadow-slate-200/50 p-6 transition-all hover:shadow-xl">
                    <div class="mb-5">
                        <h2 class="text-base font-bold text-slate-800 font-['Outfit']">🏆 Top 10 Prodi Terfavorit</h2>
                        <p class="text-xs text-slate-400 mt-0.5 font-medium">Berdasarkan jumlah pendaftar</p>
                    </div>
                    <div class="relative h-80">
                        <canvas id="chartTopProdi" x-ref="chartTopProdi"></canvas>
                    </div>
                </div>
                <div class="bg-white/80 backdrop-blur-xl rounded-2xl border border-white/60 shadow-lg shadow-slate-200/50 p-6 transition-all hover:shadow-xl">
                    <div class="mb-5 flex items-start justify-between">
                        <div>
                            <h2 class="text-base font-bold text-slate-800 font-['Outfit']">🏫 {{ $chartFakultas['judul'] }}</h2>
                            <p class="text-xs text-slate-400 mt-0.5 font-medium">{{ $chartFakultas['sub'] }}</p>
                        </div>
                        @if($chartFakultas['mode'] === 'prodi')
                        <span class="text-xs bg-blue-50 text-blue-600 border border-blue-200 rounded-full px-2.5 py-1 font-medium whitespace-nowrap">
                            🔍 Drill-down aktif
                        </span>
                        @endif
                    </div>
                    <div class="relative h-80">
                        <canvas id="chartFakultas" x-ref="chartFakultas"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── TAB 1.5: PROFIL PENDAFTAR ──────────────────────────────────────── --}}
        <div x-show="activeTab === 'profil'" x-transition.opacity style="display: none;">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                {{-- Gender Pie Chart --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                    <div class="mb-4">
                        <h2 class="text-sm font-semibold text-gray-800">🚻 Jenis Kelamin</h2>
                        <p class="text-xs text-gray-400 mt-0.5">Proporsi pendaftar Laki-laki vs Perempuan</p>
                    </div>
                    <div class="relative h-64">
                        <canvas id="chartGender"></canvas>
                    </div>
                </div>

                {{-- Jenis Sekolah Bar Chart --}}
                <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                    <div class="mb-4">
                        <h2 class="text-sm font-semibold text-gray-800">🏫 Kategori Sekolah Asal</h2>
                        <p class="text-xs text-gray-400 mt-0.5">Distribusi asal sekolah pendaftar</p>
                    </div>
                    <div class="relative h-64">
                        <canvas id="chartSekolah"></canvas>
                    </div>
                </div>
            </div>

            {{-- Top Sekolah Section --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden mb-6">
                <div class="p-4 border-b border-gray-200 bg-gray-50 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <h2 class="text-sm font-semibold text-gray-800">🏆 Top 10 Sekolah Asal Pendaftar Terbanyak</h2>
                    <div class="flex flex-col sm:flex-row gap-2">
                        <input type="text" wire:model.live.debounce.300ms="searchSekolah" placeholder="Cari nama sekolah..." class="text-sm border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 w-full sm:w-48">
                        <select wire:model.live="filterJenisSekolah" class="text-sm border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 w-full sm:w-40">
                            <option value="">Semua Jenis</option>
                            @foreach($chartProfil['list_jenis_sekolah'] as $jenis)
                                <option value="{{ $jenis }}">{{ $jenis }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                {{-- Chart Top 10 Sekolah --}}
                <div class="p-5 border-b border-gray-100">
                    <div class="relative h-72">
                        <canvas id="chartTopSekolah"></canvas>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-gray-600">
                        <thead class="bg-white border-b border-gray-200 text-xs uppercase text-gray-500">
                            <tr>
                                <th class="py-3 px-4">No</th>
                                <th class="py-3 px-4">Nama Sekolah</th>
                                <th class="py-3 px-4">Kategori Sekolah</th>
                                <th class="py-3 px-4 text-right">Jumlah Pendaftar</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($chartProfil['top_sekolah'] as $i => $sek)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="py-3 px-4 font-medium">{{ $i + 1 }}</td>
                                <td class="py-3 px-4 font-medium text-gray-800">{{ $sek['asal_sekolah'] }}</td>
                                <td class="py-3 px-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">{{ $sek['jenis_sekolah'] }}</span>
                                </td>
                                <td class="py-3 px-4 text-right">{{ number_format($sek['total']) }} org</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="py-6 text-center text-gray-400">Data sekolah tidak tersedia.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ── TAB 2: WILAYAH ──────────────────────────────────────────────── --}}
        <div x-show="activeTab === 'wilayah'" x-transition.opacity style="display: none;">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="p-4 border-b border-gray-200 bg-gray-50">
                        <h2 class="text-sm font-semibold text-gray-800">🗺️ Peta Sebaran Asal Provinsi</h2>
                        <p class="text-xs text-gray-500 mt-0.5">Intensitas warna menunjukkan konsentrasi asal pendaftar</p>
                    </div>
                    <div id="mapWilayah" wire:ignore style="width: 100%; height: 500px; z-index: 1;"></div>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden flex flex-col h-full">
                    <div class="p-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                        <h2 class="text-sm font-semibold text-gray-800">
                            @if($selectedProvinsi)
                                Kabupaten di {{ $selectedProvinsi }}
                            @else
                                📊 Top 10 Kab/Kota
                            @endif
                        </h2>
                        @if($selectedProvinsi)
                            <button wire:click="resetProvinsi" class="text-xs text-blue-600 hover:text-blue-800 font-medium">← Kembali</button>
                        @endif
                    </div>
                    <div class="p-0 overflow-y-auto max-h-[440px]">
                        <table class="w-full text-left text-sm text-gray-600">
                            <tbody>
                                @forelse($chartWilayah['topKabupaten'] as $i => $kab)
                                <tr wire:click="selectKabupaten('{{ addslashes($kab['kabupaten_kota']) }}')" class="border-b border-gray-100 last:border-0 hover:bg-blue-50 cursor-pointer transition">
                                    <td class="py-3 px-4 font-medium">{{ $i + 1 }}. {{ $kab['nama'] }}</td>
                                    <td class="py-3 px-4 text-right">{{ number_format($kab['total']) }} org</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="2" class="py-6 text-center text-gray-400">Belum ada data wilayah.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── TAB 3: UKT ──────────────────────────────────────────────────── --}}
        <div x-show="activeTab === 'ukt'" x-transition.opacity style="display: none;">

            {{-- Row: Pie + Sub-tab panel --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- Kiri: Pie Chart --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 flex flex-col">
                    <div class="mb-4">
                        <h2 class="text-sm font-semibold text-gray-800">🥧 Distribusi Kelompok UKT</h2>
                        <p class="text-xs text-gray-400 mt-0.5">Proporsi pendaftar berdasarkan kelompok UKT</p>
                    </div>
                    <div style="position:relative; height:240px;">
                        <canvas id="chartUkt" x-ref="chartUkt"></canvas>
                    </div>
                    {{-- Legend total --}}
                    <div class="mt-4 pt-3 border-t border-gray-100 grid grid-cols-2 gap-1">
                        @foreach($chartUkt['chartPie']['labels'] as $i => $label)
                        <div class="flex items-center gap-1.5 text-xs text-gray-600">
                            <span class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background:{{ ['#4F46E5','#06B6D4','#10B981','#F59E0B','#F43F5E','#8B5CF6','#EC4899','#6366F1','#84CC16','#F97316'][$i % 10] }}"></span>
                            <span>{{ $label }}: <b>{{ number_format($chartUkt['chartPie']['data'][$i] ?? 0) }}</b></span>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Kanan: Sub-tabs panel (span 2 kolom) --}}
                <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 shadow-sm flex flex-col" x-data="{uktTab: 'jalur'}">

                    {{-- Sub-tab selector --}}
                    <div class="flex gap-1 flex-wrap p-3 border-b border-gray-100 bg-gray-50 rounded-t-xl">
                        @php
                            $uktTabs = [
                                ['id'=>'jalur',    'label'=>'Per Jalur'],
                                ['id'=>'provinsi', 'label'=>'Per Provinsi'],
                                ['id'=>'kab',      'label'=>'Per Kabupaten'],
                                ['id'=>'prodi',    'label'=>'Per Prodi'],
                            ];
                        @endphp
                        @foreach($uktTabs as $t)
                        <button @click="uktTab='{{ $t['id'] }}'"
                            :class="uktTab==='{{ $t['id'] }}' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200'"
                            class="px-3 py-1.5 rounded-lg text-xs font-medium transition">
                            {{ $t['label'] }}
                        </button>
                        @endforeach
                    </div>

                    <div class="overflow-auto flex-1">

                        {{-- 1. Per Jalur Masuk --}}
                        <div x-show="uktTab==='jalur'">
                            @include('livewire.partials.ukt-table', [
                                'title'    => 'Distribusi UKT per Jalur Masuk',
                                'rows'     => $chartUkt['crosstab'],
                                'labelKey' => 'jalur',
                                'cols'     => $chartUkt['uktList']->map(fn($u) => $u->kelompok)->all(),
                                'colPrefix'=> 'UKT ',
                            ])
                        </div>

                        {{-- 2. Per Provinsi --}}
                        <div x-show="uktTab==='provinsi'" style="display:none;">
                            @include('livewire.partials.ukt-table', [
                                'title'    => 'Distribusi UKT per Provinsi',
                                'rows'     => $chartUkt['perProvinsi'],
                                'labelKey' => 'label',
                                'cols'     => $chartUkt['uktList']->map(fn($u) => $u->kelompok)->all(),
                                'colPrefix'=> 'UKT ',
                            ])
                        </div>

                        {{-- 3. Per Kabupaten (top 25) --}}
                        <div x-show="uktTab==='kab'" style="display:none;">
                            @include('livewire.partials.ukt-table', [
                                'title'    => 'Distribusi UKT per Kabupaten/Kota (Top 25)',
                                'rows'     => $chartUkt['perKabupaten'],
                                'labelKey' => 'label',
                                'cols'     => $chartUkt['uktList']->map(fn($u) => $u->kelompok)->all(),
                                'colPrefix'=> 'UKT ',
                            ])
                        </div>

                        {{-- 4. Per Prodi --}}
                        <div x-show="uktTab==='prodi'" style="display:none;">
                            @include('livewire.partials.ukt-table', [
                                'title'    => 'Distribusi UKT per Program Studi',
                                'rows'     => $chartUkt['perProdi'],
                                'labelKey' => 'label',
                                'cols'     => $chartUkt['uktList']->map(fn($u) => $u->kelompok)->all(),
                                'colPrefix'=> 'UKT ',
                            ])
                        </div>


                    </div>
                </div>
            </div>
        </div>

        {{-- ══ SECTION HEADER: MAHASISWA ASING ══ --}}
        @if($chartAsing['totalAsing'] > 0)
        <div class="flex items-center gap-3 mb-5 mt-2">
            <div class="h-px flex-1 bg-gray-200"></div>
            <div class="flex items-center gap-2 px-3 py-1 bg-white border border-gray-200 rounded-full shadow-sm">
                <span class="text-base">🌍</span>
                <span class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Mahasiswa Asing</span>
                <span class="bg-emerald-100 text-emerald-700 text-xs font-bold px-2 py-0.5 rounded-full">{{ $chartAsing['totalAsing'] }}</span>
            </div>
            <div class="h-px flex-1 bg-gray-200"></div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">

        {{-- Card 1: Asal Negara --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <div class="mb-3">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-bold text-gray-800">🌍 Asal Negara Mahasiswa Asing</h2>
                </div>
                <div class="flex items-center gap-2 mt-1.5">
                    <span class="text-xs bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full px-2 py-0.5 font-semibold">{{ $chartAsing['totalAsing'] }} mhs</span>
                    <span class="text-xs bg-orange-50 text-orange-700 border border-orange-200 rounded-full px-2 py-0.5 font-semibold">{{ $chartAsing['jumlahNegara'] }} negara</span>
                </div>
            </div>
            <div style="position:relative; height:260px;">
                <canvas id="chartAsingNegara"></canvas>
            </div>
        </div>

        {{-- Card 2: Per Program Studi --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <div class="mb-3">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-bold text-gray-800">📚 Per Program Studi</h2>
                </div>
                <div class="flex items-center gap-2 mt-1.5">
                    <span class="text-xs bg-blue-50 text-blue-700 border border-blue-200 rounded-full px-2 py-0.5 font-semibold">{{ $chartAsing['asingLulus'] }} lulus</span>
                    <span class="text-xs bg-violet-50 text-violet-700 border border-violet-200 rounded-full px-2 py-0.5 font-semibold">{{ $chartAsing['asingRegistrasi'] }} registrasi</span>
                </div>
            </div>
            <div style="position:relative; height:260px;">
                <canvas id="chartAsingProdi"></canvas>
            </div>
        </div>

        {{-- Card 3: Tren Tahunan --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <div class="mb-3">
                <h2 class="text-sm font-bold text-gray-800">📈 Tren Mahasiswa Asing</h2>
                <p class="text-xs text-gray-400 mt-0.5">Jumlah pendaftar asing per tahun akademik</p>
            </div>
            <div style="position:relative; height:260px;">
                <canvas id="chartAsingTren"></canvas>
            </div>
        </div>

        </div>
        @endif

    </div>

    {{-- ── Modals Drill-down ──────────────────────────────────────────────── --}}
    
    {{-- Modal Rekap Kabupaten --}}
    @if($showRekapModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 transition-opacity">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg mx-4 overflow-hidden" @click.away="$wire.set('showRekapModal', false)">
            <div class="p-5 border-b border-gray-100 flex justify-between items-center bg-blue-50">
                <h3 class="text-lg font-bold text-gray-800">Rekap: {{ $selectedKabupaten }}</h3>
                <button wire:click="$set('showRekapModal', false)" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <div class="p-5">
                <div class="mb-4 text-center">
                    <p class="text-sm text-gray-500 uppercase tracking-wide">Total Pendaftar</p>
                    <p class="text-4xl font-bold text-blue-600">{{ number_format($rekapKabupaten['total'] ?? 0) }}</p>
                </div>
                
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div class="bg-gray-50 p-3 rounded-lg border border-gray-100">
                        <h4 class="text-xs font-semibold text-gray-500 mb-2">Jenis Kelamin</h4>
                        <ul class="text-sm space-y-1">
                            @foreach($rekapKabupaten['gender'] ?? [] as $jk => $count)
                            <li class="flex justify-between"><span>{{ $jk }}</span> <span class="font-medium">{{ number_format($count) }}</span></li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="bg-gray-50 p-3 rounded-lg border border-gray-100">
                        <h4 class="text-xs font-semibold text-gray-500 mb-2">Status Pendaftar</h4>
                        <ul class="text-sm space-y-1">
                            @foreach($rekapKabupaten['status'] ?? [] as $st => $count)
                            <li class="flex justify-between"><span>{{ $st }}</span> <span class="font-medium">{{ number_format($count) }}</span></li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="bg-gray-50 p-3 rounded-lg border border-gray-100">
                        <h4 class="text-xs font-semibold text-gray-500 mb-2">Jalur Seleksi</h4>
                        <ul class="text-sm space-y-1">
                            @foreach($rekapKabupaten['jalur'] ?? [] as $jl => $count)
                            <li class="flex justify-between"><span>{{ $jl }}</span> <span class="font-medium">{{ number_format($count) }}</span></li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="bg-gray-50 p-3 rounded-lg border border-gray-100">
                        <h4 class="text-xs font-semibold text-gray-500 mb-2">Kategori Sekolah</h4>
                        <ul class="text-sm space-y-1">
                            @foreach($rekapKabupaten['jenis_sekolah'] ?? [] as $js => $count)
                            <li class="flex justify-between"><span>{{ $js }}</span> <span class="font-medium">{{ number_format($count) }}</span></li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <button wire:click="showMahasiswaDetail" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2.5 rounded-xl transition shadow-sm">
                    Lihat Detail Mahasiswa
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- Modal Detail Mahasiswa --}}
    @if($showMahasiswaModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 transition-opacity">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-5xl mx-4 overflow-hidden flex flex-col max-h-[90vh]" @click.away="$wire.set('showMahasiswaModal', false)">
            <div class="p-5 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                <div>
                    <h3 class="text-lg font-bold text-gray-800">Detail Pendaftar: {{ $selectedKabupaten }}</h3>
                    <p class="text-xs text-gray-500 mt-1">Menampilkan hingga 100 pendaftar pertama</p>
                </div>
                <button wire:click="$set('showMahasiswaModal', false)" class="text-gray-400 hover:text-gray-600 text-xl">✕</button>
            </div>
            <div class="p-0 overflow-y-auto flex-1">
                <table class="w-full text-left text-sm text-gray-600 whitespace-nowrap">
                    <thead class="bg-white sticky top-0 border-b border-gray-200 text-xs uppercase text-gray-500">
                        <tr>
                            <th class="py-3 px-4 font-semibold text-center w-10">#</th>
                            <th class="py-3 px-4 font-semibold">Nama Peserta</th>
                            <th class="py-3 px-4 font-semibold">Nomor Tes/NISN</th>
                            <th class="py-3 px-4 font-semibold">Jalur</th>
                            <th class="py-3 px-4 font-semibold">Pilihan Prodi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($mahasiswaList as $i => $mhs)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="py-3 px-4 text-center text-gray-400 text-xs font-medium">{{ $i + 1 }}</td>
                            <td class="py-3 px-4 font-medium text-gray-800">{{ $mhs['nama'] ?? '-' }}</td>
                            <td class="py-3 px-4">{{ $mhs['nomor_tes'] ?? '-' }}</td>
                            <td class="py-3 px-4">{{ $mhs['nama_jalur'] ?? '-' }}</td>
                            <td class="py-3 px-4">{{ $mhs['program_studi']['nama_prodi'] ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-gray-400">Tidak ada data detail.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

</div>
