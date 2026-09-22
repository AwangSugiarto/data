<?php

namespace App\Livewire;

use App\Models\CalonMahasiswa;
use App\Models\Mahasiswa;
use App\Models\DimFakultas;
use App\Models\DimJalurSeleksi;
use App\Models\DimKelompokUkt;
use App\Models\DimProgramStudi;
use App\Models\DimTahunAkademik;
use App\Models\DimWilayah;
use App\Models\DimWarganegara;
use Livewire\Component;
use Livewire\WithPagination;

class DataIndividu extends Component
{
    use WithPagination;

    // ─── Filter ───────────────────────────────────────────────────────────────
    public int|string $tahunId    = '';
    public int|string $fakultasId = '';
    public int|string $prodiId    = '';
    public int|string $jalurId    = '';
    public string     $status     = '';
    public string     $search     = '';
    public int $perPage = 10;
    public string     $sortColumn    = 'nama';
    public string     $sortDirection = 'asc';

    // ─── Modal CRUD ───────────────────────────────────────────────────────────
    public bool $showModal   = false;
    public bool $showConfirm = false;
    public ?int $editId      = null;
    public ?int $deleteId    = null;

    // ─── Form fields ──────────────────────────────────────────────────────────
    public string     $f_nomor_pendaftaran  = '';
    public string     $f_nama              = '';
    public int|string $f_tahun_id          = '';
    public int|string $f_prodi_id          = '';
    public int|string $f_jalur_id          = '';
    public int|string $f_wilayah_id        = '';
    public int|string $f_warganegara_id    = '';
    public int|string $f_kelompok_ukt_id   = '';
    public bool       $f_is_lulus          = false;
    // Status mahasiswa (jika sudah registrasi)
    public string     $f_nim               = '';
    public string     $f_status_akademik   = '';

    protected function rules(): array
    {
        return [
            'f_nomor_pendaftaran' => 'nullable|string|max:50',
            'f_nama'              => 'nullable|string|max:255',
            'f_tahun_id'          => 'required|exists:dim_tahun_akademik,id',
            'f_prodi_id'          => 'required|exists:dim_program_studi,id',
            'f_jalur_id'          => 'nullable|exists:dim_jalur_seleksi,id',
            'f_wilayah_id'        => 'nullable|exists:dim_wilayah,id',
            'f_warganegara_id'    => 'nullable|exists:dim_warganegara,id',
            'f_kelompok_ukt_id'   => 'nullable|exists:dim_kelompok_ukt,id',
            'f_nim'               => 'nullable|string|max:50',
            'f_status_akademik'   => 'nullable|string|max:50',
        ];
    }

    public function mount(): void
    {
        $latest = DimTahunAkademik::orderByDesc('tahun')->first();
        if ($latest) $this->tahunId = $latest->id;
    }

    // Reset halaman saat filter berubah
    public function updatedSearch():     void { $this->resetPage(); }
    public function updatedTahunId():    void { $this->resetPage(); $this->prodiId = ''; }
    public function updatedFakultasId(): void { $this->resetPage(); $this->prodiId = ''; }
    public function updatedProdiId():    void { $this->resetPage(); }
    public function updatedJalurId():    void { $this->resetPage(); }
    public function updatedStatus():     void { $this->resetPage(); }

