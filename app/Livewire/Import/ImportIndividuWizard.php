<?php

namespace App\Livewire\Import;

use App\Models\ImportBatch;
use App\Services\Import\ExcelImportIndividuService;
use App\Services\Import\ExcelReaderService;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class ImportIndividuWizard extends Component
{
    use WithFileUploads;

    public int $step = 1;

    // Step 1: Upload
    public $file = null;
    public string $uploadedPath = '';
    public string $uploadedExt  = '';
    public string $uploadedName = '';

    // Step 2: Konfigurasi
    public string $tahun  = '';

    // Preview data
    public array $headers    = [];
    public array $previewRows= [];
    public int   $totalRows  = 0;

    // Step 3: Mapping kolom (format long/individual)
    public string $col_nomor_tes    = '';
    public string $col_nisn         = '';
    public string $col_npsn         = '';
    public string $col_nama         = '';
    public string $col_prodi        = '';
    public string $col_jalur        = '';
    public string $col_jk           = '';
    public string $col_asal_sekolah = '';
    public string $col_jenis_sekolah= '';
    public string $col_provinsi     = '';
    public string $col_kabupaten    = '';
    public string $col_kecamatan    = '';
    public string $col_ukt          = '';
    public string $col_status       = '';
    public string $col_warganegara  = '';
    public string $col_kode_negara  = '';

    // Step 4: Hasil
    public ?array $importResult = null;
    public bool   $importing    = false;
    public int    $totalRowsToProcess = 0;
    public int    $processedRows = 0;
    public int    $batchId = 0;
    public array  $importStats = ['inserted' => 0, 'skipped' => 0, 'antrian' => 0, 'errors' => []];

    // Tahun list dari DB untuk dropdown
    public array $tahunOptions = [];

    public function mount(): void
    {
        $years = \App\Models\DimTahunAkademik::pluck('tahun')
            ->map(fn($t) => (int) floor($t / 10))
            ->unique()
            ->sortDesc()
            ->toArray();
            
        $currentYear = (int) date('Y');
        // Pastikan tahun 2020 sampai tahun depan ada di opsi
        for ($y = 2020; $y <= $currentYear + 2; $y++) {
            if (!in_array($y, $years)) {
                $years[] = $y;
            }
        }
        
        rsort($years);
            
        $this->tahunOptions = [];
        foreach($years as $y) {
            $this->tahunOptions[$y . '1'] = $y . '1 (Ganjil ' . $y . '/' . ($y+1) . ')';
            $this->tahunOptions[$y . '2'] = $y . '2 (Genap ' . $y . '/' . ($y+1) . ')';
        }
        $this->tahun = ''; // Kosongkan — user wajib memilih sendiri
    }

    public function uploadFile(): void
    {
        $this->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240', // 10MB
        ]);

        $ext  = $this->file->getClientOriginalExtension();
        $name = $this->file->getClientOriginalName();
        $path = $this->file->storeAs('imports', time() . '_ind_' . $name, 'local');

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

            $this->step = 2;
        } catch (\Throwable $e) {
            $this->addError('file', 'Gagal membaca file: ' . $e->getMessage());
        }
    }

    public function goToMapping(): void
    {
        $this->validate([
            'tahun' => 'required|numeric|digits:5'
        ]);
        $this->step = 3;
    }

    public function goToPreview(): void
    {
        $rules = [
            'col_nomor_tes' => 'required',
            'col_nama'      => 'required',
            'col_prodi'     => 'required',
            'col_jalur'     => 'required',
        ];
        $this->validate($rules);
        $this->step = 4;
    }

    public function startImport(): void
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
        
        $this->batchId = $batch->id;
        $this->importStats = ['inserted' => 0, 'skipped' => 0, 'antrian' => 0, 'errors' => []];
        $this->processedRows = 0;
        $this->importing = true;
        
        try {
            $importer = app(ExcelImportIndividuService::class);
            $this->totalRowsToProcess = $importer->stageImport($this->uploadedPath, $this->batchId);
            
            if ($this->totalRowsToProcess === 0) {
                $this->importResult = $this->importStats;
                $this->step = 5;
                $this->importing = false;
            }
        } catch (\Throwable $e) {
            $batch->update(['status' => 'failed', 'catatan' => $e->getMessage()]);
            session()->flash('error', 'Gagal melakukan staging import: ' . $e->getMessage());
            $this->importing = false;
        }
    }

    public function processNextChunk(): void
    {
        if (!$this->importing || !$this->batchId) {
            return;
        }

        $mapping = [
            'tahun'             => $this->tahun,
            'col_nomor_tes'     => $this->col_nomor_tes,
            'col_nisn'          => $this->col_nisn,
            'col_npsn'          => $this->col_npsn,
            'col_nama'          => $this->col_nama,
            'col_prodi'         => $this->col_prodi,
            'col_jalur'         => $this->col_jalur,
            'col_jk'            => $this->col_jk,
            'col_asal_sekolah'  => $this->col_asal_sekolah,
            'col_jenis_sekolah' => $this->col_jenis_sekolah,
            'col_provinsi'      => $this->col_provinsi,
            'col_kabupaten'     => $this->col_kabupaten,
            'col_kecamatan'     => $this->col_kecamatan,
            'col_ukt'           => $this->col_ukt,
            'col_status'        => $this->col_status,
            'col_warganegara'   => $this->col_warganegara,
            'col_kode_negara'   => $this->col_kode_negara,
        ];

        try {
            $importer = app(ExcelImportIndividuService::class);
            $processedCount = $importer->processChunk($this->batchId, $mapping, 100, $this->importStats);

            $this->processedRows += $processedCount;

            if ($processedCount === 0 || $this->processedRows >= $this->totalRowsToProcess) {
                // Selesai
                $importer->finalizeImport($this->batchId, $this->importStats);
                $this->importResult = $this->importStats;
                $this->step = 5;
                $this->importing = false;
            }
        } catch (\Throwable $e) {
            ImportBatch::find($this->batchId)->update(['status' => 'failed', 'catatan' => $e->getMessage()]);
            session()->flash('error', 'Gagal memproses chunk: ' . $e->getMessage());
            $this->importing = false;
        }
    }

    public function restart(): void
    {
        $this->reset();
        $this->mount();
        $this->step = 1;
    }

    public function goBack(): void
    {
        if ($this->step > 1) $this->step--;
    }

    public function render()
    {
        return view('livewire.import.import-individu-wizard');
    }
}
