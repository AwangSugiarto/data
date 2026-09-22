<?php

namespace App\Livewire\MasterData;

use App\Models\DimWarganegara;
use Illuminate\Database\QueryException;
use Livewire\Component;
use Livewire\WithPagination;

class NegaraIndex extends Component
{
    use WithPagination;

    public string $search        = '';
    public string $sortColumn    = 'nama_negara';
    public string $sortDirection = 'asc';

    public bool   $showModal = false;
    public bool   $editMode  = false;
    public ?int   $editId    = null;

    // Form fields
    public string $nama_negara  = '';
    public string $kode_negara  = '';

    public function updatingSearch(): void { $this->resetPage(); }

    public function sortBy(string $column): void
    {
        $this->sortColumn    = $this->sortColumn === $column
            ? $this->sortColumn
            : $column;
        $this->sortDirection = $this->sortColumn === $column && $this->sortDirection === 'asc'
            ? 'desc'
            : 'asc';
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->reset(['editId', 'nama_negara', 'kode_negara']);
        $this->editMode  = false;
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $negara = DimWarganegara::findOrFail($id);
        $this->editId      = $negara->id;
        $this->nama_negara = $negara->nama_negara;
        $this->kode_negara = $negara->kode_negara ?? '';
        $this->editMode    = true;
        $this->showModal   = true;
    }

    public function save(): void
    {
        $uniqueRule = 'unique:dim_warganegara,nama_negara' . ($this->editMode ? ',' . $this->editId : '');

        $this->validate([
            'nama_negara' => ['required', 'string', 'max:255', $uniqueRule],
            'kode_negara' => 'nullable|string|max:10',
        ], [
            'nama_negara.required' => 'Nama negara wajib diisi.',
            'nama_negara.unique'   => 'Nama negara ini sudah terdaftar.',
        ]);

        $data = [
            'nama_negara' => $this->nama_negara,
            'kode_negara' => strtoupper($this->kode_negara) ?: null,
        ];

        if ($this->editMode && $this->editId) {
            DimWarganegara::findOrFail($this->editId)->update($data);
            session()->flash('success', 'Data negara berhasil diperbarui.');
        } else {
            DimWarganegara::create($data);
            session()->flash('success', 'Negara berhasil ditambahkan.');
        }

        $this->showModal = false;
    }

    public function delete(int $id): void
    {
        try {
            DimWarganegara::findOrFail($id)->delete();
            session()->flash('success', 'Negara berhasil dihapus.');
        } catch (QueryException $e) {
            if ($e->getCode() == '23000') {
                session()->flash('error', 'Negara tidak dapat dihapus karena masih digunakan pada data mahasiswa.');
            } else {
                session()->flash('error', 'Terjadi kesalahan saat menghapus data.');
            }
        }
    }

    public function render()
    {
        $negara = DimWarganegara::query()
            ->when($this->search, fn($q) => $q
                ->where('nama_negara', 'like', "%{$this->search}%")
                ->orWhere('kode_negara', 'like', "%{$this->search}%"))
            ->orderBy($this->sortColumn, $this->sortDirection)
            ->paginate(10);

        return view('livewire.master-data.negara-index', compact('negara'))
            ->layout('layouts.app', ['title' => 'Master Negara / Kewarganegaraan']);
    }
}
