<?php

namespace App\Livewire\MasterData;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\DimBeasiswa;
use App\Models\FactPenerimaBeasiswa;
use App\Models\DimTahunAkademik;
use App\Imports\PenerimaBeasiswaImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;

class BeasiswaIndex extends Component
{
    use WithPagination;
    use WithFileUploads;

    public $activeTab = 'master'; // 'master' or 'penerima'
    public $searchMaster = '';
    public $searchPenerima = '';
    public $filterTahunAkademik = '';

    // Modals
    public $showMasterModal = false;
    public $showImportModal = false;

    // Master Form
    public $masterId = null;
    public $kode_beasiswa = '';
    public $nama_beasiswa = '';
    public $keterangan = '';

    // Import
    public $importFile;

    public function mount()
    {
        $tahun = DimTahunAkademik::orderBy('id', 'desc')->first();
        if ($tahun) {
            $this->filterTahunAkademik = $tahun->id;
        }
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    // --- Master Beasiswa CRUD ---

    public function openMasterModal($id = null)
    {
        $this->resetValidation();
        $this->reset(['masterId', 'kode_beasiswa', 'nama_beasiswa', 'keterangan']);

        if ($id) {
            $beasiswa = DimBeasiswa::findOrFail($id);
            $this->masterId = $beasiswa->id;
            $this->kode_beasiswa = $beasiswa->kode_beasiswa;
            $this->nama_beasiswa = $beasiswa->nama_beasiswa;
            $this->keterangan = $beasiswa->keterangan;
        }

        $this->showMasterModal = true;
    }

    public function saveMaster()
    {
        $this->validate([
            'kode_beasiswa' => 'required|unique:dim_beasiswa,kode_beasiswa,' . $this->masterId,
            'nama_beasiswa' => 'required|string|max:255',
            'keterangan' => 'nullable|string',
        ]);

        DimBeasiswa::updateOrCreate(
            ['id' => $this->masterId],
            [
                'kode_beasiswa' => strtoupper($this->kode_beasiswa),
                'nama_beasiswa' => $this->nama_beasiswa,
                'keterangan' => $this->keterangan,
            ]
        );

        session()->flash('success', 'Jenis beasiswa berhasil disimpan.');
        $this->showMasterModal = false;
    }

    public function deleteMaster($id)
    {
        $beasiswa = DimBeasiswa::findOrFail($id);
        $beasiswa->delete();
        session()->flash('success', 'Jenis beasiswa berhasil dihapus.');
    }

    // --- Import Penerima Beasiswa ---

    public function openImportModal()
    {
        $this->resetValidation();
        $this->reset('importFile');
        $this->showImportModal = true;
    }

    public function importData()
    {
        $this->validate([
            'importFile' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            Excel::import(new PenerimaBeasiswaImport(null), $this->importFile->getRealPath());
            session()->flash('success', 'Berhasil mengimpor data penerima beasiswa.');
            $this->showImportModal = false;
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $messages = [];
            foreach ($failures as $failure) {
                $messages[] = "Baris {$failure->row()}: " . implode(', ', $failure->errors());
            }
            session()->flash('error', "Gagal mengimpor data:\n" . implode("\n", $messages));
        } catch (\Exception $e) {
            Log::error('Import Penerima Beasiswa Error: ' . $e->getMessage());
            session()->flash('error', 'Gagal mengimpor data: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        // Simple template download
        return response()->streamDownload(function () {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Header
            $sheet->setCellValue('A1', 'tahun_akademik');
            $sheet->setCellValue('B1', 'nim');
            $sheet->setCellValue('C1', 'kode_prodi');
            $sheet->setCellValue('D1', 'kode_beasiswa');
            $sheet->setCellValue('E1', 'nama_mahasiswa');
            
            // Dummy Data
            $sheet->setCellValue('A2', '2024');
            $sheet->setCellValue('B2', '123456789');
            $sheet->setCellValue('C2', '55201');
            $sheet->setCellValue('D2', 'KIP-K');
            $sheet->setCellValue('E2', 'Budi Santoso');
            
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');
        }, 'template_penerima_beasiswa.xlsx');
    }

    public function render()
    {
        $tahunList = DimTahunAkademik::orderBy('tahun', 'desc')->get();

        $masterList = DimBeasiswa::where('nama_beasiswa', 'like', "%{$this->searchMaster}%")
            ->orWhere('kode_beasiswa', 'like', "%{$this->searchMaster}%")
            ->orderBy('nama_beasiswa')
            ->paginate(10, ['*'], 'masterPage');

        $penerimaList = collect();
        if ($this->filterTahunAkademik) {
            $penerimaList = FactPenerimaBeasiswa::with(['beasiswa', 'programStudi', 'tahunAkademik'])
                ->where('tahun_akademik_id', $this->filterTahunAkademik)
                ->where(function($query) {
                    if ($this->searchPenerima) {
                        $query->where('nim', 'like', "%{$this->searchPenerima}%")
                              ->orWhere('nama_mahasiswa', 'like', "%{$this->searchPenerima}%")
                              ->orWhereHas('programStudi', function($q) {
                                  $q->where('nama_prodi', 'like', "%{$this->searchPenerima}%");
                              })
                              ->orWhereHas('beasiswa', function($q) {
                                  $q->where('nama_beasiswa', 'like', "%{$this->searchPenerima}%");
                              });
                    }
                })
                ->orderBy('id', 'desc')
                ->paginate(10, ['*'], 'penerimaPage');
        } else {
            $penerimaList = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15);
        }

        return view('livewire.master-data.beasiswa-index', [
            'tahunList' => $tahunList,
            'masterList' => $masterList,
            'penerimaList' => $penerimaList
        ])->layout('layouts.app', ['title' => 'Master Data Beasiswa']);
    }
}
