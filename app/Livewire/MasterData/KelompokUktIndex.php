<?php

namespace App\Livewire\MasterData;

use App\Models\DimKelompokUkt;
use Illuminate\Database\QueryException;
use Livewire\Component;

class KelompokUktIndex extends Component
{
    public bool $showModal = false;
    public bool $editMode = false;
    public ?int $editId = null;

    public $filterTahunAkademik = '';

    public $tahun_akademik_id = '';
    public string $kelompok = '';
    public string $nominal_min = '';
    public string $nominal_max = '';

    public function mount()
    {
        $activeYear = \App\Models\DimTahunAkademik::orderBy('id', 'desc')->first();
        if ($activeYear) {
            $this->filterTahunAkademik = $activeYear->id;
        }
    }

    public function updatingFilterTahunAkademik(): void { }

    public function openCreate(): void
    {
        $this->reset(['editId','kelompok','nominal_min','nominal_max']);
        $this->tahun_akademik_id = $this->filterTahunAkademik;
        $this->editMode = false;
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $u = DimKelompokUkt::findOrFail($id);
        $this->editId      = $u->id;
        $this->tahun_akademik_id = $u->tahun_akademik_id;
        $this->kelompok    = $u->kelompok;
        $this->nominal_min = $u->nominal_min ?? '';
        $this->nominal_max = $u->nominal_max ?? '';
        $this->editMode = true;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'tahun_akademik_id' => 'required|exists:dim_tahun_akademik,id',
            'kelompok'    => 'required|string|max:50',
            'nominal_min' => 'nullable|numeric|min:0',
            'nominal_max' => 'nullable|numeric|gte:nominal_min',
        ]);

        $data = [
            'tahun_akademik_id' => $this->tahun_akademik_id,
            'kelompok'    => $this->kelompok,
            'nominal_min' => $this->nominal_min ?: null,
            'nominal_max' => $this->nominal_max ?: null,
        ];

        if ($this->editMode && $this->editId) {
            DimKelompokUkt::findOrFail($this->editId)->update($data);
            session()->flash('success', 'Kelompok UKT diperbarui.');
        } else {
            DimKelompokUkt::create($data);
            session()->flash('success', 'Kelompok UKT ditambahkan.');
        }

        $this->showModal = false;
    }

    public function deleteUkt(int $id): void
    {
        $ukt = DimKelompokUkt::findOrFail($id);

        try {
            $ukt->delete();
            session()->flash('success', 'Kelompok UKT berhasil dihapus.');
        } catch (QueryException $e) {
            if ($e->getCode() == '23000') {
                session()->flash('error', "Gagal menghapus! Kelompok UKT '{$ukt->kelompok}' tidak bisa dihapus karena masih digunakan pada data sebaran mahasiswa/UKT.");
            } else {
                session()->flash('error', 'Terjadi kesalahan sistem saat menghapus data.');
            }
        }
    }

    public function render()
    {
        $tahunList = \App\Models\DimTahunAkademik::orderBy('id', 'desc')->get();
        $uktList = DimKelompokUkt::with('tahunAkademik')
            ->when($this->filterTahunAkademik, fn($q) => $q->where('tahun_akademik_id', $this->filterTahunAkademik))
            ->orderBy('kelompok')
            ->get();
        return view('livewire.master-data.kelompok-ukt-index', compact('uktList', 'tahunList'))
            ->layout('layouts.app', ['title' => 'Manajemen Kelompok UKT']);
    }
}
