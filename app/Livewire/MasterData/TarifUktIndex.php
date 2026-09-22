<?php

namespace App\Livewire\MasterData;

use App\Models\DimTahunAkademik;
use App\Models\DimProgramStudi;
use App\Models\DimKelompokUkt;
use App\Models\DimTarifUkt;
use App\Models\DimTarifSpp;
use App\Imports\TarifBiayaImport;
use App\Exports\TarifBiayaTemplateExport;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;

class TarifUktIndex extends Component
{
    use WithPagination, WithFileUploads;

    public $filterTahunAkademik = '';
    public $searchProdi = '';

    public bool $showModal = false;
    public bool $showImportModal = false;
    public ?int $selectedProdiId = null;
    public string $selectedProdiName = '';
    public string $selectedProdiJenjang = '';
    
    // Array to store nominals: [kelompok_ukt_id => nominal_value] for S1
    public array $nominals = [];

    // Store SPP nominal for S2/S3
    public $nominal_spp = '';

    public $importFile;

    public function mount()
    {
        $activeYear = DimTahunAkademik::orderBy('id', 'desc')->first();
        if ($activeYear) {
            $this->filterTahunAkademik = $activeYear->id;
        }
    }

    public function updatingFilterTahunAkademik() { $this->resetPage(); }
    public function updatingSearchProdi() { $this->resetPage(); }

    public function openEditTarif(int $prodiId, string $prodiName, string $prodiJenjang)
    {
        if (!$this->filterTahunAkademik) {
            session()->flash('error', 'Silakan pilih Tahun Akademik terlebih dahulu.');
            return;
        }

        $this->selectedProdiId = $prodiId;
        $this->selectedProdiName = $prodiName;
        $this->selectedProdiJenjang = $prodiJenjang;
        $this->nominals = [];
        $this->nominal_spp = '';

        if (in_array(strtoupper($prodiJenjang), ['S1', 'D3', 'D4'])) {
            // Load existing nominals for this prodi and tahun
            $existingTarifs = DimTarifUkt::where('tahun_akademik_id', $this->filterTahunAkademik)
                ->where('program_studi_id', $prodiId)
                ->get();

            foreach ($existingTarifs as $tarif) {
                $this->nominals[$tarif->kelompok_ukt_id] = (int)$tarif->nominal;
            }
        } else {
            // Load SPP
            $existingSpp = DimTarifSpp::where('tahun_akademik_id', $this->filterTahunAkademik)
                ->where('program_studi_id', $prodiId)
                ->first();
            if ($existingSpp) {
                $this->nominal_spp = (int)$existingSpp->nominal;
            }
        }

        $this->showModal = true;
    }

    public function save()
    {
        $this->validate([
            'filterTahunAkademik' => 'required',
            'selectedProdiId' => 'required',
            'nominals' => 'array',
            'nominal_spp' => 'nullable|numeric'
        ]);

        if (in_array(strtoupper($this->selectedProdiJenjang), ['S1', 'D3', 'D4'])) {
            foreach ($this->nominals as $kelompok_id => $nominal) {
                if ($nominal === '' || $nominal === null) {
                    continue; // Skip empty
                }

                DimTarifUkt::updateOrCreate(
                    [
                        'tahun_akademik_id' => $this->filterTahunAkademik,
                        'program_studi_id' => $this->selectedProdiId,
                        'kelompok_ukt_id' => $kelompok_id,
                    ],
                    [
                        'nominal' => $nominal
                    ]
                );
            }
        } else {
            if ($this->nominal_spp !== '' && $this->nominal_spp !== null) {
                DimTarifSpp::updateOrCreate(
                    [
                        'tahun_akademik_id' => $this->filterTahunAkademik,
                        'program_studi_id' => $this->selectedProdiId,
                    ],
                    [
                        'nominal' => $this->nominal_spp
                    ]
                );
            }
        }

        session()->flash('success', 'Tarif Biaya Kuliah berhasil disimpan.');
        $this->showModal = false;
    }

    public function openImportModal()
    {
        if (!$this->filterTahunAkademik) {
            session()->flash('error', 'Silakan pilih Tahun Akademik terlebih dahulu.');
            return;
        }
        $this->importFile = null;
        $this->showImportModal = true;
    }

    public function downloadTemplate()
    {
        return Excel::download(new TarifBiayaTemplateExport, 'template_tarif_biaya.xlsx');
    }

    public function importTarif()
    {
        $this->validate([
            'importFile' => 'required|file|mimes:xlsx,xls'
        ]);

        try {
            Excel::import(new TarifBiayaImport($this->filterTahunAkademik), $this->importFile);
            session()->flash('success', 'Berhasil mengimpor Tarif Biaya Kuliah.');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal mengimpor: ' . $e->getMessage());
        }

        $this->showImportModal = false;
        $this->importFile = null;
    }

    public function render()
    {
        $tahunList = DimTahunAkademik::orderBy('id', 'desc')->get();
        $kelompokUktList = [];
        
        if ($this->filterTahunAkademik) {
            $kelompokUktList = DimKelompokUkt::where('tahun_akademik_id', $this->filterTahunAkademik)
                ->orderBy('kelompok')
                ->get();
        }

        $prodiList = DimProgramStudi::with(['fakultas', 'tarifUkt' => function($q) {
                $q->where('tahun_akademik_id', $this->filterTahunAkademik);
            }, 'tarifSpp' => function($q) {
                $q->where('tahun_akademik_id', $this->filterTahunAkademik);
            }])
            ->where('status_terkini', true)
            ->when($this->searchProdi, fn($q) => $q->where('nama_prodi', 'like', "%{$this->searchProdi}%"))
            ->orderBy('fakultas_id')
            ->orderBy('nama_prodi')
            ->paginate(10);

        return view('livewire.master-data.tarif-ukt-index', compact('tahunList', 'kelompokUktList', 'prodiList'))
            ->layout('layouts.app', ['title' => 'Tarif UKT per Program Studi']);
    }
}
