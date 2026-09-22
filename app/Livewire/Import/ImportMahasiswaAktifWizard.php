<?php

namespace App\Livewire\Import;

use App\Models\ImportBatch;
use App\Services\Import\ExcelImportMahasiswaAktifService;
use App\Services\Import\ExcelReaderService;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class ImportMahasiswaAktifWizard extends Component
{
    use WithFileUploads;

    public int $step = 1;

    // Step 1: Upload
    public $file = null;
    public string $uploadedPath = '';
    public string $uploadedExt  = '';
    public string $uploadedName = '';

    // Preview data
    public array $headers    = [];
    public array $previewRows= [];
    public int   $totalRows  = 0;

    // Step 2/3: Mapping kolom
    public string $col_nomor_tes    = '';
    public string $col_nim          = '';
    public string $col_status       = '';
    public string $col_ukt_nominal  = '';
    public string $col_tanggal_lulus= '';
    public string $col_tahun_lulus  = '';

    // Step 4: Hasil
    public ?array $importResult = null;
    public bool   $importing    = false;

    public function uploadFile(): void
    {
        $this->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240', // 10MB
        ]);

        $ext  = $this->file->getClientOriginalExtension();
        $name = $this->file->getClientOriginalName();
        $path = $this->file->storeAs('imports', time() . '_aktif_' . $name, 'local');

        $this->uploadedPath = Storage::disk('local')->path($path);
        $this->uploadedExt  = $ext;
        $this->uploadedName = $name;

        // Baca header + preview
        try {
            $reader = app(ExcelReaderService::class);
            $preview = $reader->preview($this->uploadedPath, $ext);

            $this->headers     = $preview['headers'];
            $this->previewRows = $preview['rows'];
            $this->totalRows   = $preview['total_rows'];

            $this->step = 2; // Langsung ke mapping
        } catch (\Throwable $e) {
            $this->addError('file', 'Gagal membaca file: ' . $e->getMessage());
        }
    }

    public function goToPreview(): void
    {
        $rules = [
            'col_nomor_tes' => 'required',
            'col_nim'       => 'required',
            'col_status'    => 'required',
            'col_ukt_nominal'=> 'required',
        ];
        $this->validate($rules);
        $this->step = 3; // Konfirmasi
    }

    public function runImport(): void
    {
        if (!$this->uploadedPath || !file_exists($this->uploadedPath)) {
            session()->flash('error', 'File tidak ditemukan. Mulai ulang wizard.');
            return;
        }

        $batch = ImportBatch::create([
            'nama_file_asal' => $this->uploadedName,
            'diupload_oleh'  => auth()->id(),
            'status'         => 'staged',
        ]);

        $mapping = [
            'col_nomor_tes'     => $this->col_nomor_tes,
            'col_nim'           => $this->col_nim,
            'col_status'        => $this->col_status,
            'col_ukt_nominal'   => $this->col_ukt_nominal,
            'col_tanggal_lulus' => $this->col_tanggal_lulus,
            'col_tahun_lulus'   => $this->col_tahun_lulus,
        ];

        try {
            $importer = app(ExcelImportMahasiswaAktifService::class);
            $this->importResult = $importer->import($this->uploadedPath, $mapping, $batch->id);
            $this->step = 4; // Selesai
        } catch (\Throwable $e) {
            $batch->update(['status' => 'failed', 'catatan' => $e->getMessage()]);
            session()->flash('error', 'Import gagal: ' . $e->getMessage());
        }
    }

    public function restart(): void
    {
        $this->reset();
        $this->step = 1;
    }

    public function goBack(): void
    {
        if ($this->step > 1) $this->step--;
    }

    public function render()
    {
        return view('livewire.import.import-mahasiswa-aktif-wizard');
    }
}
