<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // System Roles (Admin Panel)
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $crmAdmin = Role::firstOrCreate(['name' => 'crm_admin', 'guard_name' => 'web']);

        // Client Roles (App Panel)
        $clientAdmin = Role::firstOrCreate(['name' => 'client_admin', 'guard_name' => 'web']);
        $clientUser = Role::firstOrCreate(['name' => 'client_user', 'guard_name' => 'web']);

        // Note: Permissions will be handled ideally via Filament Shield or Policies later
    }
}
