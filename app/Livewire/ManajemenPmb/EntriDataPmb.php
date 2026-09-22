<?php

namespace App\Livewire\ManajemenPmb;

use App\Exports\TemplateCamaExport;
use App\Imports\CamaImport;
use App\Models\CalonMahasiswa;
use App\Models\DimFakultas;
use App\Models\DimJalurSeleksi;
use App\Models\DimKelompokUkt;
use App\Models\DimProgramStudi;
use App\Models\DimTahunAkademik;
use App\Models\DimWilayah;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class EntriDataPmb extends Component
{
    use WithFileUploads, WithPagination;

    // ─── Tab aktif ────────────────────────────────────────────────────────────
    public string $tab = 'manual';

    // ─── Filter tabel riwayat ─────────────────────────────────────────────────
    public string $fTahunId    = '';
    public string $fJalurId    = '';
    public string $fFakultasId = '';
    public string $fProdiId    = '';
    public string $fSearch     = '';
    public int $perPage = 10;

    // ─── Form entri manual ────────────────────────────────────────────────────
    public ?int   $editId          = null;
    public string $f_tahun_id      = '';
    public string $f_jalur_id      = '';
    public string $f_fakultas_id   = '';
    public string $f_prodi_id      = '';
    public string $f_wilayah_id    = '';
    public string $f_ukt_id        = '';
    public string $f_nomor         = '';
    public string $f_nama          = '';
    public bool   $f_is_lulus      = true;

    // ─── Form import Excel ────────────────────────────────────────────────────
    public string $i_tahun_id = '';
    public string $i_jalur_id = '';
    public        $i_file;

    // ─── Dropdown data ────────────────────────────────────────────────────────
    public $tahunList;
    public $jalurList;
    public $fakultasList;
    public $prodiList    = [];
    public $wilayahList;
    public $uktList;

    public function mount(): void
    {
        $this->tahunList    = DimTahunAkademik::orderByDesc('tahun')->get();
        $this->jalurList    = DimJalurSeleksi::where('status_aktif', true)->orderBy('nama_jalur')->get();
        $this->fakultasList = DimFakultas::where('status_terkini', true)->orderBy('kode_fakultas')->get();
        $this->wilayahList  = DimWilayah::orderBy('provinsi')->orderBy('kabupaten_kota')->get();
        $this->uktList      = DimKelompokUkt::orderBy('kelompok')->get();

        // Default tahun terkini
        $tahun = $this->tahunList->first();
        if ($tahun) {
            $this->f_tahun_id = (string) $tahun->id;
            $this->i_tahun_id = (string) $tahun->id;
            $this->fTahunId   = (string) $tahun->id;
        }
    }

    // ─── Update prodi saat fakultas dipilih ───────────────────────────────────
    public function updatedFFakultasId(string $val): void
    {
        $this->prodiList  = $val ? DimProgramStudi::where('fakultas_id', $val)->orderBy('nama_prodi')->get() : [];
        $this->f_prodi_id = '';
    }

    public function updatingFSearch(): void { $this->resetPage(); }

    // ─── Simpan manual ────────────────────────────────────────────────────────
    public function saveManual(): void
    {
        $this->validate([
            'f_tahun_id'  => 'required|exists:dim_tahun_akademik,id',
            'f_jalur_id'  => 'required|exists:dim_jalur_seleksi,id',
            'f_prodi_id'  => 'required|exists:dim_program_studi,id',
            'f_nomor'     => 'required|string|max:50',
            'f_nama'      => 'required|string|max:255',
            'f_is_lulus'  => 'boolean',
        ]);

        CalonMahasiswa::updateOrCreate(
            [
                'nomor_pendaftaran' => $this->f_nomor,
                'tahun_akademik_id' => (int) $this->f_tahun_id,
            ],
            [
                'jalur_seleksi_id' => (int) $this->f_jalur_id,
                'program_studi_id' => (int) $this->f_prodi_id,
                'wilayah_id'       => $this->f_wilayah_id ? (int) $this->f_wilayah_id : null,
                'kelompok_ukt_id'  => $this->f_ukt_id ? (int) $this->f_ukt_id : null,
                'nama'             => $this->f_nama,
                'is_lulus'         => $this->f_is_lulus,
            ]
        );

        session()->flash('success', "Data {$this->f_nama} berhasil disimpan.");
        $this->resetManualForm();
        $this->tab = 'riwayat';
    }

    public function editCama(int $id): void
    {
        $cama = CalonMahasiswa::with('programStudi')->findOrFail($id);
        $this->editId        = $id;
        $this->f_tahun_id    = (string) $cama->tahun_akademik_id;
        $this->f_jalur_id    = (string) ($cama->jalur_seleksi_id ?? '');
        $this->f_prodi_id    = (string) ($cama->program_studi_id ?? '');
        $this->f_fakultas_id = (string) ($cama->programStudi?->fakultas_id ?? '');
        $this->f_wilayah_id  = (string) ($cama->wilayah_id ?? '');
        $this->f_ukt_id      = (string) ($cama->kelompok_ukt_id ?? '');
        $this->f_nomor       = (string) ($cama->nomor_pendaftaran ?? '');
        $this->f_nama        = $cama->nama ?? '';
        $this->f_is_lulus    = (bool) $cama->is_lulus;

        if ($this->f_fakultas_id) {
            $this->prodiList = DimProgramStudi::where('fakultas_id', $this->f_fakultas_id)->orderBy('nama_prodi')->get();
        }
        $this->tab = 'manual';
    }

    public function deleteCama(int $id): void
    {
        $cama = CalonMahasiswa::findOrFail($id);
        $cama->mahasiswa?->delete();
        $cama->delete();
        session()->flash('success', "Data berhasil dihapus.");
    }

    private function resetManualForm(): void
    {
        $this->editId        = null;
        $this->f_jalur_id    = '';
        $this->f_prodi_id    = '';
        $this->f_fakultas_id = '';
        $this->f_wilayah_id  = '';
        $this->f_ukt_id      = '';
        $this->f_nomor       = '';
        $this->f_nama        = '';
        $this->f_is_lulus    = true;
        $this->prodiList     = [];
    }

    // ─── Import Excel ─────────────────────────────────────────────────────────
    public function importExcel(): void
    {
        $this->validate([
            'i_file'     => 'required|mimes:xlsx,xls,csv|max:10240',
            'i_tahun_id' => 'required|exists:dim_tahun_akademik,id',
        ]);

        $import = new CamaImport((int) $this->i_tahun_id, $this->i_jalur_id ? (int) $this->i_jalur_id : null);
        Excel::import($import, $this->i_file);

        $ins  = $import->getInserted();
        $upd  = $import->getUpdated();
        $skip = $import->getSkipped();
        session()->flash('success', "Import selesai: {$ins} baru ditambah, {$upd} diperbarui, {$skip} dilewati.");

        $this->reset('i_file');
        $this->tab = 'riwayat';
    }

    public function downloadTemplate()
    {
        return Excel::download(new TemplateCamaExport, 'template_import_kelulusan.xlsx');
    }

    // ─── Render ───────────────────────────────────────────────────────────────
    public function render()
    {
        $riwayat = CalonMahasiswa::with(['tahunAkademik', 'programStudi.fakultas', 'jalurSeleksi', 'mahasiswa'])
            ->when($this->fTahunId,    fn($q) => $q->where('tahun_akademik_id', $this->fTahunId))
            ->when($this->fJalurId,    fn($q) => $q->where('jalur_seleksi_id', $this->fJalurId))
            ->when($this->fProdiId,    fn($q) => $q->where('program_studi_id', $this->fProdiId))
            ->when($this->fFakultasId && !$this->fProdiId,
                   fn($q) => $q->whereHas('programStudi', fn($q2) => $q2->where('fakultas_id', $this->fFakultasId)))
            ->when($this->fSearch, fn($q) => $q->where(fn($q2) =>
                $q2->where('nama', 'like', "%{$this->fSearch}%")
                   ->orWhere('nomor_pendaftaran', 'like', "%{$this->fSearch}%")
            ))
            ->orderByDesc('created_at')
            ->paginate($this->perPage);

        $prodiFilterList = $this->fFakultasId
            ? DimProgramStudi::where('fakultas_id', $this->fFakultasId)->orderBy('nama_prodi')->get()
            : DimProgramStudi::orderBy('nama_prodi')->get();

        return view('livewire.manajemen-pmb.entri-data-pmb', compact('riwayat', 'prodiFilterList'))
            ->layout('layouts.app', ['title' => 'Entri Data Kelulusan PMB']);
    }
}