    public function sortBy(string $column): void
    {
        if ($this->sortColumn === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortColumn    = $column;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->search    = '';
        $this->fakultasId = '';
        $this->prodiId   = '';
        $this->jalurId   = '';
        $this->status    = '';
        $this->resetPage();
    }

    // ─── Query utama ──────────────────────────────────────────────────────────
    private function query()
    {
        $q = CalonMahasiswa::with(['tahunAkademik', 'programStudi.fakultas', 'jalurSeleksi', 'wilayah', 'kelompokUkt', 'mahasiswa'])
            ->when($this->tahunId,   fn($q) => $q->where('tahun_akademik_id', $this->tahunId))
            ->when($this->jalurId,   fn($q) => $q->where('jalur_seleksi_id', $this->jalurId))
            ->when($this->prodiId,   fn($q) => $q->where('program_studi_id', $this->prodiId))
            ->when($this->fakultasId && !$this->prodiId, fn($q) => $q->whereHas('programStudi', fn($q2) => $q2->where('fakultas_id', $this->fakultasId)))
            ->when($this->search, fn($q) => $q->where(fn($q2) =>
                $q2->where('nama', 'like', "%{$this->search}%")
                   ->orWhere('nomor_pendaftaran', 'like', "%{$this->search}%")
            ));

        // Filter status
        if ($this->status) {
            $q = match($this->status) {
                'daftar'            => $q->where('is_lulus', false)->doesntHave('mahasiswa'),
                'lulus'             => $q->where('is_lulus', true)->doesntHave('mahasiswa'),
                'registrasi'        => $q->whereHas('mahasiswa', fn($m) => $m->whereNotIn('status_akademik', ['Lulus', 'Stop Out', 'Drop Out', 'Mengundurkan Diri', 'Meninggal'])),
                'alumni'            => $q->whereHas('mahasiswa', fn($m) => $m->where('status_akademik', 'like', '%Lulus%')),
                'stop_out'          => $q->whereHas('mahasiswa', fn($m) => $m->where('status_akademik', 'like', '%Stop%')),
                'drop_out'          => $q->whereHas('mahasiswa', fn($m) => $m->where('status_akademik', 'like', '%Drop%')),
                default             => $q,
            };
        }

        return $q;
    }

    /**
     * Query untuk statistik header — sama dengan query() tapi
     * TANPA filter status, agar kartu ringkasan selalu menampilkan
     * gambaran lengkap sesuai filter Tahun/Jalur/Prodi/Fakultas saja.
     */
    private function statsQuery()
    {
        return CalonMahasiswa::query()
            ->when($this->tahunId,   fn($q) => $q->where('tahun_akademik_id', $this->tahunId))
            ->when($this->jalurId,   fn($q) => $q->where('jalur_seleksi_id', $this->jalurId))
            ->when($this->prodiId,   fn($q) => $q->where('program_studi_id', $this->prodiId))
            ->when($this->fakultasId && !$this->prodiId, fn($q) => $q->whereHas('programStudi', fn($q2) => $q2->where('fakultas_id', $this->fakultasId)));
    }

    // ─── CRUD ─────────────────────────────────────────────────────────────────
    public function create(): void
    {
        $this->resetForm();
        $this->editId     = null;
        $this->f_tahun_id = $this->tahunId;
        $this->showModal  = true;
    }

    public function edit(int $id): void
    {
        $row = CalonMahasiswa::with('mahasiswa')->findOrFail($id);
        $this->editId               = $id;
        $this->f_nomor_pendaftaran  = $row->nomor_pendaftaran ?? '';
        $this->f_nama               = $row->nama ?? '';
        $this->f_tahun_id           = $row->tahun_akademik_id;
        $this->f_prodi_id           = $row->program_studi_id;
        $this->f_jalur_id           = $row->jalur_seleksi_id ?? '';
        $this->f_wilayah_id         = $row->wilayah_id ?? '';
        $this->f_warganegara_id     = $row->warganegara_id ?? '';
        $this->f_kelompok_ukt_id    = $row->kelompok_ukt_id ?? '';
        $this->f_is_lulus           = (bool) $row->is_lulus;
        $this->f_nim                = $row->mahasiswa?->nim ?? '';
        $this->f_status_akademik    = $row->mahasiswa?->status_akademik ?? '';
        $this->showModal            = true;
    }

    public function save(): void
    {
        $this->validate();

        $camaData = [
            'tahun_akademik_id' => $this->f_tahun_id,
            'program_studi_id'  => $this->f_prodi_id,
            'jalur_seleksi_id'  => $this->f_jalur_id ?: null,
            'wilayah_id'        => $this->f_wilayah_id ?: null,
            'warganegara_id'    => $this->f_warganegara_id ?: null,
            'kelompok_ukt_id'   => $this->f_kelompok_ukt_id ?: null,
            'nomor_pendaftaran' => $this->f_nomor_pendaftaran ?: null,
            'nama'              => $this->f_nama ?: null,
            'is_lulus'          => $this->f_is_lulus,
        ];

        if ($this->editId) {
            $cama = CalonMahasiswa::findOrFail($this->editId);
            $cama->update($camaData);
        } else {
            $cama = CalonMahasiswa::create($camaData);
        }

        // Update/Create mahasiswa jika ada status akademik
        if ($this->f_status_akademik) {
            Mahasiswa::updateOrCreate(
                ['calon_mahasiswa_id' => $cama->id],
                [
                    'nim'             => $this->f_nim ?: ('TMP-' . $cama->id),
                    'tahun_masuk_id'  => $this->f_tahun_id,
                    'status_akademik' => $this->f_status_akademik,
                ]
            );
        }

        session()->flash('success', $this->editId ? 'Data berhasil diperbarui.' : 'Data berhasil ditambahkan.');
        $this->showModal = false;
        $this->resetForm();
    }

    public function confirmDelete(int $id): void
    {
        $this->deleteId    = $id;
        $this->showConfirm = true;
    }

    public function delete(): void
    {
        if ($this->deleteId) {
            $cama = CalonMahasiswa::findOrFail($this->deleteId);
            $cama->mahasiswa?->delete();
            $cama->delete();
            session()->flash('success', 'Data berhasil dihapus.');
        }
        $this->showConfirm = false;
        $this->deleteId    = null;
    }

    public function closeModal(): void
    {
        $this->showModal   = false;
        $this->showConfirm = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset([
            'f_nomor_pendaftaran', 'f_nama', 'f_prodi_id', 'f_jalur_id',
            'f_wilayah_id', 'f_warganegara_id', 'f_kelompok_ukt_id', 'f_nim', 'f_status_akademik',
        ]);
        $this->f_is_lulus  = false;
        $this->f_tahun_id  = $this->tahunId;
    }

    // ─── Render ───────────────────────────────────────────────────────────────
    public function render()
    {
        $rows = $this->query()->orderBy($this->sortColumn, $this->sortDirection)->paginate($this->perPage);

        // Stats ringkas untuk header — gunakan statsQuery (tanpa filter status)
        // agar kartu selalu menampilkan gambaran lengkap meski filter status aktif
        $baseQ = $this->statsQuery();

        // Total Cama (semua yang lulus seleksi)
        $cntCama = (clone $baseQ)->where('is_lulus', true)->count();

        // Pendaftar dari tabel agregat (bukan individu)
        $cntPendaftar = \App\Models\PmbPeminatAgregat::query()
            ->when($this->tahunId,    fn($q) => $q->where('tahun_akademik_id', $this->tahunId))
            ->when($this->jalurId,    fn($q) => $q->where('jalur_seleksi_id', $this->jalurId))
            ->when($this->prodiId,    fn($q) => $q->where('program_studi_id', $this->prodiId))
            ->when($this->fakultasId && !$this->prodiId, fn($q) => $q->whereHas('programStudi', fn($q2) => $q2->where('fakultas_id', $this->fakultasId)))
            ->sum('jumlah_peminat');

        // Lulus + Registrasi (sudah daftar ulang jadi mahasiswa)
        $cntLulusReg = (clone $baseQ)->where('is_lulus', true)->whereHas('mahasiswa')->count();

        // Lulus + Tidak Registrasi (lulus tapi belum/tidak daftar ulang)
        $cntLulusTidakReg = (clone $baseQ)->where('is_lulus', true)->doesntHave('mahasiswa')->count();

        // Total semua record (untuk referensi)
        $total = (clone $baseQ)->count();

        // Alias untuk backward compat di blade lama
        $cntDaftar = 0;
        $cntLulus  = $cntLulusReg;
        $cntReg    = $cntLulusReg;

        // Dropdown data
        $tahunList    = DimTahunAkademik::orderByDesc('tahun')->get();
        $fakultasList = DimFakultas::orderBy('kode_fakultas')->get();
        $prodiList    = DimProgramStudi::when($this->fakultasId, fn($q) => $q->where('fakultas_id', $this->fakultasId))
                            ->orderBy('nama_prodi')->get();
        $jalurList    = DimJalurSeleksi::orderBy('nama_jalur')->get();
        $wilayahList  = DimWilayah::orderBy('kabupaten_kota')->get();
        $negaraList   = DimWarganegara::orderBy('nama_negara')->get();
        $uktList      = DimKelompokUkt::orderBy('kelompok')->get();

        $prodiFormList = DimProgramStudi::orderBy('nama_prodi')->get();

        $statusOptions = [
            ''                => 'Semua Status',
            'daftar'          => 'Daftar',
            'lulus'           => 'Lulus',
            'registrasi'      => 'Registrasi (Aktif)',
            'alumni'          => 'Alumni',
            'stop_out'        => 'Stop Out (SO)',
            'drop_out'        => 'Drop Out (DO)',
        ];

        $statusAkademikOptions = [
            '' => '-- Tidak Ada / Masih Daftar/Lulus --',
            'Aktif'              => 'Aktif (Mahasiswa Baru/Registrasi)',
            'Lulus'              => 'Lulus (Alumni)',
            'Stop Out'           => 'Stop Out (SO)',
            'Drop Out'           => 'Drop Out (DO)',
            'Mengundurkan Diri'  => 'Mengundurkan Diri',
            'Meninggal'          => 'Meninggal',
        ];

        return view('livewire.data-individu', compact(
            'rows', 'total', 'cntDaftar', 'cntLulus', 'cntReg',
            'cntCama', 'cntPendaftar', 'cntLulusReg', 'cntLulusTidakReg',
            'tahunList', 'fakultasList', 'prodiList', 'jalurList',
            'wilayahList', 'negaraList', 'uktList', 'prodiFormList',
            'statusOptions', 'statusAkademikOptions'
        ))->layout('layouts.app', ['title' => 'Data Individu']);
    }
}
