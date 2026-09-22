<?php

namespace App\Livewire;

use App\Models\DimFakultas;
use App\Models\DimJalurSeleksi;
use App\Models\DimTahunAkademik;
use App\Models\FactKelulusan;
use App\Models\FactKuota;
use App\Models\FactPendaftaran;
use App\Models\FactRegistrasi;
use App\Models\FactPendaftarIndividu;
use App\Models\DimWilayah;
use App\Models\DimKelompokUkt;
use App\Models\DimWarganegara;
use App\Models\DimProgramStudi;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Dashboard extends Component
{
    // Filter global
    public int|string|null $tahunId   = '';
    public int|string|null $fakultasId = '';
    public int|string|null $prodiId    = '';
    public string|null     $jalurId    = '';

    // Filter Khusus Top Sekolah
    public string $searchSekolah = '';
    public string $filterJenisSekolah = '';

    // Filter Drill-down Wilayah
    public ?string $selectedProvinsi = null;
    public ?string $selectedKabupaten = null;
    public array $rekapKabupaten = [];
    public array $mahasiswaList = [];
    public bool $showRekapModal = false;
    public bool $showMahasiswaModal = false;

    public function mount(): void
    {
        // Default ke tahun terbaru yang memiliki data pendaftaran
        $latest = DimTahunAkademik::whereHas('factPendaftaran')->orderByDesc('tahun')->first();
        
        // Fallback jika belum ada data sama sekali
        if (!$latest) {
            $latest = DimTahunAkademik::orderByDesc('tahun')->first();
        }

        if ($latest) {
            $this->tahunId = $latest->id;
        }
    }

    public function updatedProdiId($value): void
    {
        if ($value) {
            $prodi = \App\Models\DimProgramStudi::find($value);
            if ($prodi && $prodi->fakultas_id) {
                $this->fakultasId = $prodi->fakultas_id;
            }
        }
    }

    public function resetFilters(): void
    {
        $this->fakultasId = '';
        $this->prodiId = '';
        $this->jalurId = '';
    }

    // ─── KPI Cards ────────────────────────────────────────────────────────────
    private function kpiBase()
    {
        return $this->baseQuery();
    }

    private function baseQuery($overrideTahunId = false)
    {
        $tId = $overrideTahunId !== false ? $overrideTahunId : $this->tahunId;

        return [
            'kuota'     => \App\Models\PmbDayaTampung::query()
                ->when($tId,   fn($q) => $q->where('tahun_akademik_id', $tId))
                ->when($this->fakultasId, fn($q) => $q->whereHas('programStudi', fn($q2) => $q2->where('fakultas_id', $this->fakultasId)))
                ->when($this->prodiId, fn($q) => $q->where('program_studi_id', $this->prodiId))
                ->when($this->jalurId,   fn($q) => $q->where('jalur_seleksi_id', $this->jalurId))
                ->sum('daya_tampung'),

            'pendaftar' => \App\Models\PmbPeminatAgregat::query()
                ->when($tId,   fn($q) => $q->where('tahun_akademik_id', $tId))
                ->when($this->fakultasId, fn($q) => $q->whereHas('programStudi', fn($q2) => $q2->where('fakultas_id', $this->fakultasId)))
                ->when($this->prodiId, fn($q) => $q->where('program_studi_id', $this->prodiId))
                ->when($this->jalurId,   fn($q) => $q->where('jalur_seleksi_id', $this->jalurId))
                ->sum('jumlah_peminat'),

            'lulus'     => \App\Models\CalonMahasiswa::query()
                ->where(function($q) {
                    $q->where('is_lulus', true)
                      ->orWhereHas('mahasiswa');
                })
                ->when($tId,   fn($q) => $q->where('tahun_akademik_id', $tId))
                ->when($this->fakultasId, fn($q) => $q->whereHas('programStudi', fn($q2) => $q2->where('fakultas_id', $this->fakultasId)))
                ->when($this->prodiId, fn($q) => $q->where('program_studi_id', $this->prodiId))
                ->when($this->jalurId, fn($q) => $q->where('jalur_seleksi_id', $this->jalurId))
                ->count(),

            'registrasi' => \App\Models\CalonMahasiswa::query()
                ->whereHas('mahasiswa')
                ->when($tId,   fn($q) => $q->where('tahun_akademik_id', $tId))
                ->when($this->fakultasId, fn($q) => $q->whereHas('programStudi', fn($q2) => $q2->where('fakultas_id', $this->fakultasId)))
                ->when($this->prodiId, fn($q) => $q->where('program_studi_id', $this->prodiId))
                ->when($this->jalurId, fn($q) => $q->where('jalur_seleksi_id', $this->jalurId))
                ->count(),

            'beasiswa' => \App\Models\CalonMahasiswa::query()
                ->whereHas('mahasiswa')
                ->whereHas('kelompokUkt', function($q) {
                    $q->where('kelompok', 'like', '%KIP%')->orWhere('kelompok', 'like', '%Beasiswa%');
                })
                ->when($tId,   fn($q) => $q->where('tahun_akademik_id', $tId))
                ->when($this->fakultasId, fn($q) => $q->whereHas('programStudi', fn($q2) => $q2->where('fakultas_id', $this->fakultasId)))
                ->when($this->prodiId, fn($q) => $q->where('program_studi_id', $this->prodiId))
                ->when($this->jalurId, fn($q) => $q->where('jalur_seleksi_id', $this->jalurId))
                ->count(),

            'alumni' => \App\Models\CalonMahasiswa::query()
                ->whereHas('mahasiswa', function($q2) {
                    $q2->where('status_akademik', 'Lulus');
                })
                ->when($tId,   fn($q) => $q->where('tahun_akademik_id', $tId))
                ->when($this->fakultasId, fn($q) => $q->whereHas('programStudi', fn($q2) => $q2->where('fakultas_id', $this->fakultasId)))
                ->when($this->prodiId, fn($q) => $q->where('program_studi_id', $this->prodiId))
                ->when($this->jalurId, fn($q) => $q->where('jalur_seleksi_id', $this->jalurId))
                ->count(),
        ];
    }

    // ─── Chart 1: Tren Pendaftar per Tahun ───────────────────────────────────
    private function chartTren(): array
    {
        // Ambil tahun terpilih sebagai batas atas
        $selectedTahun = $this->tahunId
            ? DimTahunAkademik::find($this->tahunId)?->tahun
            : null;

        // 5 tahun akademik terbaru ≤ tahun terpilih, urut ascending untuk tampilan tren
        $tahunList = DimTahunAkademik::query()
            ->when($selectedTahun, fn($q) => $q->where('tahun', '<=', $selectedTahun))
            ->orderByDesc('tahun')
            ->limit(5)
            ->get()
            ->sortBy('tahun'); // ascending untuk sumbu X

        $labels   = [];
        $daftar   = [];
        $lulus    = [];
        $registrasi = [];

        foreach ($tahunList as $t) {
            $labels[] = (string) $t->tahun;

            $daftar[] = \App\Models\PmbPeminatAgregat::where('tahun_akademik_id', $t->id)
                ->when($this->fakultasId, fn($q) => $q->whereHas('programStudi', fn($q2) => $q2->where('fakultas_id', $this->fakultasId)))
                ->when($this->prodiId, fn($q) => $q->where('program_studi_id', $this->prodiId))
                ->when($this->jalurId,    fn($q) => $q->where('jalur_seleksi_id', $this->jalurId))
                ->sum('jumlah_peminat');

            $lulus[] = \App\Models\CalonMahasiswa::where('tahun_akademik_id', $t->id)
                ->where(function($q) {
                    $q->where('is_lulus', true)
                      ->orWhereHas('mahasiswa');
                })
                ->when($this->fakultasId, fn($q) => $q->whereHas('programStudi', fn($q2) => $q2->where('fakultas_id', $this->fakultasId)))
                ->when($this->prodiId, fn($q) => $q->where('program_studi_id', $this->prodiId))
                ->when($this->jalurId,    fn($q) => $q->where('jalur_seleksi_id', $this->jalurId))
                ->count();

            $registrasi[] = \App\Models\CalonMahasiswa::where('tahun_akademik_id', $t->id)
                ->whereHas('mahasiswa')
                ->when($this->fakultasId, fn($q) => $q->whereHas('programStudi', fn($q2) => $q2->where('fakultas_id', $this->fakultasId)))
                ->when($this->prodiId, fn($q) => $q->where('program_studi_id', $this->prodiId))
                ->when($this->jalurId,    fn($q) => $q->where('jalur_seleksi_id', $this->jalurId))
                ->count();
        }

        return compact('labels', 'daftar', 'lulus', 'registrasi');
    }

    // ─── Chart 2: Komposisi per Jalur ────────────────────────────────────────
    private function chartJalur(): array
    {
        $jalurList = DimJalurSeleksi::orderBy('nama_jalur')->get();
        $labels = [];
        $data   = [];

        foreach ($jalurList as $j) {
            $total = FactPendaftaran::where('jalur_seleksi_id', $j->id)
                ->when($this->tahunId,    fn($q) => $q->where('tahun_akademik_id', $this->tahunId))
                ->when($this->fakultasId, fn($q) => $q->whereHas('programStudi', fn($q2) => $q2->where('fakultas_id', $this->fakultasId)))
                ->when($this->prodiId, fn($q) => $q->where('program_studi_id', $this->prodiId))
                ->sum('jumlah_daftar');
            if ($total > 0) {
                $labels[] = $j->nama_jalur;
                $data[]   = $total;
            }
        }

        return compact('labels', 'data');
    }

    // ─── Chart 3: Top 10 Prodi per Pendaftar ─────────────────────────────────
    private function chartTopProdi(): array
    {
        $rows = FactPendaftaran::query()
            ->selectRaw('program_studi_id, SUM(jumlah_daftar) as total')
            ->when($this->tahunId,    fn($q) => $q->where('tahun_akademik_id', $this->tahunId))
            ->when($this->jalurId,    fn($q) => $q->where('jalur_seleksi_id', $this->jalurId))
            ->when($this->fakultasId, fn($q) => $q->whereHas('programStudi', fn($q2) => $q2->where('fakultas_id', $this->fakultasId)))
            ->when($this->prodiId, fn($q) => $q->where('program_studi_id', $this->prodiId))
            ->with('programStudi')
            ->groupBy('program_studi_id')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        return [
            'labels' => $rows->map(fn($r) => $r->programStudi?->nama_prodi ?? 'Unknown')->all(),
            'data'   => $rows->pluck('total')->map(fn($v) => (int) $v)->all(),
        ];
    }

    // ─── Chart 4: Perbandingan Kuota vs Pendaftar vs Lulus per Fakultas ───────
    private function chartFakultas(): array
    {
        $labels     = [];
        $kuota      = [];
        $daftar     = [];
        $lulus      = [];
        $registrasi = [];

        // ── DRILL-DOWN: tampilkan per Program Studi jika Fakultas dipilih ─────
        if ($this->fakultasId) {
            $fak   = DimFakultas::find($this->fakultasId);
            $prodis = \App\Models\DimProgramStudi::where('fakultas_id', $this->fakultasId)
                ->where('status_terkini', true)
                ->when($this->prodiId, fn($q) => $q->where('id', $this->prodiId))
                ->orderBy('nama_prodi')
                ->get();

            foreach ($prodis as $prodi) {
                [$k, $d, $l, $r] = $this->getFakultasAggregates(collect([$prodi->id]));
                if ($k > 0 || $d > 0 || $l > 0 || $r > 0) {
                    $labels[]     = $prodi->nama_prodi;
                    $kuota[]      = $k;
                    $daftar[]     = $d;
                    $lulus[]      = $l;
                    $registrasi[] = $r;
                }
            }

            return compact('labels', 'kuota', 'daftar', 'lulus', 'registrasi')
                + ['mode' => 'prodi', 'judul' => 'Analisis Program Studi', 'sub' => ($fak->nama_fakultas ?? 'Fakultas Terpilih') . ' — Kuota vs Pendaftar vs Lulus vs Registrasi'];
        }

        // ── MODE NORMAL: tampilkan per Fakultas ───────────────────────────────
        foreach (DimFakultas::orderBy('nama_fakultas')->get() as $f) {
            $prodiIds = $f->programStudi()->pluck('id');
            if ($prodiIds->isEmpty()) continue;

            [$k, $d, $l, $r] = $this->getFakultasAggregates($prodiIds);
            if ($k > 0 || $d > 0 || $l > 0 || $r > 0) {
                $labels[]     = $f->kode_fakultas ?? $f->nama_fakultas;
                $kuota[]      = $k;
                $daftar[]     = $d;
                $lulus[]      = $l;
                $registrasi[] = $r;
            }
        }

        return compact('labels', 'kuota', 'daftar', 'lulus', 'registrasi')
            + ['mode' => 'fakultas', 'judul' => 'Analisis Fakultas', 'sub' => 'Kuota vs Pendaftar vs Lulus vs Registrasi'];
    }

    /** Helper: hitung agregat (kuota, daftar, lulus, registrasi) untuk kumpulan prodi ID */
    private function getFakultasAggregates(\Illuminate\Support\Collection $prodiIds): array
    {
        $k = FactKuota::whereIn('program_studi_id', $prodiIds)
            ->when($this->tahunId, fn($q) => $q->where('tahun_akademik_id', $this->tahunId))
            ->sum('kuota_akhir');

        $d = FactPendaftaran::whereIn('program_studi_id', $prodiIds)
            ->when($this->tahunId, fn($q) => $q->where('tahun_akademik_id', $this->tahunId))
            ->sum('jumlah_daftar');

        $lAgregat = FactKelulusan::whereIn('program_studi_id', $prodiIds)
            ->when($this->tahunId, fn($q) => $q->where('tahun_akademik_id', $this->tahunId))
            ->sum('jumlah_lulus');

        $lIndividu = \App\Models\CalonMahasiswa::whereIn('program_studi_id', $prodiIds)
            ->where(fn($q) => $q->where('is_lulus', true)->orWhereHas('mahasiswa'))
            ->when($this->tahunId, fn($q) => $q->where('tahun_akademik_id', $this->tahunId))
            ->count();

        $l = max((int) $lAgregat, $lIndividu);

        $rIndividu = \App\Models\CalonMahasiswa::whereIn('program_studi_id', $prodiIds)
            ->whereHas('mahasiswa')
            ->when($this->tahunId, fn($q) => $q->where('tahun_akademik_id', $this->tahunId))
            ->count();

        $rAgregat = 0;
        if (class_exists(\App\Models\FactRegistrasi::class)) {
            $rAgregat = \App\Models\FactRegistrasi::whereIn('program_studi_id', $prodiIds)
                ->when($this->tahunId, fn($q) => $q->where('tahun_akademik_id', $this->tahunId))
                ->sum('jumlah_registrasi');
        }
        $r = max((int) $rAgregat, $rIndividu);

        return [(int) $k, (int) $d, $l, $r];
    }
    // ─── Chart 5: Sebaran Wilayah Asal ───────────────────────────────────────
    private function chartWilayah(): array
    {
        $rows = FactPendaftarIndividu::query()
            ->selectRaw('dim_wilayah.provinsi, count(fact_pendaftar_individu.id) as total')
            ->join('dim_wilayah', 'dim_wilayah.id', '=', 'fact_pendaftar_individu.wilayah_id')
            ->when($this->tahunId,    fn($q) => $q->where('tahun_akademik_id', $this->tahunId))
            ->when($this->jalurId,    fn($q) => $q->where('jalur_seleksi_id', $this->jalurId))
            ->when($this->fakultasId, fn($q) => $q->whereHas('programStudi', fn($q2) => $q2->where('fakultas_id', $this->fakultasId)))
            ->when($this->prodiId, fn($q) => $q->where('program_studi_id', $this->prodiId))
            ->groupBy('dim_wilayah.provinsi')
            ->get();

        $mapData = $rows->mapWithKeys(function($row) {
            return [strtoupper($row->provinsi) => (int)$row->total];
        })->toArray();

        // Top 10 Kabupaten ATAU Semua Kabupaten di Provinsi terpilih
        $kabQuery = FactPendaftarIndividu::query()
            ->selectRaw('dim_wilayah.kabupaten_kota, dim_wilayah.provinsi, count(fact_pendaftar_individu.id) as total')
            ->join('dim_wilayah', 'dim_wilayah.id', '=', 'fact_pendaftar_individu.wilayah_id')
            ->when($this->tahunId,    fn($q) => $q->where('tahun_akademik_id', $this->tahunId))
            ->when($this->jalurId,    fn($q) => $q->where('jalur_seleksi_id', $this->jalurId))
            ->when($this->fakultasId, fn($q) => $q->whereHas('programStudi', fn($q2) => $q2->where('fakultas_id', $this->fakultasId)))
            ->when($this->prodiId, fn($q) => $q->where('program_studi_id', $this->prodiId))
            ->when($this->selectedProvinsi, fn($q) => $q->where('dim_wilayah.provinsi', $this->selectedProvinsi))
            ->groupBy('dim_wilayah.kabupaten_kota', 'dim_wilayah.provinsi')
            ->orderByDesc('total');

        if (!$this->selectedProvinsi) {
            $kabQuery->limit(10);
        }

        $topKabupaten = $kabQuery->get()
            ->map(fn($row) => [
                'nama' => $row->kabupaten_kota . ($this->selectedProvinsi ? '' : ', ' . $row->provinsi),
                'kabupaten_kota' => $row->kabupaten_kota,
                'total' => (int)$row->total
            ])->toArray();

        return compact('mapData', 'topKabupaten');
    }

    // ─── Chart 6: Sebaran UKT ──────────────────────────────────────────────
    private function chartUkt(): array
    {
        // Crosstab UKT x Jalur
        $jalurList = DimJalurSeleksi::orderBy('nama_jalur')->get();
        $uktList   = DimKelompokUkt::orderBy('kelompok')->get();

        $crosstab = [];
        foreach ($jalurList as $j) {
            $row = ['jalur' => $j->nama_jalur, 'total' => 0, 'ukts' => []];
            foreach ($uktList as $u) {
                $count = FactPendaftarIndividu::where('jalur_seleksi_id', $j->id)
                    ->where('kelompok_ukt_id', $u->id)
                    ->when($this->tahunId,    fn($q) => $q->where('tahun_akademik_id', $this->tahunId))
                    ->when($this->fakultasId, fn($q) => $q->whereHas('programStudi', fn($q2) => $q2->where('fakultas_id', $this->fakultasId)))
                    ->when($this->prodiId,    fn($q) => $q->where('program_studi_id', $this->prodiId))
                    ->count();
                $row['ukts'][$u->kelompok] = $count;
                $row['total'] += $count;
            }
            if ($row['total'] > 0) {
                $crosstab[] = $row;
            }
        }

        // Pie chart — sum per kelompok UKT dari crosstab (tidak perlu query tambahan)
        $labels = [];
        $data   = [];
        foreach ($uktList as $u) {
            $total = collect($crosstab)->sum(fn($r) => $r['ukts'][$u->kelompok] ?? 0);
            if ($total > 0) {
                $labels[] = 'Kelompok ' . $u->kelompok;
                $data[]   = $total;
            }
        }
        $chartPie = compact('labels', 'data');

        // Helper closure untuk base query individu
        $baseQ = fn() => DB::table('fact_pendaftar_individu as f')
            ->when($this->tahunId,    fn($q) => $q->where('f.tahun_akademik_id', $this->tahunId))
            ->when($this->jalurId,    fn($q) => $q->where('f.jalur_seleksi_id',  $this->jalurId))
            ->when($this->prodiId,    fn($q) => $q->where('f.program_studi_id',  $this->prodiId))
            ->when($this->fakultasId, fn($q) => $q->join('dim_program_studi as dps','f.program_studi_id','=','dps.id')->where('dps.fakultas_id', $this->fakultasId))
            ->whereNotNull('f.kelompok_ukt_id');

        // ── 2. Per Provinsi (baris: provinsi, kolom: kelompok UKT) ──────────
        $provRaw = (clone $baseQ())
            ->join('dim_wilayah as w',     'f.wilayah_id',      '=', 'w.id')
            ->join('dim_kelompok_ukt as u', 'f.kelompok_ukt_id', '=', 'u.id')
            ->whereNotNull('f.wilayah_id')
            ->selectRaw('w.provinsi, u.kelompok, count(*) as cnt')
            ->groupBy('w.provinsi','u.kelompok')
            ->get();
        $perProvinsi = $this->buildCrosstabRows($provRaw, 'provinsi', $uktList);

        // ── 3. Per Kabupaten (top 25 by total) ──────────────────────────────
        $kabRaw = (clone $baseQ())
            ->join('dim_wilayah as w',     'f.wilayah_id',      '=', 'w.id')
            ->join('dim_kelompok_ukt as u', 'f.kelompok_ukt_id', '=', 'u.id')
            ->whereNotNull('f.wilayah_id')
            ->selectRaw('CONCAT(w.kabupaten_kota, ", ", w.provinsi) as kab, u.kelompok, count(*) as cnt')
            ->groupBy('w.kabupaten_kota','w.provinsi','u.kelompok')
            ->get();
        $perKabupaten = collect($this->buildCrosstabRows($kabRaw, 'kab', $uktList))
            ->sortByDesc('total')->take(25)->values()->all();

        // ── 4. Per Prodi (baris: prodi, kolom: kelompok UKT) ────────────────
        $prodiRaw = (clone $baseQ())
            ->join('dim_program_studi as ps', 'f.program_studi_id', '=', 'ps.id')
            ->join('dim_kelompok_ukt as u',   'f.kelompok_ukt_id',  '=', 'u.id')
            ->selectRaw('ps.nama_prodi as prodi, u.kelompok, count(*) as cnt')
            ->groupBy('ps.nama_prodi','u.kelompok')
            ->get();
        $perProdi = collect($this->buildCrosstabRows($prodiRaw, 'prodi', $uktList))
            ->sortByDesc('total')->values()->all();

        return compact('chartPie','crosstab','uktList',
            'perProvinsi','perKabupaten','perProdi');
    }

    /**
     * Helper: pivot raw rows [{labelField, kelompok, cnt}] menjadi array tabel.
     * @param \Illuminate\Support\Collection $rows
     * @param string $labelField  nama kolom label (provinsi|kab|prodi)
     * @param \Illuminate\Support\Collection $cols  kolom (kelompok)
     */
    private function buildCrosstabRows($rows, string $labelField, $cols): array
    {
        $result = [];
        $grouped = $rows->groupBy($labelField);
        foreach ($grouped as $label => $items) {
            $row = ['label' => $label, 'total' => 0, 'cols' => []];
            foreach ($cols as $col) {
                $k = $col->kelompok;
                $cnt = (int)($items->where('kelompok', $k)->first()?->cnt ?? 0);
                $row['cols'][$k] = $cnt;
                $row['total'] += $cnt;
            }
            $result[] = $row;
        }
        return $result;
    }

    // ─── Chart 7: Profil Pendaftar (Gender & Sekolah) ──────────────────────
    private function chartProfil(): array
    {
        $baseQuery = FactPendaftarIndividu::query()
            ->when($this->tahunId,    fn($q) => $q->where('tahun_akademik_id', $this->tahunId))
            ->when($this->jalurId,    fn($q) => $q->where('jalur_seleksi_id', $this->jalurId))
            ->when($this->fakultasId, fn($q) => $q->whereHas('programStudi', fn($q2) => $q2->where('fakultas_id', $this->fakultasId)))
            ->when($this->prodiId, fn($q) => $q->where('program_studi_id', $this->prodiId));

        $byGender = (clone $baseQuery)
            ->selectRaw('jenis_kelamin, count(id) as total')
            ->whereNotNull('jenis_kelamin')
            ->groupBy('jenis_kelamin')
            ->get();
            
        $byJenisSekolah = (clone $baseQuery)
            ->selectRaw('jenis_sekolah, count(id) as total')
            ->whereNotNull('jenis_sekolah')
            ->groupBy('jenis_sekolah')
            ->orderByDesc('total')
            ->get();

        $topSekolah = (clone $baseQuery)
            ->selectRaw('asal_sekolah, jenis_sekolah, count(id) as total')
            ->whereNotNull('asal_sekolah')
            ->when($this->searchSekolah, fn($q) => $q->where('asal_sekolah', 'like', '%' . $this->searchSekolah . '%'))
            ->when($this->filterJenisSekolah, fn($q) => $q->where('jenis_sekolah', $this->filterJenisSekolah))
            ->groupBy('asal_sekolah', 'jenis_sekolah')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->toArray();

        // Ambil list unik jenis sekolah untuk dropdown filter
        $listJenisSekolah = FactPendaftarIndividu::whereNotNull('jenis_sekolah')->select('jenis_sekolah')->distinct()->orderBy('jenis_sekolah')->pluck('jenis_sekolah')->toArray();

        return [
            'gender' => [
                'labels' => $byGender->pluck('jenis_kelamin')->all(),
                'data' => $byGender->pluck('total')->map(fn($v) => (int)$v)->all()
            ],
            'jenis_sekolah' => [
                'labels' => $byJenisSekolah->pluck('jenis_sekolah')->all(),
                'data' => $byJenisSekolah->pluck('total')->map(fn($v) => (int)$v)->all()
            ],
            'top_sekolah' => $topSekolah,
            'list_jenis_sekolah' => $listJenisSekolah
        ];
    }

    // ─── Actions Drill-down Wilayah ──────────────────────────────────────────
    public function selectProvinsi($provinsi)
    {
        $this->selectedProvinsi = strtoupper($provinsi);
        $this->selectedKabupaten = null;
        $this->showRekapModal = false;
        $this->showMahasiswaModal = false;
    }

    public function selectKabupaten($kabupatenNama)
    {
        $this->selectedKabupaten = $kabupatenNama;
        
        $baseQuery = FactPendaftarIndividu::query()
            ->join('dim_wilayah', 'dim_wilayah.id', '=', 'fact_pendaftar_individu.wilayah_id')
            ->where('dim_wilayah.kabupaten_kota', $kabupatenNama)
            ->when($this->selectedProvinsi, fn($q) => $q->where('dim_wilayah.provinsi', $this->selectedProvinsi))
            ->when($this->tahunId,    fn($q) => $q->where('tahun_akademik_id', $this->tahunId))
            ->when($this->jalurId,    fn($q) => $q->where('jalur_seleksi_id', $this->jalurId))
            ->when($this->fakultasId, fn($q) => $q->whereHas('programStudi', fn($q2) => $q2->where('fakultas_id', $this->fakultasId)))
            ->when($this->prodiId, fn($q) => $q->where('program_studi_id', $this->prodiId));

        $total = (clone $baseQuery)->count();
        
        $byStatus = (clone $baseQuery)
            ->selectRaw('status, count(fact_pendaftar_individu.id) as count')
            ->groupBy('status')
            ->pluck('count', 'status')->toArray();
            
        $byGender = (clone $baseQuery)
            ->selectRaw('jenis_kelamin, count(fact_pendaftar_individu.id) as count')
            ->whereNotNull('jenis_kelamin')
            ->groupBy('jenis_kelamin')
            ->pluck('count', 'jenis_kelamin')->toArray();
            
        $byJenisSekolah = (clone $baseQuery)
            ->selectRaw('jenis_sekolah, count(fact_pendaftar_individu.id) as count')
            ->whereNotNull('jenis_sekolah')
            ->groupBy('jenis_sekolah')
            ->pluck('count', 'jenis_sekolah')->toArray();
            
        $byJalur = (clone $baseQuery)
            ->join('dim_jalur_seleksi', 'dim_jalur_seleksi.id', '=', 'fact_pendaftar_individu.jalur_seleksi_id')
            ->selectRaw('dim_jalur_seleksi.nama_jalur, count(fact_pendaftar_individu.id) as count')
            ->groupBy('dim_jalur_seleksi.nama_jalur')
            ->pluck('count', 'dim_jalur_seleksi.nama_jalur')->toArray();

        $this->rekapKabupaten = [
            'total' => $total,
            'status' => $byStatus,
            'gender' => $byGender,
            'jenis_sekolah' => $byJenisSekolah,
            'jalur' => $byJalur
        ];

        $this->showRekapModal = true;
    }

    public function showMahasiswaDetail()
    {
        $this->showRekapModal = false;
        
        $this->mahasiswaList = FactPendaftarIndividu::query()
            ->select('fact_pendaftar_individu.*', 'dim_wilayah.kabupaten_kota', 'dim_jalur_seleksi.nama_jalur')
            ->join('dim_wilayah', 'dim_wilayah.id', '=', 'fact_pendaftar_individu.wilayah_id')
            ->leftJoin('dim_jalur_seleksi', 'dim_jalur_seleksi.id', '=', 'fact_pendaftar_individu.jalur_seleksi_id')
            ->where('dim_wilayah.kabupaten_kota', $this->selectedKabupaten)
            ->when($this->selectedProvinsi, fn($q) => $q->where('dim_wilayah.provinsi', $this->selectedProvinsi))
            ->when($this->tahunId,    fn($q) => $q->where('tahun_akademik_id', $this->tahunId))
            ->when($this->jalurId,    fn($q) => $q->where('jalur_seleksi_id', $this->jalurId))
            ->when($this->fakultasId, fn($q) => $q->whereHas('programStudi', fn($q2) => $q2->where('fakultas_id', $this->fakultasId)))
            ->when($this->prodiId, fn($q) => $q->where('program_studi_id', $this->prodiId))
            ->with(['programStudi'])
            ->limit(100) // limit untuk performa
            ->get()
            ->toArray();
            
        $this->showMahasiswaModal = true;
    }
    
    public function resetProvinsi()
    {
        $this->selectedProvinsi = null;
        $this->selectedKabupaten = null;
    }

    public function render()
    {
        $tahunList    = DimTahunAkademik::orderByDesc('tahun')->get();
        $fakultasList = DimFakultas::where('status_terkini', true)->orderBy('nama_fakultas')->get();
        $jalurList    = DimJalurSeleksi::where('status_aktif', true)->orderBy('nama_jalur')->get();
        
        $fakultasId = $this->fakultasId;
        $prodiList = \App\Models\DimProgramStudi::where('status_terkini', true)
            ->orderBy('nama_prodi')
            ->when($fakultasId, fn($q) => $q->where('fakultas_id', $fakultasId))
            ->get();

        $kpi          = $this->baseQuery();
        
        // Calculate YoY Growth
        $kpiPrev = null;
        if ($this->tahunId) {
            $currentTahun = $tahunList->firstWhere('id', $this->tahunId);
            if ($currentTahun) {
                $prevTahun = DimTahunAkademik::where('tahun', '<', $currentTahun->tahun)
                                             ->orderByDesc('tahun')
                                             ->first();
                if ($prevTahun) {
                    $kpiPrev = $this->baseQuery($prevTahun->id);
                }
            }
        }
        
        $calcGrowth = function($current, $prev) {
            if (!$prev || $prev == 0) return null;
            return round((($current - $prev) / $prev) * 100, 1);
        };
        
        $growth = [
            'kuota'      => $kpiPrev ? $calcGrowth($kpi['kuota'], $kpiPrev['kuota']) : null,
            'pendaftar'  => $kpiPrev ? $calcGrowth($kpi['pendaftar'], $kpiPrev['pendaftar']) : null,
            'lulus'      => $kpiPrev ? $calcGrowth($kpi['lulus'], $kpiPrev['lulus']) : null,
            'registrasi' => $kpiPrev ? $calcGrowth($kpi['registrasi'], $kpiPrev['registrasi']) : null,
            'beasiswa'   => $kpiPrev ? $calcGrowth($kpi['beasiswa'], $kpiPrev['beasiswa']) : null,
        ];
        
        $chartTren    = $this->chartTren();
        $chartJalur   = $this->chartJalur();
        $chartTopProdi= $this->chartTopProdi();

        $chartFakultas= $this->chartFakultas();
        $chartWilayah = $this->chartWilayah();
        $chartUkt     = $this->chartUkt();
        $chartProfil  = $this->chartProfil();
        $chartAsing   = $this->chartAsing();

        // Rasio konversi
        $rasioLulus = $kpi['pendaftar'] > 0
            ? round($kpi['lulus'] / $kpi['pendaftar'] * 100, 1)
            : 0;
        $rasioReg   = $kpi['lulus'] > 0
            ? round($kpi['registrasi'] / $kpi['lulus'] * 100, 1)
            : 0;
        $ketatanSaing = $kpi['kuota'] > 0
            ? round($kpi['pendaftar'] / $kpi['kuota'], 1)
            : 0;
            
        $growth['rasio_lulus'] = null;
        $growth['rasio_reg'] = null;
        $growth['keketatan'] = null;
        
        if ($kpiPrev) {
            $prevRasioLulus = $kpiPrev['pendaftar'] > 0 ? round($kpiPrev['lulus'] / $kpiPrev['pendaftar'] * 100, 1) : 0;
            $prevRasioReg = $kpiPrev['lulus'] > 0 ? round($kpiPrev['registrasi'] / $kpiPrev['lulus'] * 100, 1) : 0;
            $prevKeketatan = $kpiPrev['kuota'] > 0 ? round($kpiPrev['pendaftar'] / $kpiPrev['kuota'], 1) : 0;
            
            $growth['rasio_lulus'] = $calcGrowth($rasioLulus, $prevRasioLulus);
            $growth['rasio_reg'] = $calcGrowth($rasioReg, $prevRasioReg);
            $growth['keketatan'] = $calcGrowth($ketatanSaing, $prevKeketatan);
        }

        $tahunLabel = $tahunList->firstWhere('id', $this->tahunId)?->tahun ?? 'Semua';

        return view('livewire.dashboard', [
            'tahunList' => $tahunList,
            'fakultasList' => $fakultasList,
            'jalurList' => $jalurList,
            'prodiList' => $prodiList,
            'kpi' => $kpi,
            'growth' => $growth,
            'rasioLulus' => $rasioLulus,
            'rasioReg' => $rasioReg,
            'ketatanSaing' => $ketatanSaing,
            'tahunLabel' => $tahunLabel,
            'chartTren' => $chartTren,
            'chartJalur' => $chartJalur,
            'chartTopProdi' => $chartTopProdi,
            'chartFakultas' => $chartFakultas,
            'chartWilayah' => $chartWilayah,
            'chartUkt' => $chartUkt,
            'chartProfil' => $chartProfil,
            'chartAsing' => $chartAsing
        ])->layout('layouts.app', ['title' => 'Dashboard PMB']);
    }

    // ─── Chart Mahasiswa Asing ─────────────────────────────────────────────────
    private function chartAsing(): array
    {
        // Base query: calon mahasiswa yang warganegara bukan Indonesia
        $baseAsing = \App\Models\CalonMahasiswa::query()
            ->whereHas('warganegara', fn($q) => $q->where('nama_negara', '!=', 'Indonesia'))
            ->when($this->tahunId,    fn($q) => $q->where('tahun_akademik_id', $this->tahunId))
            ->when($this->fakultasId, fn($q) => $q->whereHas('programStudi', fn($q2) => $q2->where('fakultas_id', $this->fakultasId)))
            ->when($this->prodiId,    fn($q) => $q->where('program_studi_id', $this->prodiId))
            ->when($this->jalurId,    fn($q) => $q->where('jalur_seleksi_id', $this->jalurId));

        $totalAsing    = (clone $baseAsing)->count();
        $asingLulus    = (clone $baseAsing)->where(function($q) {
            $q->where('is_lulus', true)->orWhereHas('mahasiswa');
        })->count();
        $asingRegistrasi = (clone $baseAsing)->whereHas('mahasiswa')->count();
        $jumlahNegara  = (clone $baseAsing)->distinct('warganegara_id')->count('warganegara_id');

        // Per Negara (top 10)
        $perNegara = (clone $baseAsing)
            ->join('dim_warganegara as wn', 'calon_mahasiswas.warganegara_id', '=', 'wn.id')
            ->selectRaw('wn.nama_negara, wn.kode_negara, count(*) as total')
            ->groupBy('wn.nama_negara', 'wn.kode_negara')
            ->orderByDesc('total')
            ->limit(15)
            ->get();

        // Per Prodi (top 8)
        $perProdi = (clone $baseAsing)
            ->join('dim_program_studi as ps', 'calon_mahasiswas.program_studi_id', '=', 'ps.id')
            ->selectRaw('ps.nama_prodi, ps.kode_prodi, count(*) as total')
            ->groupBy('ps.nama_prodi', 'ps.kode_prodi')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        // Per Jalur
        $perJalur = (clone $baseAsing)
            ->join('dim_jalur_seleksi as js', 'calon_mahasiswas.jalur_seleksi_id', '=', 'js.id')
            ->selectRaw('js.nama_jalur, count(*) as total')
            ->groupBy('js.nama_jalur')
            ->orderByDesc('total')
            ->get();

        // Tren per Tahun (5 tahun terakhir)
        $selectedTahun = $this->tahunId
            ? DimTahunAkademik::find($this->tahunId)?->tahun
            : null;
        $tahunList = DimTahunAkademik::query()
            ->when($selectedTahun, fn($q) => $q->where('tahun', '<=', $selectedTahun))
            ->orderByDesc('tahun')->limit(5)->get()->sortBy('tahun');

        $trenLabels = [];
        $trenData   = [];
        foreach ($tahunList as $t) {
            $trenLabels[] = (string) $t->tahun;
            $trenData[]   = \App\Models\CalonMahasiswa::whereHas('warganegara', fn($q) => $q->where('nama_negara', '!=', 'Indonesia'))
                ->where('tahun_akademik_id', $t->id)->count();
        }

        return compact(
            'totalAsing', 'asingLulus', 'asingRegistrasi', 'jumlahNegara',
            'perNegara', 'perProdi', 'perJalur', 'trenLabels', 'trenData'
        );
    }
}
