<?php

namespace App\Livewire\Akademik;

use App\Models\DimProgramStudi;
use App\Models\DimStatusMahasiswa;
use App\Models\DimTahunAkademik;
use App\Models\DimTarifUkt;
use App\Models\Mahasiswa;
use Livewire\Component;
use Livewire\WithPagination;

class MahasiswaIndex extends Component
{
    use WithPagination;

    // ── Filter infografis ─────────────────────────────────────────────────────
    public string $filterTahunId = '';      // '' = semua tahun

    // ── Filter tabel ──────────────────────────────────────────────────────────
    public string $search         = '';
    public string $filterStatus   = '';
    public string $filterProdiId  = '';
    public string $filterTahunTbl = ''; // bisa beda dari infografis
    public string $filterWarganegara = '';
    public int $perPage = 10;
    public string $sortBy        = 'created_at';
    public string $sortDirection = 'desc';

    public function updatingSearch()      { $this->resetPage(); }
    public function updatingFilterStatus(){ $this->resetPage(); }
    public function updatingFilterProdiId(){ $this->resetPage(); }
    public function updatingFilterTahunTbl(){ $this->resetPage(); }
    public function updatingFilterWarganegara(){ $this->resetPage(); }
    public function updatedFilterTahunId(){ $this->resetPage(); }

