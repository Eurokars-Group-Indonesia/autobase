<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SearchHistoryPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Insert permission for search history view
        DB::table('ms_permissions')->insert([
            'permission_code' => 'search-history.view',
            'permission_name' => 'View Search History',
            'is_active' => '1',
            'created_by' => 1,
            'created_date' => now(),
            'unique_id' => (string) Str::uuid(),
        ]);

        echo "Search history view permission created successfully!\n";
        
        // Get the permission ID
        $permission = DB::table('ms_permissions')
            ->where('permission_code', 'search-history.view')
            ->first();

        if (!$permission) {
            echo "Error: Permission not found after creation!\n";
            return;
        }

        // Assign permission to Administrator role (role_id = 1)
        DB::table('ms_role_permissions')->insert([
            'role_id' => 1,
            'permission_id' => $permission->permission_id,
            'created_by' => 1,
            'created_date' => now(),
            'unique_id' => (string) Str::uuid(),
        ]);

        echo "Permission assigned to Administrator role successfully!\n";
    }
}
