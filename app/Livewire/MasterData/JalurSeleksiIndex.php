<?php

namespace App\Livewire\MasterData;

use App\Models\DimJalurSeleksi;
use App\Models\DimKelompokJalur;
use App\Models\DimLembaga;
use App\Models\JalurAlias;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class JalurSeleksiIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $sortColumn = 'nama_jalur';
    public string $sortDirection = 'asc';
    public bool $showModal = false;
    public bool $showAliasModal = false;
    public bool $editMode = false;

    public ?int $editId = null;
    public string $nama_jalur = '';
    public string $lembaga = '';
    public bool $status_aktif = true;

    // Alias
    public ?int $aliasForJalurId = null;
    public string $aliasForJalurNama = '';
    public string $nama_alias = '';
    public array $existingAliases = [];

    public function openCreate(): void
    {
        $this->reset(['editId', 'nama_jalur', 'lembaga']);
        $this->status_aktif = true;
        $this->editMode     = false;
        $this->showModal    = true;
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

    public function openEdit(int $id): void
    {
        $j = DimJalurSeleksi::findOrFail($id);
        $this->editId        = $j->id;
        $this->nama_jalur    = $j->nama_jalur;
        $this->lembaga       = $j->lembaga ?? '';
        $this->status_aktif  = $j->status_aktif;
        $this->editMode = true;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'nama_jalur' => 'required|string|max:255|unique:dim_jalur_seleksi,nama_jalur,' . ($this->editMode ? $this->editId : 'NULL') . ',id',
            'lembaga'    => 'nullable|string|max:255',
        ], [
            'nama_jalur.unique' => 'Nama jalur ini sudah ada di dalam sistem. Silakan gunakan nama lain.'
        ]);

        $data = [
            'nama_jalur'   => $this->nama_jalur,
            'lembaga'      => $this->lembaga,
            'status_aktif' => $this->status_aktif,
        ];

        if ($this->editMode && $this->editId) {
            DimJalurSeleksi::findOrFail($this->editId)->update($data);
            session()->flash('success', 'Jalur Seleksi berhasil diperbarui.');
        } else {
            DimJalurSeleksi::create($data);
            session()->flash('success', 'Jalur Seleksi berhasil ditambahkan.');
        }

        $this->showModal = false;
    }

    public function toggleStatus(int $id): void
    {
        $j = DimJalurSeleksi::findOrFail($id);
        $j->update(['status_aktif' => !$j->status_aktif]);
    }

    public function openAlias(int $id): void
    {
        $j = DimJalurSeleksi::with('alias')->findOrFail($id);
        $this->aliasForJalurId   = $j->id;
        $this->aliasForJalurNama = $j->nama_jalur;
        $this->existingAliases   = $j->alias->toArray();
        $this->nama_alias        = '';
        $this->showAliasModal    = true;
    }

    public function saveAlias(): void
    {
        $this->validate(['nama_alias' => 'required|string|max:255']);
        JalurAlias::firstOrCreate([
            'jalur_seleksi_id' => $this->aliasForJalurId,
            'nama_alias'       => $this->nama_alias,
        ]);
        $this->existingAliases = DimJalurSeleksi::with('alias')->find($this->aliasForJalurId)->alias->toArray();
        $this->nama_alias = '';
    }

    public function deleteAlias(int $id): void
    {
        JalurAlias::destroy($id);
        $this->existingAliases = DimJalurSeleksi::with('alias')->find($this->aliasForJalurId)->alias->toArray();
    }

    public function deleteJalur(int $id): void
    {
        $jalur = DimJalurSeleksi::findOrFail($id);

        try {
            DB::transaction(function () use ($jalur) {
                $jalur->alias()->delete();
                $jalur->delete();
            });
            session()->flash('success', 'Jalur Seleksi berhasil dihapus.');
        } catch (QueryException $e) {
            if ($e->getCode() == '23000') {
                session()->flash('error', "Gagal menghapus! Jalur Seleksi '{$jalur->nama_jalur}' tidak bisa dihapus karena masih digunakan pada data kuota/pendaftar/kelulusan. Saran: Nonaktifkan saja statusnya.");
            } else {
                session()->flash('error', 'Terjadi kesalahan sistem saat menghapus data.');
            }
        }
    }

    public function render()
    {
        $jalur = DimJalurSeleksi::with('alias')
            ->withCount('alias')
            ->when($this->search, fn($q) => $q->where('nama_jalur', 'like', "%{$this->search}%"))
            ->orderBy($this->sortColumn, $this->sortDirection)
            ->paginate(10);

        $lembagaList = DimLembaga::where('status_aktif', true)->orderBy('nama_lembaga')->get();

        return view('livewire.master-data.jalur-seleksi-index', compact('jalur', 'lembagaList'))
            ->layout('layouts.app', ['title' => 'Manajemen Jalur Seleksi']);
    }
}
