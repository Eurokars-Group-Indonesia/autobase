<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TransactionHeaderSeeder extends Seeder
{
    public function run(): void
    {
        $adminUserId = DB::table('ms_users')->where('email', 'admin@example.com')->value('user_id');
        
        if (!$adminUserId) {
            $this->command->error('Admin user not found. Please run RBACSeeder first.');
            return;
        }

        // Create Transaction Header Permission (only view)
        $permissions = [
            ['permission_code' => 'transactions.view', 'permission_name' => 'View Transactions'],
        ];

        $permissionIds = [];
        foreach ($permissions as $permission) {
            $existingPermission = DB::table('ms_permissions')
                ->where('permission_code', $permission['permission_code'])
                ->first();

            if (!$existingPermission) {
                $permissionId = DB::table('ms_permissions')->insertGetId([
                    'permission_code' => $permission['permission_code'],
                    'permission_name' => $permission['permission_name'],
                    'created_by' => $adminUserId,
                    'created_date' => now(),
                    'unique_id' => (string) Str::uuid(),
                    'is_active' => '1',
                ]);
                $permissionIds[] = $permissionId;
                $this->command->info("Created permission: {$permission['permission_code']}");
            } else {
                $permissionIds[] = $existingPermission->permission_id;
                $this->command->info("Permission already exists: {$permission['permission_code']}");
            }
        }

        // Create Transaction Header Menu
        $existingMenu = DB::table('ms_menus')->where('menu_code', 'transactions')->first();
        
        if (!$existingMenu) {
            $menuId = DB::table('ms_menus')->insertGetId([
                'menu_code' => 'transactions',
                'menu_name' => 'Transactions',
                'menu_url' => '/transactions',
                'menu_icon' => 'bi-receipt',
                'parent_id' => null,
                'menu_order' => 70,
                'created_by' => $adminUserId,
                'created_date' => now(),
                'unique_id' => (string) Str::uuid(),
                'is_active' => '1',
            ]);
            $this->command->info("Created menu: Transactions");
        } else {
            $menuId = $existingMenu->menu_id;
            $this->command->info("Menu already exists: Transactions");
        }

        // Attach permissions to Admin role
        $adminRoleId = DB::table('ms_role')->where('role_code', 'ADMIN')->value('role_id');
        
        if ($adminRoleId) {
            foreach ($permissionIds as $permissionId) {
                $existingRolePermission = DB::table('ms_role_permissions')
                    ->where('role_id', $adminRoleId)
                    ->where('permission_id', $permissionId)
                    ->first();

                if (!$existingRolePermission) {
                    DB::table('ms_role_permissions')->insert([
                        'role_id' => $adminRoleId,
                        'permission_id' => $permissionId,
                        'created_by' => $adminUserId,
                        'created_date' => now(),
                        'unique_id' => (string) Str::uuid(),
                        'is_active' => '1',
                    ]);
                }
            }
            $this->command->info("Attached transaction permissions to Admin role");

            // Attach menu to Admin role
            $existingRoleMenu = DB::table('ms_role_menus')
                ->where('role_id', $adminRoleId)
                ->where('menu_id', $menuId)
                ->first();

            if (!$existingRoleMenu) {
                DB::table('ms_role_menus')->insert([
                    'role_id' => $adminRoleId,
                    'menu_id' => $menuId,
                    'created_by' => $adminUserId,
                    'created_date' => now(),
                    'unique_id' => (string) Str::uuid(),
                    'is_active' => '1',
                ]);
                $this->command->info("Attached transaction menu to Admin role");
            }
        }

        $this->command->info('Transaction Header module seeded successfully!');
    }
}
