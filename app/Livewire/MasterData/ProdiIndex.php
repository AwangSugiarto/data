<?php

namespace App\Livewire\MasterData;

use App\Models\DimFakultas;
use App\Models\DimProgramStudi;
use App\Models\ProdiAlias;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class ProdiIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterFakultas = '';
    public string $filterJenjang = '';
    public string $sortColumn = 'nama_prodi';
    public string $sortDirection = 'asc';
    public bool $showModal = false;
    public bool $showAliasModal = false;
    public bool $editMode = false;

    public ?int $editId = null;
    public int|string $fakultas_id = '';
    public string $nama_prodi = '';
    public string $kode_prodi = '';
    public string $jenjang = 'S1';
    public bool $status_terkini = true;
    public string $berlaku_dari = '';
    public string $berlaku_sampai = '';

    // New Metadata Fields
    public string $kode_dikti = '';
    public string $nama_jenjang_pendidikan = '';
    public string $gelar = '';
    public string $akreditasi = '';
    public string $no_sk_dikti = '';
    public string $tgl_sk_dikti = '';
    public string $tgl_akhir_sk_dikti = '';
    public string $no_sk_ban = '';
    public string $tgl_sk_ban = '';
    public string $tgl_akhir_sk_ban = '';
    public string $tanggal_pendirian = '';
    public string $telepon = '';
    public string $email = '';
    public string $pejabat = '';
    public string $jabatan = '';
    public $nama_sekprodi;
    public $nip_sekprodi;
    public $keterangan;

    public $selectedJalurs = [];
    public $jalurOptions = [];

    // Alias
    public ?int $aliasForProdiId = null;
    public string $aliasForProdiNama = '';
    public string $nama_alias = '';
    public array $existingAliases = [];

    public function mount()
    {
        $this->jalurOptions = \App\Models\DimJalurSeleksi::where('status_aktif', true)->orderBy('nama_jalur')->get();
    }

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingFilterFakultas(): void { $this->resetPage(); }

    public function openCreate(): void
    {
        $this->reset([
            'editId', 'nama_prodi', 'kode_prodi', 'berlaku_dari', 'berlaku_sampai',
            'kode_dikti', 'nama_jenjang_pendidikan', 'gelar', 'akreditasi',
            'no_sk_dikti', 'tgl_sk_dikti', 'tgl_akhir_sk_dikti',
            'no_sk_ban', 'tgl_sk_ban', 'tgl_akhir_sk_ban',
            'tanggal_pendirian', 'telepon', 'email', 'pejabat', 'jabatan',
            'nama_sekprodi', 'nip_sekprodi', 'keterangan'
        ]);
        $this->fakultas_id = '';
        $this->jenjang = 'S1';
        $this->status_terkini = true;
        $this->editMode = false;
        $this->showModal = true;
        $this->selectedJalurs = [];
    }

    public function openEdit(int $id): void
    {
        $p = DimProgramStudi::findOrFail($id);
        $this->editId         = $p->id;
        $this->fakultas_id    = $p->fakultas_id;
        $this->nama_prodi     = $p->nama_prodi;
        $this->kode_prodi     = $p->kode_prodi ?? '';
        $this->jenjang        = $p->jenjang;
        $this->status_terkini = $p->status_terkini;
        $this->berlaku_dari   = $p->berlaku_dari?->format('Y-m-d') ?? '';
        $this->berlaku_sampai = $p->berlaku_sampai?->format('Y-m-d') ?? '';

        $this->kode_dikti = $p->kode_dikti ?? '';
        $this->nama_jenjang_pendidikan = $p->nama_jenjang_pendidikan ?? '';
        $this->gelar = $p->gelar ?? '';
        $this->akreditasi = $p->akreditasi ?? '';
        $this->no_sk_dikti = $p->no_sk_dikti ?? '';
        $this->tgl_sk_dikti = $p->tgl_sk_dikti?->format('Y-m-d') ?? '';
        $this->tgl_akhir_sk_dikti = $p->tgl_akhir_sk_dikti?->format('Y-m-d') ?? '';
        $this->no_sk_ban = $p->no_sk_ban ?? '';
        $this->tgl_sk_ban = $p->tgl_sk_ban?->format('Y-m-d') ?? '';
        $this->tgl_akhir_sk_ban = $p->tgl_akhir_sk_ban?->format('Y-m-d') ?? '';
        $this->tanggal_pendirian = $p->tanggal_pendirian?->format('Y-m-d') ?? '';
        $this->telepon = $p->telepon ?? '';
        $this->email = $p->email ?? '';
        $this->pejabat = $p->pejabat ?? '';
        $this->jabatan = $p->jabatan ?? '';
        $this->nama_sekprodi = $p->nama_sekprodi ?? '';
        $this->nip_sekprodi = $p->nip_sekprodi ?? '';
        $this->keterangan = $p->keterangan ?? '';

        $this->selectedJalurs = $p->jalurSeleksi->pluck('id')->toArray();

        $this->editMode = true;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'fakultas_id' => 'required|exists:dim_fakultas,id',
            'nama_prodi'  => 'required|string|max:255',
            'jenjang'     => 'required|in:D3,S1,S2,S3',
            'berlaku_dari'  => 'nullable|date',
            'berlaku_sampai'=> 'nullable|date|after_or_equal:berlaku_dari',
            'kode_dikti' => 'nullable|string|max:50',
            'akreditasi' => 'nullable|string|max:50',
            'tgl_sk_dikti' => 'nullable|date',
            'tgl_akhir_sk_dikti' => 'nullable|date',
            'tgl_sk_ban' => 'nullable|date',
            'tgl_akhir_sk_ban' => 'nullable|date',
            'tanggal_pendirian' => 'nullable|date',
            'email' => 'nullable|email|max:255',
            'selectedJalurs' => 'array',
        ]);

        $data = [
            'fakultas_id'    => $this->fakultas_id,
            'nama_prodi'     => $this->nama_prodi,
            'kode_prodi'     => $this->kode_prodi ?: null,
            'jenjang'        => $this->jenjang,
            'status_terkini' => $this->status_terkini,
            'berlaku_dari'   => $this->berlaku_dari ?: null,
            'berlaku_sampai' => $this->berlaku_sampai ?: null,
            'kode_dikti' => $this->kode_dikti ?: null,
            'nama_jenjang_pendidikan' => $this->nama_jenjang_pendidikan ?: null,
            'gelar' => $this->gelar ?: null,
            'akreditasi' => $this->akreditasi ?: null,
            'no_sk_dikti' => $this->no_sk_dikti ?: null,
            'tgl_sk_dikti' => $this->tgl_sk_dikti ?: null,
            'tgl_akhir_sk_dikti' => $this->tgl_akhir_sk_dikti ?: null,
            'no_sk_ban' => $this->no_sk_ban ?: null,
            'tgl_sk_ban' => $this->tgl_sk_ban ?: null,
            'tgl_akhir_sk_ban' => $this->tgl_akhir_sk_ban ?: null,
            'tanggal_pendirian' => $this->tanggal_pendirian ?: null,
            'telepon' => $this->telepon ?: null,
            'email' => $this->email ?: null,
            'pejabat' => $this->pejabat ?: null,
            'jabatan' => $this->jabatan ?: null,
            'nama_sekprodi' => $this->nama_sekprodi ?: null,
            'nip_sekprodi' => $this->nip_sekprodi ?: null,
            'keterangan' => $this->keterangan ?: null,
        ];

        if ($this->editMode && $this->editId) {
            $prodi = DimProgramStudi::findOrFail($this->editId);
            $prodi->update($data);
            $prodi->jalurSeleksi()->sync($this->selectedJalurs);
            session()->flash('success', 'Program Studi berhasil diperbarui.');
        } else {
            $prodi = DimProgramStudi::create($data);
            $prodi->jalurSeleksi()->sync($this->selectedJalurs);
            session()->flash('success', 'Program Studi berhasil ditambahkan.');
        }

        $this->showModal = false;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->reset([
            'editId', 'nama_prodi', 'kode_prodi', 'berlaku_dari', 'berlaku_sampai',
            'kode_dikti', 'nama_jenjang_pendidikan', 'gelar', 'akreditasi',
            'no_sk_dikti', 'tgl_sk_dikti', 'tgl_akhir_sk_dikti',
            'no_sk_ban', 'tgl_sk_ban', 'tgl_akhir_sk_ban',
            'tanggal_pendirian', 'telepon', 'email', 'pejabat', 'jabatan',
            'nama_sekprodi', 'nip_sekprodi', 'keterangan'
        ]);
    }

    public function toggleStatus(int $id): void
    {
        $p = DimProgramStudi::findOrFail($id);
        $p->update(['status_terkini' => !$p->status_terkini]);
    }

    public function openAlias(int $id): void
    {
        $p = DimProgramStudi::with('alias')->findOrFail($id);
        $this->aliasForProdiId   = $p->id;
        $this->aliasForProdiNama = $p->nama_prodi;
        $this->existingAliases   = $p->alias->toArray();
        $this->nama_alias        = '';
        $this->showAliasModal    = true;
    }

    public function saveAlias(): void
    {
        $this->validate(['nama_alias' => 'required|string|max:255']);
        ProdiAlias::firstOrCreate([
            'program_studi_id' => $this->aliasForProdiId,
            'nama_alias'       => $this->nama_alias,
        ]);
        $this->existingAliases = DimProgramStudi::with('alias')->find($this->aliasForProdiId)->alias->toArray();
        $this->nama_alias = '';
    }

    public function deleteAlias(int $id): void
    {
        ProdiAlias::destroy($id);
        $this->existingAliases = DimProgramStudi::with('alias')->find($this->aliasForProdiId)->alias->toArray();
    }

    public function deleteProdi(int $id): void
    {
        $prodi = DimProgramStudi::findOrFail($id);

        try {
            DB::transaction(function () use ($prodi) {
                $prodi->alias()->delete();
                $prodi->delete();
            });
            session()->flash('success', 'Program Studi berhasil dihapus.');
        } catch (QueryException $e) {
            // Error 1451 is foreign key constraint violation
            if ($e->getCode() == '23000') {
                session()->flash('error', "Gagal menghapus! Program Studi '{$prodi->nama_prodi}' tidak bisa dihapus karena masih digunakan pada data lain (seperti data pendaftar/kelulusan). Saran: Nonaktifkan saja statusnya.");
            } else {
                session()->flash('error', 'Terjadi kesalahan sistem saat menghapus data.');
            }
        }
    }

    public function sortBy(string $column): void
    {
        if ($this->sortColumn === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortColumn = $column;
            $this->sortDirection = 'asc';
        }
    }

    public function render()
    {
        $fakultasList = DimFakultas::orderBy('nama_fakultas')->get();
        $query = DimProgramStudi::with('fakultas')
            ->when($this->search, fn($q) => $q->where('nama_prodi', 'like', "%{$this->search}%"))
            ->when($this->filterFakultas, fn($q) => $q->where('fakultas_id', $this->filterFakultas))
            ->when($this->filterJenjang, fn($q) => $q->where('jenjang', $this->filterJenjang));

        if ($this->sortColumn === 'fakultas.nama_fakultas') {
            $query->leftJoin('dim_fakultas', 'dim_program_studi.fakultas_id', '=', 'dim_fakultas.id')
                  ->select('dim_program_studi.*')
                  ->orderBy('dim_fakultas.nama_fakultas', $this->sortDirection);
        } else {
            $query->orderBy($this->sortColumn, $this->sortDirection);
        }

        $prodi = $query->paginate(10);

        return view('livewire.master-data.prodi-index', compact('prodi', 'fakultasList'))
            ->layout('layouts.app', ['title' => 'Manajemen Program Studi']);
    }
}
