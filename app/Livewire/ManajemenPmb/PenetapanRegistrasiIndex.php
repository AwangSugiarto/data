<?php

namespace App\Livewire\ManajemenPmb;

use App\Models\CalonMahasiswa;
use App\Models\Mahasiswa;
use App\Models\DimTahunAkademik;
use App\Models\DimJalurSeleksi;
use App\Models\DimFakultas;
use App\Models\DimProgramStudi;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\UpdateStatusRegistrasiImport;
use App\Exports\TemplateRegistrasiExport;

class PenetapanRegistrasiIndex extends Component
{
    use WithPagination, WithFileUploads;

    public string $search     = '';
    public string $tahunId    = '';
    public string $jalurId    = '';
    public string $fakultasId = '';
    public string $prodiId    = '';
    public        $file;
    public string $tab        = 'list';
    public int $perPage = 10;
    public array  $selectedIds = [];
    public bool   $selectAll  = false;

    public function mount(): void
    {
        // Default ke tahun terkini
        $tahun = DimTahunAkademik::orderByDesc('tahun')->first();
        $this->tahunId = (string) ($tahun?->id ?? '');
    }

    public function updatingSearch(): void    { $this->resetPage(); $this->selectedIds = []; }
    public function updatedFakultasId(): void  { $this->prodiId = ''; $this->resetPage(); $this->selectedIds = []; }
    public function updatedJalurId(): void     { $this->resetPage(); $this->selectedIds = []; }
    public function updatedTahunId(): void     { $this->resetPage(); $this->selectedIds = []; }
    public function updatedProdiId(): void     { $this->resetPage(); $this->selectedIds = []; }

    public function updatedSelectAll(bool $val): void
    {
        // Ambil semua ID dari page saat ini
        $this->selectedIds = $val
            ? $this->buildQuery()->pluck('id')->map(fn($id) => (string) $id)->toArray()
            : [];
    }

    // ── Hapus satu ────────────────────────────────────────────────────────────
    public function hapusCama(int $id): void
    {
        $cama = CalonMahasiswa::findOrFail($id);
        $cama->mahasiswa?->delete();
        $cama->delete();
        session()->flash('success', "Data {$cama->nama} berhasil dihapus.");
        $this->selectedIds = array_filter($this->selectedIds, fn($i) => $i != $id);
    }

    // ── Hapus massal ──────────────────────────────────────────────────────────
    public function hapusMassal(): void
    {
        if (empty($this->selectedIds)) return;
        $cnt = 0;
        foreach ($this->selectedIds as $id) {
            $cama = CalonMahasiswa::find($id);
            if ($cama) {
                $cama->mahasiswa?->delete();
                $cama->delete();
                $cnt++;
            }
        }
        $this->selectedIds = [];
        $this->selectAll   = false;
        session()->flash('success', "{$cnt} data berhasil dihapus.");
    }

    // ── Tetapkan satu per satu via tombol ──────────────────────────────────────
    public function tetapkanRegistrasi(int $id): void
    {
        $cama = CalonMahasiswa::with('mahasiswa')->findOrFail($id);

        if (!$cama->is_lulus) {
            session()->flash('error', 'Peserta belum berstatus Lulus, tidak bisa ditetapkan Registrasi.');
            return;
        }

        Mahasiswa::updateOrCreate(
            ['calon_mahasiswa_id' => $cama->id],
            ['status_akademik' => 'Aktif']
        );

        session()->flash('success', "Status {$cama->nama} berhasil ditetapkan sebagai Registrasi!");
    }

    // ── Batalkan registrasi ────────────────────────────────────────────────────
    public function batalkanRegistrasi(int $id): void
    {
        $cama = CalonMahasiswa::findOrFail($id);
        $cama->mahasiswa?->delete();
        session()->flash('success', "Registrasi {$cama->nama} berhasil dibatalkan.");
    }

    // ── Import massal via Excel ────────────────────────────────────────────────
    public function importStatus(): void
    {
        $this->validate(['file' => 'required|mimes:xlsx,xls,csv|max:10240']);

        $import = new UpdateStatusRegistrasiImport;
        Excel::import($import, $this->file);

        $created = $import->getCreated();
        $updated = $import->getUpdated();
        $skipped = $import->getSkipped();
        session()->flash('success', "Import selesai: {$created} mahasiswa baru diregistrasi, {$updated} diperbarui, {$skipped} dilewati.");
        $this->reset('file');
        $this->tab = 'list';
    }

    public function downloadTemplate()
    {
        return Excel::download(new TemplateRegistrasiExport, 'template_update_registrasi.xlsx');
    }

    public function render()
    {
        $tahunList    = DimTahunAkademik::orderByDesc('tahun')->get();
        $jalurList    = DimJalurSeleksi::orderBy('nama_jalur')->get();
        $fakultasList = DimFakultas::orderBy('kode_fakultas')->get();
        $prodiList    = DimProgramStudi::when($this->fakultasId, fn($q) => $q->where('fakultas_id', $this->fakultasId))
                            ->orderBy('nama_prodi')->get();

        $camaList = $this->buildQuery()
            ->with(['programStudi.fakultas', 'tahunAkademik', 'jalurSeleksi', 'mahasiswa'])
            ->paginate($this->perPage);

        return view('livewire.manajemen-pmb.penetapan-registrasi-index', compact(
            'camaList', 'tahunList', 'jalurList', 'fakultasList', 'prodiList'
        ))->layout('layouts.app', ['title' => 'Penetapan Registrasi']);
    }

    private function buildQuery()
    {
        return CalonMahasiswa::query()
            ->where('is_lulus', true)
            ->when($this->tahunId,    fn($q) => $q->where('tahun_akademik_id', $this->tahunId))
            ->when($this->jalurId,    fn($q) => $q->where('jalur_seleksi_id', $this->jalurId))
            ->when($this->prodiId,    fn($q) => $q->where('program_studi_id', $this->prodiId))
            ->when($this->fakultasId && !$this->prodiId,
                   fn($q) => $q->whereHas('programStudi', fn($q2) => $q2->where('fakultas_id', $this->fakultasId)))
            ->when($this->search, fn($q) => $q->where(fn($q2) =>
                $q2->where('nama', 'like', "%{$this->search}%")
                   ->orWhere('nomor_pendaftaran', 'like', "%{$this->search}%")
            ))
            ->orderBy('nama');
    }
}
