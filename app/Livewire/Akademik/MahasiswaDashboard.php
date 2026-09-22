<?php

namespace App\Livewire\Akademik;

use App\Models\DimFakultas;
use App\Models\DimKelompokUkt;
use App\Models\DimProgramStudi;
use App\Models\DimTahunAkademik;
use App\Models\DimTarifUkt;
use App\Models\Mahasiswa;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class MahasiswaDashboard extends Component
{
    use WithPagination;

    // ── Filter global ──────────────────────────────────────────────────────────
    public string $filterTahunId    = '';
    public string $filterFakultasId = '';
    public string $filterProdiId    = '';
    public string $filterStatus     = 'Aktif';

    // ── Filter tabel ───────────────────────────────────────────────────────────
    public string $search        = '';
    public int $perPage = 10;
    public string $sortBy        = 'created_at';
    public string $sortDirection = 'desc';

    public function updatingSearch()           { $this->resetPage(); }
    public function updatingFilterStatus()     { $this->resetPage(); }
    public function updatingFilterProdiId()    { $this->resetPage(); }
    public function updatingFilterTahunId()    { $this->resetPage(); }
    public function updatingFilterFakultasId() { $this->filterProdiId = ''; $this->resetPage(); }

    public function sort(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy        = $column;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    // ── Base query (semua filter kecuali status — untuk KPI card) ──────────────
    private function baseAllStatus()
    {
        return Mahasiswa::query()
            ->when($this->filterTahunId,    fn($q) => $q->where('tahun_masuk_id', $this->filterTahunId))
            ->when($this->filterProdiId,    fn($q) => $q->whereHas('calonMahasiswa',
                fn($q2) => $q2->where('program_studi_id', $this->filterProdiId)
            ))
            ->when($this->filterFakultasId && !$this->filterProdiId, fn($q) =>
                $q->whereHas('calonMahasiswa.programStudi', fn($q2) =>
                    $q2->where('fakultas_id', $this->filterFakultasId)
                )
            );
    }

    // ── Base query WITH filterStatus ───────────────────────────────────────────
    private function baseQuery()
    {
        return $this->baseAllStatus()
            ->when($this->filterStatus, fn($q) => $q->where('status_akademik', $this->filterStatus));
    }

    // ── KPI Stats ─────────────────────────────────────────────────────────────
    private function stats(): array
    {
        $byStatus = $this->baseAllStatus()
            ->selectRaw("COALESCE(status_akademik, 'Aktif') as status, count(*) as total")
            ->groupBy('status_akademik')
            ->pluck('total', 'status')
            ->toArray();

        return [
            'total'      => array_sum($byStatus),
            'aktif'      => $byStatus['Aktif'] ?? 0,
            'stop_out'   => ($byStatus['Stop Out'] ?? 0) + ($byStatus['SO'] ?? 0),
            'drop_out'   => ($byStatus['Drop Out'] ?? 0) + ($byStatus['DO'] ?? 0),
            'lulus'      => ($byStatus['Lulus'] ?? 0) + ($byStatus['Alumni'] ?? 0),
            'undur_diri' => ($byStatus['Mengundurkan Diri'] ?? 0) + ($byStatus['Undur Diri'] ?? 0),
            'by_status'  => $byStatus,
        ];
    }

    // ── Chart: Status Donut ────────────────────────────────────────────────────
    private function chartStatusDonut(array $byStatus): array
    {
        $colors = [
            'Aktif'             => '#22C55E', 'Stop Out' => '#F59E0B',
            'SO'                => '#F59E0B', 'Drop Out' => '#EF4444',
            'DO'                => '#EF4444', 'Lulus'    => '#3B82F6',
            'Alumni'            => '#3B82F6', 'Mengundurkan Diri' => '#8B5CF6',
            'Undur Diri'        => '#8B5CF6',
        ];
        $labels   = array_keys($byStatus);
        $data     = array_values($byStatus);
        $bgColors = array_map(fn($l) => $colors[$l] ?? '#94A3B8', $labels);
        return compact('labels', 'data', 'bgColors');
    }

    // ── Chart: Top 15 Prodi ────────────────────────────────────────────────────
    private function chartProdi(): array
    {
        $rows = Mahasiswa::selectRaw('calon_mahasiswas.program_studi_id, dim_program_studi.nama_prodi, COUNT(mahasiswas.id) as total')
            ->join('calon_mahasiswas',  'calon_mahasiswas.id',   '=', 'mahasiswas.calon_mahasiswa_id')
            ->join('dim_program_studi', 'dim_program_studi.id',  '=', 'calon_mahasiswas.program_studi_id')
            ->when($this->filterStatus,     fn($q) => $q->where('mahasiswas.status_akademik', $this->filterStatus))
            ->when($this->filterTahunId,    fn($q) => $q->where('mahasiswas.tahun_masuk_id', $this->filterTahunId))
            ->when($this->filterFakultasId, fn($q) => $q->where('dim_program_studi.fakultas_id', $this->filterFakultasId))
            ->when($this->filterProdiId,    fn($q) => $q->where('calon_mahasiswas.program_studi_id', $this->filterProdiId))
            ->groupBy('calon_mahasiswas.program_studi_id', 'dim_program_studi.nama_prodi')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        return [
            'labels' => $rows->pluck('nama_prodi')->toArray(),
            'data'   => $rows->pluck('total')->map(fn($v) => (int)$v)->toArray(),
        ];
    }

    // ── Chart: Tren per Angkatan ───────────────────────────────────────────────
    private function chartAngkatan(): array
    {
        $rows = Mahasiswa::selectRaw(
                'dim_tahun_akademik.tahun, ' .
                "COALESCE(mahasiswas.status_akademik, 'Aktif') as status, " .
                'COUNT(mahasiswas.id) as total'
            )
            ->join('dim_tahun_akademik', 'dim_tahun_akademik.id', '=', 'mahasiswas.tahun_masuk_id')
            ->when($this->filterFakultasId, fn($q) => $q->whereHas('calonMahasiswa.programStudi',
                fn($q2) => $q2->where('fakultas_id', $this->filterFakultasId)
            ))
            ->when($this->filterProdiId, fn($q) => $q->whereHas('calonMahasiswa',
                fn($q2) => $q2->where('program_studi_id', $this->filterProdiId)
            ))
            ->groupBy('dim_tahun_akademik.tahun', 'mahasiswas.status_akademik')
            ->orderBy('dim_tahun_akademik.tahun')
            ->get();

        $byTahun = [];
        foreach ($rows as $r) {
            $tahun = $r->tahun ?? '?';
            if (!isset($byTahun[$tahun])) {
                $byTahun[$tahun] = ['Aktif' => 0, 'Stop Out' => 0, 'Drop Out' => 0, 'Lulus' => 0, 'Lainnya' => 0];
            }
            $s = $r->status;
            if ($s === 'Aktif')                              $byTahun[$tahun]['Aktif']    += $r->total;
            elseif (in_array($s, ['Stop Out', 'SO']))        $byTahun[$tahun]['Stop Out'] += $r->total;
            elseif (in_array($s, ['Drop Out', 'DO']))        $byTahun[$tahun]['Drop Out'] += $r->total;
            elseif (in_array($s, ['Lulus', 'Alumni']))       $byTahun[$tahun]['Lulus']    += $r->total;
            else                                              $byTahun[$tahun]['Lainnya']  += $r->total;
        }
        ksort($byTahun);

        return [
            'labels'   => array_keys($byTahun),
            'aktif'    => array_column(array_values($byTahun), 'Aktif'),
            'stop_out' => array_column(array_values($byTahun), 'Stop Out'),
            'drop_out' => array_column(array_values($byTahun), 'Drop Out'),
            'lulus'    => array_column(array_values($byTahun), 'Lulus'),
            'lainnya'  => array_column(array_values($byTahun), 'Lainnya'),
        ];
    }

    // ── Chart: Gender (Donut) ──────────────────────────────────────────────────
    private function chartGender(): array
    {
        $rows = DB::table('mahasiswas as m')
            ->join('calon_mahasiswas as c', 'c.id', '=', 'm.calon_mahasiswa_id')
            ->when($this->filterStatus,     fn($q) => $q->where('m.status_akademik', $this->filterStatus))
            ->when($this->filterTahunId,    fn($q) => $q->where('m.tahun_masuk_id', $this->filterTahunId))
            ->when($this->filterProdiId,    fn($q) => $q->where('c.program_studi_id', $this->filterProdiId))
            ->when($this->filterFakultasId && !$this->filterProdiId, fn($q) =>
                $q->join('dim_program_studi as ps_g', 'c.program_studi_id', '=', 'ps_g.id')
                  ->where('ps_g.fakultas_id', $this->filterFakultasId)
            )
            ->selectRaw("COALESCE(c.jenis_kelamin, 'Tidak Diketahui') as gender, count(*) as total")
            ->groupBy('c.jenis_kelamin')
            ->get();

        $genderLabel = fn($g) => match ($g) {
            'L' => 'Laki-laki', 'P' => 'Perempuan', default => 'Tidak Diketahui'
        };
        $genderColor = fn($g) => match ($g) {
            'L' => '#3B82F6', 'P' => '#EC4899', default => '#94A3B8'
        };

        return [
            'labels'   => $rows->map(fn($r) => $genderLabel($r->gender))->toArray(),
            'data'     => $rows->pluck('total')->map(fn($v) => (int)$v)->toArray(),
            'bgColors' => $rows->map(fn($r) => $genderColor($r->gender))->toArray(),
        ];
    }

    // ── Data Wilayah (Tab 3) ───────────────────────────────────────────────────
    private function dataWilayah(): array
    {
        $baseW = fn() => DB::table('mahasiswas as m')
            ->join('calon_mahasiswas as c', 'c.id', '=', 'm.calon_mahasiswa_id')
            ->join('dim_wilayah as w', 'c.wilayah_id', '=', 'w.id')
            ->when($this->filterStatus,     fn($q) => $q->where('m.status_akademik', $this->filterStatus))
            ->when($this->filterTahunId,    fn($q) => $q->where('m.tahun_masuk_id', $this->filterTahunId))
            ->when($this->filterProdiId,    fn($q) => $q->where('c.program_studi_id', $this->filterProdiId))
            ->when($this->filterFakultasId && !$this->filterProdiId, fn($q) =>
                $q->join('dim_program_studi as ps_w', 'c.program_studi_id', '=', 'ps_w.id')
                  ->where('ps_w.fakultas_id', $this->filterFakultasId)
            )
            ->whereNotNull('c.wilayah_id');

        $provRows = $baseW()
            ->selectRaw('w.provinsi, count(*) as total')
            ->groupBy('w.provinsi')
            ->get();
        $mapData = $provRows->pluck('total', 'provinsi')->map(fn($v) => (int)$v)->toArray();

        $perProvinsi = $provRows->map(fn($r) => [
            'provinsi' => $r->provinsi,
            'total'    => (int)$r->total,
        ])->sortByDesc('total')->values()->all();

        $topKabupaten = $baseW()
            ->selectRaw('w.kabupaten_kota, w.provinsi, count(*) as total')
            ->groupBy('w.kabupaten_kota', 'w.provinsi')
            ->orderByDesc('total')
            ->limit(25)
            ->get()
            ->map(fn($r) => [
                'nama'          => $r->kabupaten_kota . ', ' . $r->provinsi,
                'kabupaten_kota'=> $r->kabupaten_kota,
                'provinsi'      => $r->provinsi,
                'total'         => (int)$r->total,
            ])->values()->all();

        return compact('mapData', 'perProvinsi', 'topKabupaten');
    }

    // ── Data UKT (Tab 4) ───────────────────────────────────────────────────────
    private function dataUkt(): array
    {
        $uktList = DimKelompokUkt::orderBy('kelompok')->get();

        $baseU = fn() => DB::table('mahasiswas as m')
            ->join('dim_kelompok_ukt as u', 'm.kelompok_ukt_id', '=', 'u.id')
            ->when($this->filterStatus,     fn($q) => $q->where('m.status_akademik', $this->filterStatus))
            ->when($this->filterTahunId,    fn($q) => $q->where('m.tahun_masuk_id', $this->filterTahunId))
            ->when($this->filterProdiId,    fn($q) => $q->where('m.program_studi_id', $this->filterProdiId))
            ->when($this->filterFakultasId && !$this->filterProdiId, fn($q) =>
                $q->join('calon_mahasiswas as c_u', 'c_u.id', '=', 'm.calon_mahasiswa_id')
                  ->join('dim_program_studi as ps_u', 'c_u.program_studi_id', '=', 'ps_u.id')
                  ->where('ps_u.fakultas_id', $this->filterFakultasId)
            )
            ->whereNotNull('m.kelompok_ukt_id');

        // Pie chart
        $pieRows = $baseU()
            ->selectRaw('u.kelompok, count(*) as total')
            ->groupBy('u.kelompok')
            ->get();

        $chartPie = [
            'labels' => $pieRows->map(fn($r) => 'Kelompok '.$r->kelompok)->toArray(),
            'data'   => $pieRows->pluck('total')->map(fn($v) => (int)$v)->toArray(),
        ];

        // Per Angkatan × UKT
        $angkatanRaw = $baseU()
            ->join('dim_tahun_akademik as ta', 'm.tahun_masuk_id', '=', 'ta.id')
            ->selectRaw('ta.tahun as label, u.kelompok, count(*) as cnt')
            ->groupBy('ta.tahun', 'u.kelompok')
            ->get();
        $perAngkatan = $this->buildUktCrosstab($angkatanRaw, 'label', $uktList);

        // Per Prodi × UKT
        $prodiRaw = DB::table('mahasiswas as m')
            ->join('calon_mahasiswas as c', 'c.id', '=', 'm.calon_mahasiswa_id')
            ->join('dim_program_studi as ps', 'c.program_studi_id', '=', 'ps.id')
            ->join('dim_kelompok_ukt as u', 'm.kelompok_ukt_id', '=', 'u.id')
            ->when($this->filterStatus,     fn($q) => $q->where('m.status_akademik', $this->filterStatus))
            ->when($this->filterTahunId,    fn($q) => $q->where('m.tahun_masuk_id', $this->filterTahunId))
            ->when($this->filterProdiId,    fn($q) => $q->where('c.program_studi_id', $this->filterProdiId))
            ->when($this->filterFakultasId, fn($q) => $q->where('ps.fakultas_id', $this->filterFakultasId))
            ->whereNotNull('m.kelompok_ukt_id')
            ->selectRaw('ps.nama_prodi as label, u.kelompok, count(*) as cnt')
            ->groupBy('ps.nama_prodi', 'u.kelompok')
            ->get();
        $perProdi = collect($this->buildUktCrosstab($prodiRaw, 'label', $uktList))
            ->sortByDesc('total')->values()->all();

        // Per Provinsi × UKT
        $provRaw = DB::table('mahasiswas as m')
            ->join('calon_mahasiswas as c', 'c.id', '=', 'm.calon_mahasiswa_id')
            ->join('dim_wilayah as w', 'c.wilayah_id', '=', 'w.id')
            ->join('dim_kelompok_ukt as u', 'm.kelompok_ukt_id', '=', 'u.id')
            ->when($this->filterStatus,     fn($q) => $q->where('m.status_akademik', $this->filterStatus))
            ->when($this->filterTahunId,    fn($q) => $q->where('m.tahun_masuk_id', $this->filterTahunId))
            ->when($this->filterProdiId,    fn($q) => $q->where('c.program_studi_id', $this->filterProdiId))
            ->when($this->filterFakultasId, fn($q) => $q->join('dim_program_studi as ps_p', 'c.program_studi_id', '=', 'ps_p.id')->where('ps_p.fakultas_id', $this->filterFakultasId))
            ->whereNotNull('m.kelompok_ukt_id')
            ->whereNotNull('c.wilayah_id')
            ->selectRaw('w.provinsi as label, u.kelompok, count(*) as cnt')
            ->groupBy('w.provinsi', 'u.kelompok')
            ->get();
        $perProvinsi = collect($this->buildUktCrosstab($provRaw, 'label', $uktList))
            ->sortByDesc('total')->values()->all();

        return compact('chartPie', 'uktList', 'perAngkatan', 'perProdi', 'perProvinsi');
    }

    /** Helper: pivot rows → crosstab array */
    private function buildUktCrosstab($rows, string $labelField, $uktList): array
    {
        $result  = [];
        $grouped = collect($rows)->groupBy($labelField);
        foreach ($grouped as $label => $items) {
            $row = ['label' => $label, 'total' => 0, 'cols' => []];
            foreach ($uktList as $u) {
                $cnt = (int)($items->where('kelompok', $u->kelompok)->first()?->cnt ?? 0);
                $row['cols'][$u->kelompok] = $cnt;
                $row['total'] += $cnt;
            }
            $result[] = $row;
        }
        return $result;
    }

    // ── Tabel mahasiswa ───────────────────────────────────────────────────────
    private function tabelMahasiswa()
    {
        $needsJoin = in_array($this->sortBy, ['nama', 'program_studi']);

        return Mahasiswa::with(['calonMahasiswa.programStudi.fakultas', 'tahunMasuk', 'kelompokUkt'])
            ->select('mahasiswas.*')
            ->when($needsJoin, fn($q) =>
                $q->leftJoin('calon_mahasiswas', 'calon_mahasiswas.id', '=', 'mahasiswas.calon_mahasiswa_id')
            )
            ->when($this->sortBy === 'program_studi', fn($q) =>
                $q->leftJoin('dim_program_studi', 'dim_program_studi.id', '=', 'calon_mahasiswas.program_studi_id')
            )
            ->when($this->filterStatus,     fn($q) => $q->where('mahasiswas.status_akademik', $this->filterStatus))
            ->when($this->filterTahunId,    fn($q) => $q->where('mahasiswas.tahun_masuk_id', $this->filterTahunId))
            ->when($this->filterProdiId,    fn($q) => $q->whereHas('calonMahasiswa',
                fn($q2) => $q2->where('program_studi_id', $this->filterProdiId)
            ))
            ->when($this->filterFakultasId && !$this->filterProdiId, fn($q) =>
                $q->whereHas('calonMahasiswa.programStudi', fn($q2) =>
                    $q2->where('fakultas_id', $this->filterFakultasId)
                )
            )
            ->when($this->search, fn($q) => $q->where(fn($q2) =>
                $q2->where('mahasiswas.nim', 'like', "%{$this->search}%")
                   ->orWhereHas('calonMahasiswa', fn($q3) =>
                       $q3->where('nama', 'like', "%{$this->search}%")
                   )
            ))
            ->when($this->sortBy === 'nim',           fn($q) => $q->orderBy('mahasiswas.nim',              $this->sortDirection))
            ->when($this->sortBy === 'nama',          fn($q) => $q->orderBy('calon_mahasiswas.nama',       $this->sortDirection))
            ->when($this->sortBy === 'program_studi', fn($q) => $q->orderBy('dim_program_studi.nama_prodi',$this->sortDirection))
            ->when($this->sortBy === 'status_akademik',fn($q)=> $q->orderBy('mahasiswas.status_akademik',  $this->sortDirection))
            ->when(!in_array($this->sortBy, ['nim','nama','program_studi','status_akademik']),
                fn($q) => $q->orderByDesc('mahasiswas.created_at')
            )
            ->paginate($this->perPage);
    }

    /** Hitung semester ke-N dari tahun masuk (format YYYYS). */
    private function hitungSemester(int $tahun): ?int
    {
        $tahunMasuk = intdiv($tahun, 10);
        $smtMasuk   = $tahun % 10;
        $now        = now();
        $tahunNow   = (int) $now->format('Y');
        $bulanNow   = (int) $now->format('m');
        $smtNow     = $bulanNow >= 9 ? 1 : 2;
        $entryAbs   = ($tahunMasuk * 2) + ($smtMasuk - 1);
        $nowAbs     = ($tahunNow * 2)   + ($smtNow - 1);
        $smt        = $nowAbs - $entryAbs + 1;
        return $smt > 0 ? $smt : null;
    }

    public function render()
    {
        $tahunList    = DimTahunAkademik::orderByDesc('tahun')->get();
        $fakultasList = DimFakultas::where('status_terkini', true)->orderBy('nama_fakultas')->get();
        $prodiList    = $this->filterFakultasId
            ? DimProgramStudi::where('fakultas_id', $this->filterFakultasId)
                ->where('status_terkini', true)->orderBy('nama_prodi')->get()
            : collect();

        $stats         = $this->stats();
        $chartDonut    = $this->chartStatusDonut($stats['by_status']);
        $chartProdi    = $this->chartProdi();
        $chartAngkatan = $this->chartAngkatan();
        $chartGender   = $this->chartGender();
        $dataWilayah   = $this->dataWilayah();
        $dataUkt       = $this->dataUkt();

        // Tarif UKT map
        $prodiKodeMap = DimProgramStudi::pluck('kode_prodi', 'id');
        $tarifMap = DimTarifUkt::all()->mapWithKeys(fn($t) => [
            "{$t->tahun_akademik_id}:{$prodiKodeMap[$t->program_studi_id]}:{$t->kelompok_ukt_id}" => $t->nominal
        ]);

        $mahasiswaList = $this->tabelMahasiswa();
        $mahasiswaList->getCollection()->transform(function ($mhs) use ($tarifMap, $prodiKodeMap) {
            $tahunVal = $mhs->tahunMasuk?->tahun;
            $mhs->semester_ke = $tahunVal ? $this->hitungSemester((int)$tahunVal) : null;
            $cama      = $mhs->calonMahasiswa;
            $prodiKode = $cama ? ($prodiKodeMap[$cama->program_studi_id] ?? null) : null;
            $tarifKey  = ($prodiKode && $mhs->kelompok_ukt_id)
                ? "{$mhs->tahun_masuk_id}:{$prodiKode}:{$mhs->kelompok_ukt_id}"
                : null;
            $mhs->nominal_ukt = ($tarifKey && isset($tarifMap[$tarifKey])) ? (float)$tarifMap[$tarifKey] : null;
            return $mhs;
        });

        $this->dispatch('dashboardAkademikUpdated',
            chartDonut:    $chartDonut,
            chartProdi:    $chartProdi,
            chartAngkatan: $chartAngkatan,
            chartGender:   $chartGender,
            dataWilayah:   $dataWilayah,
        );

        return view('livewire.akademik.mahasiswa-dashboard', compact(
            'tahunList', 'fakultasList', 'prodiList',
            'stats', 'chartDonut', 'chartProdi', 'chartAngkatan',
            'chartGender', 'dataWilayah', 'dataUkt',
            'mahasiswaList'
        ))->layout('layouts.app', ['title' => 'Dashboard Akademik']);
    }
}
