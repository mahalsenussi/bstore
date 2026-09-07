<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $superAdmin = Role::firstOrCreate(['name' => 'super-admin']);

        $permissions = [
            'view dashboard',
            'manage brands',
            'manage stores',
            'manage categories',
            'manage products',
            'manage inventory',
            'manage orders',
            'manage customers',
            'manage promotions',
            'manage users',
            'view reports',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $superAdmin->syncPermissions($permissions);
    }
}
