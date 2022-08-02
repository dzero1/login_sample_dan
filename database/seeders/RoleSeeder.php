<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds and create the initial roles and permissions.
     *
     * @return void
     */
    public function run()
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // create permissions
        $userPermissionNames = ['dashboard', 'view profile', 'edit profile'];
        $adminPermissionNames = array_merge($userPermissionNames, ['delete profile', 'view report', 'generate report']);
        $permissions = collect($adminPermissionNames)->map(function ($permission) {
            return ['name' => $permission, 'guard_name' => 'web'];
        });
        Permission::insert($permissions->toArray());

        // create roles and assign created permissions

        // admin role
        Role::create(['name' => 'admin'])
            ->givePermissionTo($adminPermissionNames);

        // user role
        Role::create(['name' => 'user'])
            ->givePermissionTo($userPermissionNames);

        Role::create(['name' => $_ENV['SUPER_ADMIN_ROLE']])
            ->givePermissionTo(Permission::all());

    }
}
