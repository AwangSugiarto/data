<?php

namespace App\Livewire\Import;

use App\Models\ImportBatch;
use App\Services\Import\ExcelImportService;
use App\Services\Import\ExcelReaderService;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Wizard Import Excel — 4 langkah:
 *  1. Upload file
 *  2. Konfigurasi dasar (tahun, format)
 *  3. Mapping kolom
 *  4. Preview & konfirmasi
 */
class ImportWizard extends Component
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
    public string $format = 'long'; // 'long' | 'wide'

    // Preview data
    public array $headers    = [];
    public array $previewRows= [];
    public int   $totalRows  = 0;

    // Step 3: Mapping kolom (format long)
    public string $col_prodi     = '';
    public string $col_jalur     = '';
    public string $col_kuota     = '';
    public string $col_pil1      = '';
    public string $col_pil2      = '';
    public string $col_pil3      = '';
    public string $col_pil4      = '';
    public string $col_lulus     = '';
    public string $col_registrasi= '';
    public string $col_fakultas  = ''; // opsional

    // Step 3: Mapping kolom (format wide)
    public array $jalurKolom = [
        // ['jalur'=>'', 'kuota'=>'', 'pil1'=>'', 'pil2'=>'', 'pil3'=>'', 'pil4'=>'', 'lulus'=>'', 'registrasi'=>'']
    ];

    // Step 4: Hasil
    public ?array $importResult = null;
    public bool   $importing    = false;

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
        $this->tahun = '';
    }

    // ─── Step 1: Upload ──────────────────────────────────────────────────────
    public function uploadFile(): void
    {
        $this->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240', // 10MB
        ]);

        $ext  = $this->file->getClientOriginalExtension();
        $name = $this->file->getClientOriginalName();
        $path = $this->file->storeAs('imports', time() . '_' . $name, 'local');

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

            // Init wide mapping dengan 1 entry kosong
            $this->jalurKolom  = [
                ['jalur' => '', 'kuota' => '', 'pil1' => '', 'pil2' => '', 'pil3' => '', 'pil4' => '', 'lulus' => '', 'registrasi' => ''],
            ];

            $this->step = 2;
        } catch (\Throwable $e) {
            $this->addError('file', 'Gagal membaca file: ' . $e->getMessage());
        }
    }

    // ─── Step 2 → 3 ─────────────────────────────────────────────────────────
    public function goToMapping(): void
    {
        $this->validate([
            'tahun'    => 'required|numeric|digits:5',
            'format'   => 'required|in:long,wide',
        ], [
            'tahun.required' => 'Tahun Akademik belum dipilih.',
        ]);
        $this->step = 3;
    }

    // Step 3: Tambah baris jalur (format wide)
    public function addJalurKolom(): void
    {
        $this->jalurKolom[] = ['jalur' => '', 'kuota' => '', 'pil1' => '', 'pil2' => '', 'pil3' => '', 'pil4' => '', 'lulus' => '', 'registrasi' => ''];
    }

    public function removeJalurKolom(int $i): void
    {
        array_splice($this->jalurKolom, $i, 1);
    }

    // Step 3 → 4
    public function goToPreview(): void
    {
        $rules = [
            'col_prodi' => 'required',
        ];
        if ($this->format === 'long') {
            $rules['col_jalur'] = 'required';
        }
        $this->validate($rules);
        $this->step = 4;
    }

    // ─── Step 4: Eksekusi Import ─────────────────────────────────────────────
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
            'tahun'         => $this->tahun,
            'format'        => $this->format,
            'col_prodi'     => $this->col_prodi,
            'col_jalur'     => $this->col_jalur,
            'col_kuota'     => $this->col_kuota,
            'col_pil1'      => $this->col_pil1,
            'col_pil2'      => $this->col_pil2,
            'col_pil3'      => $this->col_pil3,
            'col_pil4'      => $this->col_pil4,
            'col_lulus'     => $this->col_lulus,
            'col_registrasi'=> $this->col_registrasi,
            'col_fakultas'  => $this->col_fakultas,
            'jalur_kolom'   => $this->jalurKolom,
        ];

        try {
            $importer          = app(ExcelImportService::class);
            $this->importResult= $importer->import($this->uploadedPath, $mapping, $batch->id);
            $this->step        = 5;
        } catch (\Throwable $e) {
            $batch->update(['status' => 'failed', 'catatan' => $e->getMessage()]);
            session()->flash('error', 'Import gagal: ' . $e->getMessage());
        }
    }

    // ─── Reset/Ulang ─────────────────────────────────────────────────────────
    public function restart(): void
    {
        $this->reset();
        $this->mount();
        $this->step = 1;
    }

    // Navigasi mundur
    public function goBack(): void
    {
        if ($this->step > 1) $this->step--;
    }

    public function render()
    {
        return view('livewire.import.import-wizard')
            ->layout('layouts.app', ['title' => 'Import Excel PMB']);
    }
}