    public function sort(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy       = $column;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    // ── Update status individu ────────────────────────────────────────────────
    public function updateStatus(int $id, string $status): void
    {
        if (!auth()->user()->hasAnyRole(['super-admin', 'admin', 'admin-pmb'])) {
            session()->flash('error', 'Akses ditolak. Anda tidak memiliki izin untuk mengubah status.');
            return;
        }

        $mhs = Mahasiswa::findOrFail($id);
        $mhs->update(['status_akademik' => $status]);
        session()->flash('success', "Status {$mhs->calonMahasiswa?->nama} berhasil diubah ke {$status}.");
    }

    // ── Auto-DO: batch ubah yang sudah > 14 semester ─────────────────────────
    public function prosesAutoDo(): void
    {
        if (!auth()->user()->hasAnyRole(['super-admin', 'admin', 'admin-pmb'])) {
            session()->flash('error', 'Akses ditolak. Anda tidak memiliki izin untuk memproses auto-DO.');
            return;
        }

        $now       = now();
        $tahunNow  = (int) $now->format('Y');
        $bulanNow  = (int) $now->format('m');

        $count = 0;
        Mahasiswa::where('status_akademik', 'Aktif')
            ->with('tahunMasuk')
            ->get()
            ->each(function (Mahasiswa $mhs) use ($tahunNow, $bulanNow, &$count) {
                $tahunMasuk = (int) ($mhs->tahunMasuk?->tahun ?? 0);
                if (!$tahunMasuk) return;

                $semester = ($tahunNow - $tahunMasuk) * 2 + ($bulanNow >= 9 ? 1 : 0);
                if ($semester > 14) {
                    $mhs->update(['status_akademik' => 'Drop Out']);
                    $count++;
                }
            });

        session()->flash('success', "Proses Auto-DO selesai: {$count} mahasiswa diubah statusnya ke Drop Out.");
    }

    /**
     * Hitung semester ke-N dari tahun masuk.
     * Format tahun: YYYYS (contoh 20261 = tahun 2026, semester 1/Ganjil).
     * Semester 1 = Ganjil (mulai ~Sept), Semester 2 = Genap (mulai ~Feb).
     */
    private function semesterAbsolute(int $tahunKode): int
    {
        $year = intdiv($tahunKode, 10); // 20261 → 2026
        $smt  = $tahunKode % 10;        // 20261 → 1
        return $year * 2 + ($smt - 1);  // absolut
    }

    private function currentSemesterAbsolute(): int
    {
        $now  = now();
        $year = (int) $now->format('Y');
        $smt  = (int) $now->format('n') >= 9 ? 1 : 2; // >=Sept → Ganjil(1), lainnya Genap(2)
        return $year * 2 + ($smt - 1);
    }

    // ── Render ────────────────────────────────────────────────────────────────
    public function render()
    {
        // ── Infografis: statistik ─────────────────────────────────────────────
        $statsQuery = Mahasiswa::query()
            ->when($this->filterTahunId, fn($q) => $q->where('mahasiswas.tahun_masuk_id', $this->filterTahunId));

        $stats = [
            'aktif'             => (clone $statsQuery)->where('status_akademik', 'Aktif')->count(),
            'stop_out'          => (clone $statsQuery)->where('status_akademik', 'Stop Out')->count(),
            'drop_out'          => (clone $statsQuery)->whereIn('status_akademik', ['Drop Out'])->count(),
            'lulus'             => (clone $statsQuery)->whereIn('status_akademik', ['Lulus', 'Alumni'])->count(),
            'undur_diri'        => (clone $statsQuery)->whereIn('status_akademik', ['Mengundurkan Diri', 'Undur Diri'])->count(),
            'meninggal'         => (clone $statsQuery)->whereIn('status_akademik', ['Meninggal Dunia', 'Meninggal'])->count(),
            'lainnya'           => (clone $statsQuery)->whereIn('status_akademik', ['Lainnya'])->count(),
            'total'             => (clone $statsQuery)->count(),
        ];

        // ── Chart 1: Per-Prodi (status Aktif) ─────────────────────────────────
        $perProdi = (clone $statsQuery)
            ->where('status_akademik', 'Aktif')
            ->with('calonMahasiswa.programStudi')
            ->get()
            ->groupBy(fn($m) => $m->calonMahasiswa?->programStudi?->nama_prodi ?? 'Tidak Diketahui')
            ->map->count()
            ->sortDesc()
            ->take(12);

        // ── Chart 2: Distribusi Status ────────────────────────────────────────
        $statusDist = [
            ['Aktif',              $stats['aktif'],    '#22C55E'],
            ['Stop Out',           $stats['stop_out'], '#F59E0B'],
            ['Drop Out',           $stats['drop_out'], '#EF4444'],
            ['Lulus/Alumni',       $stats['lulus'],    '#3B82F6'],
            ['Mengundurkan Diri',  $stats['undur_diri'], '#8B5CF6'],
            ['Meninggal Dunia',    $stats['meninggal'],  '#64748B'],
            ['Lainnya',            $stats['lainnya'],  '#94A3B8'],
        ];

        // ── Chart 3: Tren Tahunan ─────────────────────────────────────────────
        $trenTahunDB = (clone $statsQuery)
            ->join('dim_tahun_akademik', 'dim_tahun_akademik.id', '=', 'mahasiswas.tahun_masuk_id')
            ->selectRaw("dim_tahun_akademik.tahun, COALESCE(status_akademik, 'Aktif') as status, count(mahasiswas.id) as total")
            ->groupBy('dim_tahun_akademik.tahun', 'status_akademik')
            ->orderBy('dim_tahun_akademik.tahun')
            ->get();
        
        $trenData = [];
        foreach($trenTahunDB as $r) {
            $t = $r->tahun ?? '?';
            $s = $r->status;
            if(!isset($trenData[$t])) $trenData[$t] = ['aktif'=>0, 'lulus'=>0, 'do'=>0];
            if($s === 'Aktif') $trenData[$t]['aktif'] += $r->total;
            elseif(in_array($s, ['Lulus', 'Alumni'])) $trenData[$t]['lulus'] += $r->total;
            elseif(in_array($s, ['Drop Out', 'DO'])) $trenData[$t]['do'] += $r->total;
        }
        $trenTahunan = [
            'labels' => array_keys($trenData),
            'aktif'  => array_column(array_values($trenData), 'aktif'),
            'lulus'  => array_column(array_values($trenData), 'lulus'),
            'do'     => array_column(array_values($trenData), 'do'),
        ];

        // ── Chart 4: Komposisi Jalur (Donut) ──────────────────────────────────
        $komposisiDB = (clone $statsQuery)
            ->join('calon_mahasiswas', 'calon_mahasiswas.id', '=', 'mahasiswas.calon_mahasiswa_id')
            ->join('dim_jalur_seleksi', 'dim_jalur_seleksi.id', '=', 'calon_mahasiswas.jalur_seleksi_id')
            ->selectRaw("dim_jalur_seleksi.nama_jalur, count(mahasiswas.id) as total")
            ->groupBy('dim_jalur_seleksi.nama_jalur')
            ->orderByDesc('total')
            ->get();
        
        $komposisiJalur = [
            'labels' => $komposisiDB->pluck('nama_jalur')->toArray(),
            'data'   => $komposisiDB->pluck('total')->toArray(),
        ];

        // ── Mahasiswa Asing ───────────────────────────────────────────────────
        $asingQuery = Mahasiswa::query()
            ->whereHas('calonMahasiswa', fn($q) =>
                $q->whereHas('warganegara', fn($q2) =>
                    $q2->where('nama_negara', '!=', 'Indonesia')
                )
            )
            ->when($this->filterTahunId, fn($q) => $q->where('tahun_masuk_id', $this->filterTahunId));

        $statsAsing = [
            'total'  => (clone $asingQuery)->count(),
            'aktif'  => (clone $asingQuery)->where('status_akademik', 'Aktif')->count(),
            'negara' => (clone $asingQuery)
                ->with('calonMahasiswa.warganegara')
                ->get()
                ->pluck('calonMahasiswa.warganegara.nama_negara')
                ->filter()
                ->unique()
                ->count(),
        ];

        // Per negara asal
        $asingPerNegara = (clone $asingQuery)
            ->with('calonMahasiswa.warganegara')
            ->get()
            ->groupBy(fn($m) => $m->calonMahasiswa?->warganegara?->nama_negara ?? 'Tidak Diketahui')
            ->map->count()
            ->sortDesc()
            ->take(15);

        // Per program studi
        $asingPerProdiRaw = (clone $asingQuery)
            ->with('calonMahasiswa.programStudi')
            ->get();

        $asingPerProdi = collect(['S1', 'S2', 'S3'])->mapWithKeys(function ($jenjang) use ($asingPerProdiRaw) {
            return [$jenjang => $asingPerProdiRaw
                ->filter(fn($m) => ($m->calonMahasiswa?->programStudi?->jenjang ?? '') === $jenjang)
                ->groupBy(fn($m) => $m->calonMahasiswa?->programStudi?->nama_prodi ?? 'Tidak Diketahui')
                ->map->count()
                ->sortDesc()
                ->take(10)];
        });

        // Per jalur seleksi
        $asingPerJalur = (clone $asingQuery)
            ->with('calonMahasiswa.jalurSeleksi')
            ->get()
            ->groupBy(fn($m) => $m->calonMahasiswa?->jalurSeleksi?->nama_jalur ?? 'Tidak Diketahui')
            ->map->count()
            ->sortDesc();

        // Tren per tahun masuk
        $asingPerTahun = (clone $asingQuery)
            ->with('tahunMasuk')
            ->get()
            ->groupBy(fn($m) => $m->tahunMasuk?->tahun ?? '?')
            ->map->count()
            ->sortKeys();

        // ── Tabel mahasiswa ───────────────────────────────────────────────────
        $needsJoin = in_array($this->sortBy, ['nama', 'nominal_ukt', 'program_studi']);
        $needsProdiJoin = $this->sortBy === 'program_studi';

        $mahasiswaList = Mahasiswa::with([
                'calonMahasiswa.programStudi.fakultas',
                'calonMahasiswa.jalurSeleksi',
                'tahunMasuk',
                'kelompokUkt',
            ])
            ->select('mahasiswas.*')
            ->when($needsJoin, fn($q) =>
                $q->leftJoin('calon_mahasiswas', 'calon_mahasiswas.id', '=', 'mahasiswas.calon_mahasiswa_id')
            )
            ->when($needsProdiJoin, fn($q) =>
                $q->leftJoin('dim_program_studi', 'dim_program_studi.id', '=', 'calon_mahasiswas.program_studi_id')
            )
            ->when($this->sortBy === 'nominal_ukt', fn($q) =>
                $q->leftJoin('dim_tarif_ukt as t_ukt', fn($j) =>
                    $j->on('t_ukt.tahun_akademik_id', '=', 'mahasiswas.tahun_masuk_id')
                      ->on('t_ukt.kelompok_ukt_id',   '=', 'mahasiswas.kelompok_ukt_id')
                      ->on('t_ukt.program_studi_id',   '=', 'calon_mahasiswas.program_studi_id')
                )->leftJoin('dim_tarif_spp as t_spp', fn($j) =>
                    $j->on('t_spp.tahun_akademik_id', '=', 'mahasiswas.tahun_masuk_id')
                      ->on('t_spp.program_studi_id',   '=', 'calon_mahasiswas.program_studi_id')
                )
            )
            ->when($this->filterTahunTbl, fn($q) => $q->where('mahasiswas.tahun_masuk_id', $this->filterTahunTbl))
            ->when($this->filterStatus,   fn($q) => $q->where('mahasiswas.status_akademik', $this->filterStatus))
            ->when($this->filterProdiId,  fn($q) => $q->whereHas('calonMahasiswa',
                fn($q2) => $q2->where('program_studi_id', $this->filterProdiId)
            ))
            ->when($this->filterWarganegara === 'lokal', fn($q) => $q->whereHas('calonMahasiswa', fn($q2) => 
                $q2->where(fn($q3) => 
                    $q3->whereHas('warganegara', fn($q4) => $q4->where('nama_negara', 'Indonesia'))
                       ->orWhereDoesntHave('warganegara')
                )
            ))
            ->when($this->filterWarganegara === 'asing', fn($q) => $q->whereHas('calonMahasiswa', fn($q2) => 
                $q2->whereHas('warganegara', fn($q3) => $q3->where('nama_negara', '!=', 'Indonesia'))
            ))
            ->when($this->search, fn($q) => $q->where(fn($q2) =>
                $q2->where('mahasiswas.nim', 'like', "%{$this->search}%")
                   ->orWhereHas('calonMahasiswa', fn($q3) =>
                       $q3->where('nama', 'like', "%{$this->search}%")
                   )
            ))
            ->when($this->sortBy === 'nim',            fn($q) => $q->orderBy('mahasiswas.nim',                $this->sortDirection))
            ->when($this->sortBy === 'nama',           fn($q) => $q->orderBy('calon_mahasiswas.nama',         $this->sortDirection))
            ->when($this->sortBy === 'program_studi',  fn($q) => $q->orderBy('dim_program_studi.nama_prodi',  $this->sortDirection))
            ->when($this->sortBy === 'tahun_masuk',    fn($q) => $q->orderBy('mahasiswas.tahun_masuk_id',     $this->sortDirection))
            ->when($this->sortBy === 'semester',       fn($q) => $q->orderBy('mahasiswas.tahun_masuk_id',     $this->sortDirection === 'asc' ? 'desc' : 'asc'))
            ->when($this->sortBy === 'kelompok_ukt',   fn($q) => $q->orderBy('mahasiswas.kelompok_ukt_id',   $this->sortDirection))
            ->when($this->sortBy === 'status_akademik',fn($q) => $q->orderBy('mahasiswas.status_akademik',   $this->sortDirection))
            ->when($this->sortBy === 'nominal_ukt',    fn($q) => $q->orderByRaw("COALESCE(t_ukt.nominal, t_spp.nominal, 0) {$this->sortDirection}"))
            ->when(!in_array($this->sortBy, ['nim','nama','program_studi','tahun_masuk','semester','kelompok_ukt','status_akademik','nominal_ukt']),
                fn($q) => $q->orderByDesc('mahasiswas.created_at')
            )
            ->paginate($this->perPage);

        // Hitung semester saat ini
        $nowAbs = $this->currentSemesterAbsolute();

        // Build tarif lookup: "tahun_id:kode_prodi:ukt_id" => nominal
        // Pakai kode_prodi sebagai jembatan agar prodi duplikat (id beda, kode sama) tetap match
        $prodis = \App\Models\DimProgramStudi::all();
        $prodiKodeMap = $prodis->pluck('kode_prodi', 'id'); // id => kode_prodi
        $prodiJenjangMap = $prodis->pluck('jenjang', 'id'); // id => jenjang

        $tarifMap = \App\Models\DimTarifUkt::all()
            ->mapWithKeys(fn($t) => [
                "{$t->tahun_akademik_id}:{$prodiKodeMap[$t->program_studi_id]}:{$t->kelompok_ukt_id}" => $t->nominal
            ]);

        $tarifSppMap = \App\Models\DimTarifSpp::all()
            ->mapWithKeys(fn($t) => [
                "{$t->tahun_akademik_id}:{$prodiKodeMap[$t->program_studi_id]}" => $t->nominal
            ]);

        $mahasiswaList->each(function (Mahasiswa $mhs) use ($nowAbs, $tarifMap, $tarifSppMap, $prodiKodeMap, $prodiJenjangMap) {
            $tahunKode = (int) ($mhs->tahunMasuk?->tahun ?? 0);
            if ($tahunKode > 0) {
                $entryAbs        = $this->semesterAbsolute($tahunKode);
                $mhs->semester_ke  = max(1, $nowAbs - $entryAbs + 1);
                $mhs->over_semester = $mhs->semester_ke > 14;
            } else {
                $mhs->semester_ke   = null;
                $mhs->over_semester = false;
            }

            // Lookup nominal UKT/SPP via kode_prodi (bridge antar prodi duplikat)
            $cama      = $mhs->calonMahasiswa;
            $prodiId   = $cama ? $cama->program_studi_id : null;
            $prodiKode = $prodiId ? ($prodiKodeMap[$prodiId] ?? null) : null;
            $jenjang   = $prodiId ? ($prodiJenjangMap[$prodiId] ?? null) : null;

            if (in_array($jenjang, ['S2', 'S3'])) {
                $tarifKey = $prodiKode ? "{$mhs->tahun_masuk_id}:{$prodiKode}" : null;
                $mhs->nominal_ukt = ($tarifKey && isset($tarifSppMap[$tarifKey]))
                    ? (float) $tarifSppMap[$tarifKey]
                    : null;
            } else {
                $tarifKey = ($prodiKode && $mhs->kelompok_ukt_id)
                    ? "{$mhs->tahun_masuk_id}:{$prodiKode}:{$mhs->kelompok_ukt_id}"
                    : null;
                $mhs->nominal_ukt = ($tarifKey && isset($tarifMap[$tarifKey]))
                    ? (float) $tarifMap[$tarifKey]
                    : null;
            }
        });

        $tahunList     = DimTahunAkademik::orderByDesc('tahun')->get();
        $prodiList     = DimProgramStudi::orderBy('nama_prodi')->get();
        $statusOptions = DimStatusMahasiswa::ordered()->get();

        // Kirim data chart ke JS via browser event (agar re-render saat filter berubah)
        $this->dispatch('chartsUpdated',
            perProdi:       $perProdi->toArray(),
            statusDist:     $statusDist,
            trenTahunan:    $trenTahunan,
            komposisiJalur: $komposisiJalur,
            asingPerNegara: $asingPerNegara->toArray(),
            asingPerProdi:  $asingPerProdi->toArray(),
            asingPerJalur:  $asingPerJalur->toArray(),
            asingPerTahun:  $asingPerTahun->toArray(),
        );

        return view('livewire.akademik.mahasiswa-index', compact(
            'stats', 'perProdi', 'statusDist', 'trenTahunan', 'komposisiJalur',
            'statsAsing', 'asingPerNegara', 'asingPerProdi', 'asingPerJalur', 'asingPerTahun',
            'mahasiswaList', 'tahunList', 'prodiList', 'statusOptions'
        ))->layout('layouts.app', ['title' => 'Dashboard Akademik']);
    }
}
