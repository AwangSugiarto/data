<?php

namespace App\Livewire\Import;

use App\Models\ImportBatch;
use Livewire\Component;
use Livewire\WithPagination;

class ImportRiwayat extends Component
{
    use WithPagination;

    public string $search = '';

    public function render()
    {
        $batches = ImportBatch::with('uploader')
            ->when($this->search, fn($q) => $q->where('nama_file_asal', 'like', "%{$this->search}%"))
            ->latest()
            ->paginate(10);

        return view('livewire.import.import-riwayat', compact('batches'))
            ->layout('layouts.app', ['title' => 'Riwayat Import']);
    }
}
