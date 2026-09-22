<?php

namespace App\Livewire\MasterData;

use App\Models\DimFakultas;
use App\Models\FakultasAlias;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class FakultasIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public bool $showModal = false;
    public bool $showAliasModal = false;
    public bool $editMode = false;

    public ?int $editId = null;
    public string $nama_fakultas = '';
    public string $kode_fakultas = '';
    public bool $status_terkini = true;
    public string $berlaku_dari = '';
    public string $berlaku_sampai = '';
    public string $pejabat = '';
    public string $nip_pejabat = '';
    public string $telepon = '';
    public string $email = '';
    public string $keterangan = '';

    // Alias
    public ?int $aliasForFakultasId = null;
    public string $aliasForFakultasNama = '';
    public string $nama_alias = '';
    public array $existingAliases = [];

    public function updatingSearch(): void { $this->resetPage(); }

    public function openCreate(): void
    {
        $this->reset([
            'editId', 'nama_fakultas', 'kode_fakultas', 'berlaku_dari', 'berlaku_sampai',
            'pejabat', 'nip_pejabat', 'telepon', 'email', 'keterangan'
        ]);
        $this->status_terkini = true;
        $this->editMode = false;
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $f = DimFakultas::findOrFail($id);
        $this->editId         = $f->id;
        $this->nama_fakultas  = $f->nama_fakultas;
        $this->kode_fakultas  = $f->kode_fakultas ?? '';
        $this->status_terkini = $f->status_terkini;
        $this->berlaku_dari   = $f->berlaku_dari?->format('Y-m-d') ?? '';
        $this->berlaku_sampai = $f->berlaku_sampai?->format('Y-m-d') ?? '';
        $this->pejabat        = $f->pejabat ?? '';
        $this->nip_pejabat    = $f->nip_pejabat ?? '';
        $this->telepon        = $f->telepon ?? '';
        $this->email          = $f->email ?? '';
        $this->keterangan     = $f->keterangan ?? '';
        $this->editMode = true;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'nama_fakultas' => 'required|string|max:255',
            'kode_fakultas' => 'nullable|string|max:20',
            'berlaku_dari'  => 'nullable|date',
            'berlaku_sampai'=> 'nullable|date|after_or_equal:berlaku_dari',
            'pejabat'       => 'nullable|string|max:255',
            'nip_pejabat'   => 'nullable|string|max:100',
            'telepon'       => 'nullable|string|max:50',
            'email'         => 'nullable|email|max:255',
            'keterangan'    => 'nullable|string',
        ]);

        $data = [
            'nama_fakultas'  => $this->nama_fakultas,
            'kode_fakultas'  => $this->kode_fakultas ?: null,
            'status_terkini' => $this->status_terkini,
            'berlaku_dari'   => $this->berlaku_dari ?: null,
            'berlaku_sampai' => $this->berlaku_sampai ?: null,
            'pejabat'        => $this->pejabat ?: null,
            'nip_pejabat'    => $this->nip_pejabat ?: null,
            'telepon'        => $this->telepon ?: null,
            'email'          => $this->email ?: null,
            'keterangan'     => $this->keterangan ?: null,
        ];

        if ($this->editMode && $this->editId) {
            DimFakultas::findOrFail($this->editId)->update($data);
            session()->flash('success', 'Fakultas berhasil diperbarui.');
        } else {
            DimFakultas::create($data);
            session()->flash('success', 'Fakultas berhasil ditambahkan.');
        }

        $this->showModal = false;
        $this->reset([
            'editId', 'nama_fakultas', 'kode_fakultas', 'berlaku_dari', 'berlaku_sampai',
            'pejabat', 'nip_pejabat', 'telepon', 'email', 'keterangan'
        ]);
    }

    public function toggleStatus(int $id): void
    {
        $f = DimFakultas::findOrFail($id);
        $f->update(['status_terkini' => !$f->status_terkini]);
    }

    public function openAlias(int $id): void
    {
        $f = DimFakultas::with('alias')->findOrFail($id);
        $this->aliasForFakultasId   = $f->id;
        $this->aliasForFakultasNama = $f->nama_fakultas;
        $this->existingAliases      = $f->alias->toArray();
        $this->nama_alias           = '';
        $this->showAliasModal       = true;
    }

    public function saveAlias(): void
    {
        $this->validate(['nama_alias' => 'required|string|max:255']);
        FakultasAlias::firstOrCreate([
            'fakultas_id' => $this->aliasForFakultasId,
            'nama_alias'  => $this->nama_alias,
        ]);
        $this->existingAliases = DimFakultas::with('alias')->find($this->aliasForFakultasId)->alias->toArray();
        $this->nama_alias = '';
        session()->flash('success_alias', 'Alias berhasil ditambahkan.');
    }

    public function deleteAlias(int $id): void
    {
        FakultasAlias::destroy($id);
        $this->existingAliases = DimFakultas::with('alias')->find($this->aliasForFakultasId)->alias->toArray();
    }

    public function deleteFakultas(int $id): void
    {
        $fakultas = DimFakultas::findOrFail($id);

        try {
            DB::transaction(function () use ($fakultas) {
                $fakultas->alias()->delete();
                $fakultas->delete();
            });
            session()->flash('success', 'Fakultas berhasil dihapus.');
        } catch (QueryException $e) {
            if ($e->getCode() == '23000') {
                session()->flash('error', "Gagal menghapus! Fakultas '{$fakultas->nama_fakultas}' tidak bisa dihapus karena masih digunakan pada data Program Studi. Saran: Nonaktifkan saja statusnya.");
            } else {
                session()->flash('error', 'Terjadi kesalahan sistem saat menghapus data.');
            }
        }
    }

    public function render()
    {
        $fakultas = DimFakultas::withCount('programStudi')
            ->when($this->search, fn($q) => $q->where('nama_fakultas', 'like', "%{$this->search}%"))
            ->orderBy('nama_fakultas')
            ->paginate(10);

        return view('livewire.master-data.fakultas-index', compact('fakultas'))
            ->layout('layouts.app', ['title' => 'Manajemen Fakultas']);
    }
}
