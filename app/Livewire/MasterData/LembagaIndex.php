<?php

namespace App\Livewire\MasterData;

use App\Models\DimLembaga;
use Illuminate\Database\QueryException;
use Livewire\Component;
use Livewire\WithPagination;

class LembagaIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $sortColumn    = 'nama_lembaga';
    public string $sortDirection = 'asc';

    public bool    $showModal = false;
    public bool    $editMode  = false;
    public ?int    $editId    = null;

    // Form fields
    public string  $nama_lembaga = '';
    public string  $singkatan    = '';
    public string  $keterangan   = '';
    public bool    $status_aktif = true;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $column): void
    {
        if ($this->sortColumn === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortColumn    = $column;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->reset(['editId', 'nama_lembaga', 'singkatan', 'keterangan']);
        $this->status_aktif = true;
        $this->editMode     = false;
        $this->showModal    = true;
    }

    public function openEdit(int $id): void
    {
        $lembaga = DimLembaga::findOrFail($id);
        $this->editId       = $lembaga->id;
        $this->nama_lembaga = $lembaga->nama_lembaga;
        $this->singkatan    = $lembaga->singkatan ?? '';
        $this->keterangan   = $lembaga->keterangan ?? '';
        $this->status_aktif = $lembaga->status_aktif;
        $this->editMode     = true;
        $this->showModal    = true;
    }

    public function save(): void
    {
        $uniqueRule = 'unique:dim_lembaga,nama_lembaga' . ($this->editMode ? ',' . $this->editId : '');

        $this->validate([
            'nama_lembaga' => ['required', 'string', 'max:255', $uniqueRule],
            'singkatan'    => 'nullable|string|max:50',
            'keterangan'   => 'nullable|string|max:500',
        ], [
            'nama_lembaga.required' => 'Nama lembaga wajib diisi.',
            'nama_lembaga.unique'   => 'Nama lembaga ini sudah terdaftar.',
        ]);

        $data = [
            'nama_lembaga' => $this->nama_lembaga,
            'singkatan'    => $this->singkatan ?: null,
            'keterangan'   => $this->keterangan ?: null,
            'status_aktif' => $this->status_aktif,
        ];

        if ($this->editMode && $this->editId) {
            DimLembaga::findOrFail($this->editId)->update($data);
            session()->flash('success', 'Lembaga berhasil diperbarui.');
        } else {
            DimLembaga::create($data);
            session()->flash('success', 'Lembaga berhasil ditambahkan.');
        }

        $this->showModal = false;
    }

    public function toggleStatus(int $id): void
    {
        $lembaga = DimLembaga::findOrFail($id);
        $lembaga->update(['status_aktif' => !$lembaga->status_aktif]);
    }

    public function delete(int $id): void
    {
        try {
            DimLembaga::findOrFail($id)->delete();
            session()->flash('success', 'Lembaga berhasil dihapus.');
        } catch (QueryException $e) {
            if ($e->getCode() == '23000') {
                session()->flash('error', 'Lembaga tidak dapat dihapus karena masih digunakan pada data lain.');
            } else {
                session()->flash('error', 'Terjadi kesalahan saat menghapus data.');
            }
        }
    }

    public function render()
    {
        $lembaga = DimLembaga::query()
            ->when($this->search, fn($q) => $q->where('nama_lembaga', 'like', "%{$this->search}%")
                ->orWhere('singkatan', 'like', "%{$this->search}%"))
            ->orderBy($this->sortColumn, $this->sortDirection)
            ->paginate(10);

        return view('livewire.master-data.lembaga-index', compact('lembaga'))
            ->layout('layouts.app', ['title' => 'Master Lembaga Penyelenggara']);
    }
}
