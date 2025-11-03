<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create permissions for the ERP system
        $permissions = [
            // Admin access
            'access_admin',

            // Company permissions
            'view_companies',
            'create_companies',
            'update_companies',
            'delete_companies',

            // User permissions
            'view_users',
            'create_users',
            'update_users',
            'delete_users',

            // Settings permissions
            'view_settings',
            'manage_settings',
            'manage_financial_settings',

            // Permission management
            'manage_permissions',

            // General permissions
            'export_data',
            'import_data',
            'view_reports',
            'manage_reports',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $this->command->info('Permissions created successfully!');
    }
}
