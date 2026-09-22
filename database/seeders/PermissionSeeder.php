<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'view_dashboard',
            'view_data_individu',
            'view_master_data',
            'view_import',
            'view_laporan',
            'manage_users',
            'manage_roles_permissions',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Get Roles
        $superAdmin = Role::firstOrCreate(['name' => 'super-admin']);
        $adminPmb = Role::firstOrCreate(['name' => 'admin-pmb']);
        $pimpinan = Role::firstOrCreate(['name' => 'pimpinan']);
        $viewerFakultas = Role::firstOrCreate(['name' => 'viewer-fakultas']);

        // Assign Permissions to Roles (Defaults)
        // Super Admin gets everything
        $superAdmin->syncPermissions(Permission::all());

        // Admin PMB
        $adminPmb->syncPermissions([
            'view_dashboard',
            'view_data_individu',
            'view_master_data',
            'view_import',
            'view_laporan',
        ]);

        // Pimpinan
        $pimpinan->syncPermissions([
            'view_dashboard',
            'view_laporan',
        ]);

        // Viewer Fakultas
        $viewerFakultas->syncPermissions([
            'view_dashboard',
        ]);
    }
}
