<?php

namespace App\Livewire\ManajemenPmb;

use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\CamaImport;
use App\Models\DimTahunAkademik;
use App\Models\DimJalurSeleksi;

class ImportCamaIndex extends Component
{
    use WithFileUploads;

    public $file;
    public $tahunId;
    public $jalurId;

    public function import()
    {
        $this->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
            'tahunId' => 'required',
            'jalurId' => 'nullable'
        ]);

        Excel::import(new CamaImport($this->tahunId, $this->jalurId), $this->file);

        session()->flash('success', 'Data Calon Mahasiswa berstatus Lulus Seleksi berhasil diimport!');
        $this->reset('file');
    }

    public function render()
    {
        return view('livewire.manajemen-pmb.import-cama-index', [
            'tahunList' => DimTahunAkademik::orderByDesc('tahun')->get(),
            'jalurList' => DimJalurSeleksi::orderBy('nama_jalur')->get()
        ])->layout('layouts.app', ['title' => 'Import Data Cama']);
    }
}
