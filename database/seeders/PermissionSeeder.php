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
        // Define permissions by module
        $permissions = [
            // Users Module
            'users.view' => 'View Users',
            'users.create' => 'Create Users',
            'users.edit' => 'Edit Users',
            'users.delete' => 'Delete Users',

            // Roles Module
            'roles.view' => 'View Roles',
            'roles.create' => 'Create Roles',
            'roles.edit' => 'Edit Roles',
            'roles.delete' => 'Delete Roles',

            // Trains Module
            'trains.view' => 'View Trains',
            'trains.create' => 'Create Trains',
            'trains.edit' => 'Edit Trains',
            'trains.delete' => 'Delete Trains',

            // Schedules Module
            'schedules.view' => 'View Schedules',
            'schedules.create' => 'Create Schedules',
            'schedules.edit' => 'Edit Schedules',
            'schedules.delete' => 'Delete Schedules',

            // Bookings Module
            'bookings.view' => 'View Bookings',
            'bookings.create' => 'Create Bookings',
            'bookings.edit' => 'Edit Bookings',
            'bookings.delete' => 'Delete Bookings',

            // Notifications Module
            'notifications.view' => 'View Notifications',
            'notifications.send' => 'Send Notifications',

            // Risk Areas
            'risk_areas.view' => 'View Risk Areas',
            'risk_areas.create' => 'Create Risk Areas',
            'risk_areas.edit' => 'Edit Risk Areas',
            'risk_areas.delete' => 'Delete Risk Areas',
        ];

        // Create permissions
        foreach ($permissions as $permission => $description) {
            Permission::firstOrCreate(
                ['name' => $permission],
                ['guard_name' => 'web']
            );
        }

        // Get or create roles
        $adminRole = Role::firstOrCreate(['name' => 'admin'], ['guard_name' => 'web']);
        $staffRole = Role::firstOrCreate(['name' => 'staff'], ['guard_name' => 'web']);
        $userRole = Role::firstOrCreate(['name' => 'user'], ['guard_name' => 'web']);

        // Assign all permissions to admin
        $adminRole->syncPermissions(array_keys($permissions));

        // Assign specific permissions to staff
        $staffPermissions = [
            'schedules.view',
            'schedules.create',
            'schedules.edit',
            'bookings.view',
            'notifications.view',
            'notifications.send',
            'risk_areas.view',
            'risk_areas.create',
            'risk_areas.edit',
        ];
        $staffRole->syncPermissions($staffPermissions);

        // Assign basic permissions to user
        $userPermissions = [
            'bookings.view',
            'bookings.create',
            'notifications.view',
            'risk_areas.view',
        ];
        $userRole->syncPermissions($userPermissions);
    }
}
