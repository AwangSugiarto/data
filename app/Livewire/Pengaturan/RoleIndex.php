<?php

namespace App\Livewire\Pengaturan;

use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public bool $showModal = false;
    public bool $editMode = false;
    public ?int $editId = null;
    
    public string $name = '';
    public array $selectedPermissions = [];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->reset(['editId', 'name', 'selectedPermissions']);
        $this->editMode = false;
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $role = Role::with('permissions')->findOrFail($id);
        $this->editId = $role->id;
        $this->name   = $role->name;
        $this->selectedPermissions = $role->permissions->pluck('name')->toArray();
        
        $this->editMode = true;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('roles')->ignore($this->editId)],
            'selectedPermissions' => 'array',
        ]);

        if ($this->editMode && $this->editId) {
            // Protect super-admin
            if ($this->editId === 1 && $this->name !== 'super-admin') {
                session()->flash('error', 'Nama role super-admin tidak boleh diubah.');
                return;
            }

            $role = Role::findById($this->editId);
            $role->name = $this->name;
            $role->save();
            
            $role->syncPermissions($this->selectedPermissions);
            
            session()->flash('success', 'Role dan Permission berhasil diperbarui.');
        } else {
            $role = Role::create(['name' => $this->name]);
            $role->syncPermissions($this->selectedPermissions);
            
            session()->flash('success', 'Role berhasil ditambahkan.');
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        $this->showModal = false;
    }

    public function deleteRole(int $id): void
    {
        if ($id === 1) {
            session()->flash('error', 'Role super-admin tidak dapat dihapus.');
            return;
        }

        $role = Role::findById($id);
        
        // Cek apakah role masih dipakai oleh user
        if ($role->users()->count() > 0) {
            session()->flash('error', 'Role tidak dapat dihapus karena masih digunakan oleh pengguna.');
            return;
        }

        $role->delete();
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        
        session()->flash('success', 'Role berhasil dihapus.');
    }

    public function render()
    {
        $roles = Role::with('permissions', 'users')
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy('id')
            ->paginate(10);
            
        $permissions = Permission::orderBy('name')->get();

        return view('livewire.pengaturan.role-index', compact('roles', 'permissions'))
            ->layout('layouts.app', ['title' => 'Manajemen Role & Hak Akses']);
    }
}
