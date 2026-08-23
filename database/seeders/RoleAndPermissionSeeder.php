<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Dashboard
            'view dashboard',

            // Residents
            'view residents',
            'create residents',
            'update residents',
            'delete residents',
            'verify residents',

            // Households
            'view households',
            'create households',
            'update households',
            'delete households',

            // Complaints
            'view complaints',
            'create complaints',
            'update complaints',
            'delete complaints',
            'assign complaints',
            'process complaints',
            'resolve complaints',

            // Requests
            'view requests',
            'create requests',
            'update requests',
            'delete requests',
            'approve requests',
            'reject requests',
            'process requests',

            // Calamities
            'view calamities',
            'create calamities',
            'update calamities',
            'delete calamities',
            'manage calamity response',

            // Incidents
            'view incidents',
            'create incidents',
            'update incidents',
            'resolve incidents',

            // Evacuation Centers
            'view evacuation centers',
            'create evacuation centers',
            'update evacuation centers',
            'delete evacuation centers',
            'manage evacuations',

            // Relief Inventory
            'view relief inventory',
            'create relief inventory',
            'update relief inventory',
            'manage relief distribution',

            // Assistance
            'view assistance',
            'create assistance',
            'process assistance',
            'approve assistance',
            'reject assistance',

            // Reports
            'view reports',
            'export reports',

            // System
            'manage users',
            'manage roles',
            'manage settings',
            'view audit logs',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Create roles
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $moderator = Role::firstOrCreate(['name' => 'moderator', 'guard_name' => 'web']);
        $member = Role::firstOrCreate(['name' => 'member', 'guard_name' => 'web']);

        // Admin gets all permissions
        $admin->syncPermissions($permissions);

        // Moderator permissions (Member + staff permissions)
        $moderatorPermissions = [
            'view dashboard',

            // Residents
            'view residents',
            'create residents',
            'update residents',
            'verify residents',

            // Households
            'view households',
            'create households',
            'update households',

            // Complaints
            'view complaints',
            'create complaints',
            'update complaints',
            'assign complaints',
            'process complaints',
            'resolve complaints',

            // Requests
            'view requests',
            'create requests',
            'update requests',
            'process requests',
            'approve requests',
            'reject requests',

            // Calamities
            'view calamities',
            'create calamities',
            'update calamities',
            'manage calamity response',

            // Incidents
            'view incidents',
            'create incidents',
            'update incidents',
            'resolve incidents',

            // Evacuation Centers
            'view evacuation centers',
            'create evacuation centers',
            'update evacuation centers',
            'manage evacuations',

            // Relief Inventory
            'view relief inventory',
            'create relief inventory',
            'update relief inventory',
            'manage relief distribution',

            // Assistance
            'view assistance',
            'create assistance',
            'process assistance',

            // Reports
            'view reports',
        ];
        $moderator->syncPermissions($moderatorPermissions);

        // Member permissions
        $memberPermissions = [
            'view dashboard',
            'view requests',
            'view complaints',
            'view assistance',
            'create complaints',
            'create requests',
            'create assistance',
            'view calamities',
            'view evacuation centers',
            'view incidents',
        ];
        $member->syncPermissions($memberPermissions);
    }
}
