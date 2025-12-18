<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Permissions
//        $permissions = [
//            'view users',
//            'edit users',
//            'delete users',
//            'create users',
//        ];
//
//        foreach ($permissions as $permission) {
//            Permission::firstOrCreate(['name' => $permission]);
//        }

        // Create Roles and Assign Permissions
        $admin = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'api']);
        $owner = Role::firstOrCreate(['name' => 'moderator', 'guard_name' => 'api']);
        $employee = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'api']);

//        $admin->syncPermissions(Permission::all());
//        $owner->syncPermissions(['view users', 'edit users']);
//        $employee->syncPermissions(['view users']);
    }
}
