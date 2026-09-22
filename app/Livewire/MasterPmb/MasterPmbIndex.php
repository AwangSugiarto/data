<?php

namespace App\Livewire\MasterPmb;

use App\Models\DimFakultas;
use App\Models\DimJalurSeleksi;
use App\Models\DimProgramStudi;
use App\Models\DimTahunAkademik;
use App\Models\MasterPmb;
use Livewire\Component;
use Livewire\WithPagination;

class MasterPmbIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;
    
    // Form fields
    public $showModal = false;
    public $showConfirm = false;
    public $editId = null;
    
    public $f_tahun_id;
    public $f_jalur_id;
    public $f_fakultas_id;
    public $f_prodi_id;
    public $f_daya_tampung = 0;
    public $f_pendaftar = 0;

    public $prodiFormList = [];

    protected $rules = [
        'f_tahun_id' => 'required',
        'f_jalur_id' => 'required',
        'f_fakultas_id' => 'required',
        'f_prodi_id' => 'required',
        'f_daya_tampung' => 'required|numeric|min:0',
        'f_pendaftar' => 'required|numeric|min:0',
    ];

    public function updatedFFakultasId($value)
    {
        if ($value) {
            $this->prodiFormList = DimProgramStudi::where('fakultas_id', $value)->get();
        } else {
            $this->prodiFormList = [];
        }
        $this->f_prodi_id = '';
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function create()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit($id)
    {
        $this->resetForm();
        $this->editId = $id;
        $row = MasterPmb::findOrFail($id);
        
        $this->f_tahun_id = $row->tahun_akademik_id;
        $this->f_jalur_id = $row->jalur_seleksi_id;
        $this->f_fakultas_id = $row->fakultas_id;
        
        $this->updatedFFakultasId($row->fakultas_id);
        
        $this->f_prodi_id = $row->program_studi_id;
        $this->f_daya_tampung = $row->daya_tampung;
        $this->f_pendaftar = $row->pendaftar;
        
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        $data = [
            'tahun_akademik_id' => $this->f_tahun_id,
            'jalur_seleksi_id' => $this->f_jalur_id,
            'fakultas_id' => $this->f_fakultas_id,
            'program_studi_id' => $this->f_prodi_id,
            'daya_tampung' => $this->f_daya_tampung,
            'pendaftar' => $this->f_pendaftar,
        ];

        if ($this->editId) {
            MasterPmb::where('id', $this->editId)->update($data);
            session()->flash('success', 'Data Master PMB berhasil diperbarui.');
        } else {
            MasterPmb::create($data);
            session()->flash('success', 'Data Master PMB berhasil ditambahkan.');
        }

        $this->closeModal();
    }

    public function confirmDelete($id)
    {
        $this->editId = $id;
        $this->showConfirm = true;
    }

    public function delete()
    {
        if ($this->editId) {
            MasterPmb::destroy($this->editId);
            session()->flash('success', 'Data Master PMB berhasil dihapus.');
        }
        $this->closeModal();
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->showConfirm = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->editId = null;
        $this->f_tahun_id = '';
        $this->f_jalur_id = '';
        $this->f_fakultas_id = '';
        $this->f_prodi_id = '';
        $this->f_daya_tampung = 0;
        $this->f_pendaftar = 0;
        $this->prodiFormList = [];
    }

    public function render()
    {
        $query = MasterPmb::with(['tahunAkademik', 'jalurSeleksi', 'fakultas', 'programStudi']);

        if ($this->search) {
            $query->whereHas('fakultas', function($q) {
                $q->where('nama_fakultas', 'like', '%' . $this->search . '%');
            })->orWhereHas('programStudi', function($q) {
                $q->where('nama_prodi', 'like', '%' . $this->search . '%');
            });
        }

        $rows = $query->latest()->paginate($this->perPage);
        $tahunList = DimTahunAkademik::orderBy('tahun', 'desc')->get();
        $jalurList = DimJalurSeleksi::orderBy('nama_jalur')->get();
        $fakultasList = DimFakultas::orderBy('nama_fakultas')->get();

        return view('livewire.master-pmb.master-pmb-index', compact('rows', 'tahunList', 'jalurList', 'fakultasList'))
            ->layout('layouts.app', ['title' => 'Master PMB']);
    }
}
