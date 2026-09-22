<?php

namespace App\Livewire\Pengaturan;

use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Permission;

class PermissionIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public bool $showModal = false;
    public bool $editMode = false;
    public ?int $editId = null;
    public string $name = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->reset(['editId', 'name']);
        $this->editMode = false;
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $permission = Permission::findById($id);
        $this->editId = $permission->id;
        $this->name   = $permission->name;
        
        $this->editMode = true;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('permissions')->ignore($this->editId)],
        ]);

        if ($this->editMode && $this->editId) {
            $permission = Permission::findById($this->editId);
            $permission->name = $this->name;
            $permission->save();
            
            session()->flash('success', 'Permission berhasil diperbarui.');
        } else {
            Permission::create(['name' => $this->name]);
            session()->flash('success', 'Permission berhasil ditambahkan.');
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        $this->showModal = false;
    }

    public function deletePermission(int $id): void
    {
        $permission = Permission::findById($id);
        $permission->delete();
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        
        session()->flash('success', 'Permission berhasil dihapus.');
    }

    public function render()
    {
        $permissions = Permission::when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy('name')
            ->paginate(10);
            
        return view('livewire.pengaturan.permission-index', compact('permissions'))
            ->layout('layouts.app', ['title' => 'Manajemen Permission']);
    }
}
