<?php

namespace App\Livewire\Input;

use App\Models\DimFakultas;
use App\Models\DimJalurSeleksi;
use App\Models\DimProgramStudi;
use App\Models\DimTahunAkademik;
use App\Models\FactKelulusan;
use App\Models\FactKuota;
use App\Models\FactPendaftaran;
use App\Models\FactRegistrasi;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class InputAgregat extends Component
{
    use WithPagination;
    
    public $tahun_akademik_id = '';
    public $fakultas_id = '';
    public $jalur_seleksi_id = '';

    public $entries = [];
    public $selectAllEntries = false;

    public $tahunList = [];
    public $fakultasList = [];
    public $prodiList = [];
    public $jalurList = [];
    
    public $selectedRows = [];
    public $selectAll = false;

    protected $rules = [
        'tahun_akademik_id' => 'required|exists:dim_tahun_akademik,id',
        'fakultas_id'       => 'required|exists:dim_fakultas,id',
        'jalur_seleksi_id'  => 'required|exists:dim_jalur_seleksi,id',
        'entries.*.kuota'      => 'nullable|numeric|min:0',
        'entries.*.pil1'       => 'nullable|numeric|min:0',
        'entries.*.pil2'       => 'nullable|numeric|min:0',
        'entries.*.pil3'       => 'nullable|numeric|min:0',
        'entries.*.pil4'       => 'nullable|numeric|min:0',
        'entries.*.lulus'      => 'nullable|numeric|min:0',
        'entries.*.registrasi' => 'nullable|numeric|min:0',
    ];

    public function mount()
    {
        $this->tahunList   = DimTahunAkademik::orderBy('tahun', 'desc')->get();
        $this->fakultasList = DimFakultas::where('status_terkini', true)->orderBy('nama_fakultas')->get();
        $this->jalurList   = DimJalurSeleksi::where('status_aktif', true)->orderBy('nama_jalur')->get();
    }

    public function updatedFakultasId($value)
    {
        $this->refreshProdiList();
        $this->loadExistingData();
        $this->resetPage();
    }

    public function updatedTahunAkademikId() { 
        $this->loadExistingData(); 
        $this->selectedRows = [];
        $this->selectAll = false;
        $this->resetPage();
    }
    
    public function updatedJalurSeleksiId()  { 
        $this->refreshProdiList();
        $this->loadExistingData(); 
        $this->selectedRows = [];
        $this->selectAll = false;
        $this->resetPage();
    }

    private function refreshProdiList()
    {
        if (!$this->fakultas_id) {
            $this->prodiList = [];
            return;
        }

        $query = DimProgramStudi::where('status_terkini', true)
            ->where('fakultas_id', $this->fakultas_id);

        if ($this->jalur_seleksi_id) {
            $query->whereHas('jalurSeleksi', function ($q) {
                $q->where('dim_jalur_seleksi.id', $this->jalur_seleksi_id);
            });
        }

        $this->prodiList = $query->orderBy('nama_prodi')->get();
    }

    public function updatedSelectAllEntries($value)
    {
        foreach ($this->entries as $pid => $entry) {
            $this->entries[$pid]['selected'] = $value;
        }
    }

    public function tambahTahunAkademik($tahun)
    {
        $tahun = trim($tahun);
        if (empty($tahun)) return;

        $exists = DimTahunAkademik::where('tahun', $tahun)->first();
        if (!$exists) {
            $exists = DimTahunAkademik::create(['tahun' => $tahun, 'keterangan' => 'Ditambahkan dari entri agregat']);
            
            $this->tahunList = DimTahunAkademik::orderBy('tahun', 'desc')->get();
            session()->flash('success', 'Tahun Akademik ' . $tahun . ' berhasil ditambahkan.');
        }
        
        $this->tahun_akademik_id = $exists->id;
        $this->updatedTahunAkademikId();
    }

    public function hapusTahunAkademik()
    {
        if (!$this->tahun_akademik_id) return;

        $hasData = FactPendaftaran::where('tahun_akademik_id', $this->tahun_akademik_id)->exists();
        
        if ($hasData) {
            session()->flash('error', 'Tahun akademik tidak bisa dihapus karena sudah memiliki data entri. Harap hapus datanya terlebih dahulu.');
            return;
        }

        try {
            DimTahunAkademik::where('id', $this->tahun_akademik_id)->delete();
            
            $this->tahun_akademik_id = '';
            $this->tahunList = DimTahunAkademik::orderBy('tahun', 'desc')->get();
            session()->flash('success', 'Tahun Akademik berhasil dihapus permanen.');
            
            $this->updatedTahunAkademikId();
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == 23000) {
                session()->flash('error', 'Tahun akademik tidak bisa dihapus karena masih digunakan atau terhubung dengan data lain (misalnya data pendaftar individu atau data master lainnya).');
            } else {
                session()->flash('error', 'Terjadi kesalahan saat menghapus tahun akademik: ' . $e->getMessage());
            }
        }
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $query = FactPendaftaran::query()
                ->where('tahun_akademik_id', $this->tahun_akademik_id)
                ->when($this->fakultas_id, function($q) {
                    $q->whereHas('programStudi', function($q2) {
                        $q2->where('fakultas_id', $this->fakultas_id);
                    });
                })
                ->when($this->jalur_seleksi_id, fn($q) => $q->where('jalur_seleksi_id', $this->jalur_seleksi_id));
                
            $this->selectedRows = $query->get()->map(function($row) {
                return $row->tahun_akademik_id . '-' . $row->program_studi_id . '-' . $row->jalur_seleksi_id;
            })->toArray();
        } else {
            $this->selectedRows = [];
        }
    }

    private function loadExistingData()
    {
        $this->entries = [];
        $this->selectAllEntries = false;

        if ($this->tahun_akademik_id && $this->fakultas_id && $this->jalur_seleksi_id && count($this->prodiList) > 0) {
            $prodiIds = collect($this->prodiList)->pluck('id')->toArray();
            $conditions = [
                'tahun_akademik_id' => $this->tahun_akademik_id,
                'jalur_seleksi_id'  => $this->jalur_seleksi_id,
            ];

            $kuotas = FactKuota::where($conditions)->whereIn('program_studi_id', $prodiIds)->get()->keyBy('program_studi_id');
            $daftars = FactPendaftaran::where($conditions)->whereIn('program_studi_id', $prodiIds)->get();
            $lulusan = FactKelulusan::where($conditions)->whereIn('program_studi_id', $prodiIds)->get()->keyBy('program_studi_id');
            $regs = FactRegistrasi::where($conditions)->whereIn('program_studi_id', $prodiIds)->get()->keyBy('program_studi_id');

            $daftarGrouped = [];
            foreach ($daftars as $d) {
                $pid = $d->program_studi_id;
                $pil = $d->nomor_pilihan ?: 1;
                if (!isset($daftarGrouped[$pid])) $daftarGrouped[$pid] = [];
                if (!isset($daftarGrouped[$pid][$pil])) $daftarGrouped[$pid][$pil] = 0;
                $daftarGrouped[$pid][$pil] += $d->jumlah_daftar;
            }

            foreach ($this->prodiList as $prodi) {
                $pid = $prodi->id;
                $this->entries[$pid] = [
                    'selected'   => false,
                    'kuota'      => $kuotas->has($pid) ? $kuotas->get($pid)->kuota_akhir : 0,
                    'pil1'       => $daftarGrouped[$pid][1] ?? 0,
                    'pil2'       => $daftarGrouped[$pid][2] ?? 0,
                    'pil3'       => $daftarGrouped[$pid][3] ?? 0,
                    'pil4'       => $daftarGrouped[$pid][4] ?? 0,
                    'lulus'      => $lulusan->has($pid) ? $lulusan->get($pid)->jumlah_lulus : 0,
                    'registrasi' => $regs->has($pid) ? $regs->get($pid)->jumlah_registrasi : 0,
                ];
            }
        }
    }

    public function save()
    {
        $this->validate();
        
        $hasSelected = false;
        foreach ($this->entries as $pid => $entry) {
            if (!empty($entry['selected'])) {
                $hasSelected = true;
                break;
            }
        }
        
        if (!$hasSelected) {
            session()->flash('error', 'Pilih minimal satu program studi dengan mencentang kotak di sebelahnya.');
            return;
        }

        DB::beginTransaction();
        try {
            foreach ($this->entries as $pid => $entry) {
                if (empty($entry['selected'])) continue;
                
                $conditions = [
                    'tahun_akademik_id' => $this->tahun_akademik_id,
                    'program_studi_id'  => $pid,
                    'jalur_seleksi_id'  => $this->jalur_seleksi_id,
                ];

                FactKuota::updateOrCreate($conditions, ['kuota_akhir' => $entry['kuota'] ?: 0]);
                FactKelulusan::updateOrCreate($conditions, ['jumlah_lulus' => $entry['lulus'] ?: 0]);
                FactRegistrasi::updateOrCreate($conditions, ['jumlah_registrasi' => $entry['registrasi'] ?: 0]);
                
                for ($i = 1; $i <= 4; $i++) {
                    $pilCondition = $conditions;
                    $pilCondition['nomor_pilihan'] = $i;
                    FactPendaftaran::updateOrCreate($pilCondition, ['jumlah_daftar' => $entry['pil'.$i] ?: 0]);
                }
            }

            DB::commit();
            session()->flash('success', 'Data agregat prodi yang dipilih berhasil disimpan/diperbarui.');
            
            $this->loadExistingData();
            $this->resetPage(); 
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage());
        }
    }

    public function editRow($tahunId, $prodiId, $jalurId)
    {
        $prodi = DimProgramStudi::find($prodiId);
        if ($prodi) {
            $this->tahun_akademik_id = $tahunId;
            $this->fakultas_id = $prodi->fakultas_id;
            $this->jalur_seleksi_id = $jalurId;
            
            $this->refreshProdiList();
                
            $this->loadExistingData();
            
            if (isset($this->entries[$prodiId])) {
                $this->entries[$prodiId]['selected'] = true;
            }
            
            $this->dispatch('scrollToForm');
        }
    }

    public function deleteRow($tahunId, $prodiId, $jalurId)
    {
        $conditions = [
            'tahun_akademik_id' => $tahunId,
            'program_studi_id'  => $prodiId,
            'jalur_seleksi_id'  => $jalurId,
        ];
        
        FactKuota::where($conditions)->delete();
        FactPendaftaran::where($conditions)->delete();
        FactKelulusan::where($conditions)->delete();
        FactRegistrasi::where($conditions)->delete();
        
        session()->flash('success', 'Data agregat berhasil dihapus.');
        $this->loadExistingData(); 
    }

    public function deleteSelected()
    {
        if (empty($this->selectedRows)) return;

        foreach ($this->selectedRows as $row) {
            $parts = explode('-', $row);
            if (count($parts) === 3) {
                $conditions = [
                    'tahun_akademik_id' => $parts[0],
                    'program_studi_id'  => $parts[1],
                    'jalur_seleksi_id'  => $parts[2],
                ];
                FactKuota::where($conditions)->delete();
                FactPendaftaran::where($conditions)->delete();
                FactKelulusan::where($conditions)->delete();
                FactRegistrasi::where($conditions)->delete();
            }
        }

        $this->selectedRows = [];
        $this->selectAll = false;
        session()->flash('success', 'Data yang dipilih berhasil dihapus.');
        $this->loadExistingData();
    }

    public function exportExcel()
    {
        if (!$this->tahun_akademik_id) {
            session()->flash('error', 'Silakan pilih Tahun Akademik terlebih dahulu untuk mengekspor data.');
            return;
        }

        $tahun = DimTahunAkademik::find($this->tahun_akademik_id);
        $filename = 'Agregat_PMB_' . ($tahun ? $tahun->tahun : 'All') . '_' . date('Ymd_His') . '.xlsx';

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\AgregatExport(
                $this->tahun_akademik_id, 
                $this->fakultas_id, 
                null, 
                $this->jalur_seleksi_id
            ), 
            $filename
        );
    }

    public function render()
    {
        $listData = [];
        if ($this->tahun_akademik_id) {
            $listData = FactPendaftaran::query()
                ->select([
                    'fact_pendaftaran.tahun_akademik_id',
                    'fact_pendaftaran.program_studi_id',
                    'fact_pendaftaran.jalur_seleksi_id',
                    DB::raw('SUM(fact_pendaftaran.jumlah_daftar) as jumlah_daftar'),
                    'fact_kuota.kuota_akhir',
                    'fact_kelulusan.jumlah_lulus',
                    'fact_registrasi.jumlah_registrasi'
                ])
                ->leftJoin('fact_kuota', function($join) {
                    $join->on('fact_pendaftaran.tahun_akademik_id', '=', 'fact_kuota.tahun_akademik_id')
                         ->on('fact_pendaftaran.program_studi_id', '=', 'fact_kuota.program_studi_id')
                         ->on('fact_pendaftaran.jalur_seleksi_id', '=', 'fact_kuota.jalur_seleksi_id');
                })
                ->leftJoin('fact_kelulusan', function($join) {
                    $join->on('fact_pendaftaran.tahun_akademik_id', '=', 'fact_kelulusan.tahun_akademik_id')
                         ->on('fact_pendaftaran.program_studi_id', '=', 'fact_kelulusan.program_studi_id')
                         ->on('fact_pendaftaran.jalur_seleksi_id', '=', 'fact_kelulusan.jalur_seleksi_id');
                })
                ->leftJoin('fact_registrasi', function($join) {
                    $join->on('fact_pendaftaran.tahun_akademik_id', '=', 'fact_registrasi.tahun_akademik_id')
                         ->on('fact_pendaftaran.program_studi_id', '=', 'fact_registrasi.program_studi_id')
                         ->on('fact_pendaftaran.jalur_seleksi_id', '=', 'fact_registrasi.jalur_seleksi_id');
                })
                ->with(['programStudi.fakultas', 'jalurSeleksi', 'tahunAkademik'])
                ->where('fact_pendaftaran.tahun_akademik_id', $this->tahun_akademik_id)
                ->when($this->fakultas_id, function($q) {
                    $q->whereHas('programStudi', function($q2) {
                        $q2->where('fakultas_id', $this->fakultas_id);
                    });
                })
                ->when($this->jalur_seleksi_id, fn($q) => $q->where('fact_pendaftaran.jalur_seleksi_id', $this->jalur_seleksi_id))
                ->groupBy([
                    'fact_pendaftaran.tahun_akademik_id',
                    'fact_pendaftaran.program_studi_id',
                    'fact_pendaftaran.jalur_seleksi_id',
                    'fact_kuota.kuota_akhir',
                    'fact_kelulusan.jumlah_lulus',
                    'fact_registrasi.jumlah_registrasi'
                ])
                ->paginate(10);
        }

        return view('livewire.input.input-agregat', compact('listData'))
            ->layout('layouts.app', ['title' => 'Entri Agregat PMB']);
    }
}
