<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()['cache']->forget('spatie.permission.cache');

        // Define permissions
        $permissions = [
            // User management
            'view-users',
            'create-users',
            'edit-users',
            'delete-users',
            'assign-roles',

            // Train management
            'create-trains',
            'edit-trains',
            'delete-trains',
            'view-trains',

            // Schedule management
            'create-schedules',
            'edit-schedules',
            'delete-schedules',
            'view-schedules',

            // Booking management
            'view-all-bookings',
            'cancel-bookings',
            'create-bookings',
            'view-bookings',

            // Reports
            'view-reports',
            'export-data',
        ];

        // Create permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Define roles and their permissions
        $roles = [
            'admin' => $permissions, // Admin has all permissions
            'staff' => [
                'view-schedules',
                'create-schedules',
                'edit-schedules',
                'view-all-bookings',
                'cancel-bookings',
                'view-reports',
            ],
            'user' => [
                'create-bookings',
                'view-bookings',
                'view-schedules',
            ],
        ];

        // Create roles and assign permissions
        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);

            // Sync permissions to role
            foreach ($rolePermissions as $permissionName) {
                $permission = Permission::where('name', $permissionName)->first();
                if ($permission) {
                    $role->givePermissionTo($permission);
                }
            }
        }
    }
}
