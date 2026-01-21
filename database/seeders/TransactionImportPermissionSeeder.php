<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TransactionImportPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Insert permission for transaction import
        DB::table('ms_permissions')->insert([
            'permission_code' => 'transactions.header.import',
            'permission_name' => 'Import Transaction Headers',
            'is_active' => '1',
            'created_by' => 1,
            'created_date' => now(),
            'updated_by' => 1,
            'updated_date' => now(),
            'unique_id' => (string) Str::uuid(),
        ]);

        echo "Transaction header import permission created successfully!\n";
        
        // Get the permission ID
        $permission = DB::table('ms_permissions')
            ->where('permission_code', 'transactions.header.import')
            ->first();

        if ($permission) {
            // Assign to Super Admin role (role_id = 1)
            DB::table('ms_role_permissions')->insert([
                'role_id' => 1,
                'permission_id' => $permission->permission_id,
                'created_by' => 1,
                'created_date' => now(),
                'updated_by' => 1,
                'updated_date' => now(),
                'unique_id' => (string) Str::uuid(),
                'is_active' => '1',
            ]);

            echo "Permission assigned to Super Admin role!\n";
        }
    }
}
