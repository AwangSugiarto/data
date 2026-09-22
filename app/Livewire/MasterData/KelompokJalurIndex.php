<?php

namespace App\Livewire\MasterData;

use App\Models\DimKelompokJalur;
use Livewire\Component;
use Livewire\WithPagination;

class KelompokJalurIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $showModal = false;
    public $editId = null;
    public $nama_kelompok;

    protected $rules = [
        'nama_kelompok' => 'required|unique:dim_kelompok_jalurs,nama_kelompok',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openCreate()
    {
        $this->reset(['editId', 'nama_kelompok']);
        $this->showModal = true;
    }

    public function openEdit($id)
    {
        $kelompok = DimKelompokJalur::findOrFail($id);
        $this->editId = $id;
        $this->nama_kelompok = $kelompok->nama_kelompok;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate([
            'nama_kelompok' => 'required|unique:dim_kelompok_jalurs,nama_kelompok,' . $this->editId,
        ]);

        if ($this->editId) {
            DimKelompokJalur::where('id', $this->editId)->update(['nama_kelompok' => $this->nama_kelompok]);
            session()->flash('success', 'Kelompok Jalur berhasil diupdate.');
        } else {
            DimKelompokJalur::create(['nama_kelompok' => $this->nama_kelompok]);
            session()->flash('success', 'Kelompok Jalur berhasil ditambahkan.');
        }

        $this->showModal = false;
    }

    public function delete($id)
    {
        DimKelompokJalur::findOrFail($id)->delete();
        session()->flash('success', 'Kelompok Jalur berhasil dihapus.');
    }

    public function render()
    {
        $query = DimKelompokJalur::query();
        if ($this->search) {
            $query->where('nama_kelompok', 'like', '%' . $this->search . '%');
        }
        $kelompokJalur = $query->latest()->paginate(10);
        return view('livewire.master-data.kelompok-jalur-index', compact('kelompokJalur'))
            ->layout('layouts.app', ['title' => 'Manajemen Kelompok Jalur']);
    }
}
