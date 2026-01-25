<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Menu;
use App\Models\Permission;
use App\Models\Role;

class UpdateMenuStructureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminUserId = 1; // Assuming admin user ID is 1

        // ========================================
        // 1. Tambahkan Permission Baru
        // ========================================
        
        $newPermissions = [
            [
                'permission_code' => 'import-history.view',
                'permission_name' => 'View Import History',
            ],
            [
                'permission_code' => 'transaction-body.view',
                'permission_name' => 'View Transaction Body',
            ],
        ];

        $createdPermissions = [];
        foreach ($newPermissions as $permData) {
            // Check if permission already exists
            $existing = Permission::where('permission_code', $permData['permission_code'])->first();
            
            if (!$existing) {
                $permission = Permission::create([
                    'permission_code' => $permData['permission_code'],
                    'permission_name' => $permData['permission_name'],
                    'unique_id' => (string) Str::uuid(),
                    'created_by' => $adminUserId,
                    'is_active' => '1',
                ]);
                $createdPermissions[] = $permission;
                echo "✅ Created permission: {$permData['permission_code']}\n";
            } else {
                $createdPermissions[] = $existing;
                echo "ℹ️  Permission already exists: {$permData['permission_code']}\n";
            }
        }

        // ========================================
        // 2. Update Struktur Menu
        // ========================================

        // Hapus menu lama yang tidak sesuai struktur baru (opsional, bisa di-comment jika tidak ingin menghapus)
        // Menu::whereIn('menu_code', ['brands', 'dealers', 'transactions', 'search-history', 'import-history'])->delete();

        // Struktur Menu Baru:
        // 2. Master
        //    2.1 Brands
        //    2.2 Dealers
        // 3. Transactions
        //    3.1 Master Transaction
        //    3.2 Detail Transaction
        // 4. History
        //    4.1 Search History
        //    4.2 Import History

        // ========================================
        // 2. Master (Parent Menu)
        // ========================================
        $masterMenu = Menu::where('menu_code', 'master')->first();
        if (!$masterMenu) {
            $masterMenu = Menu::create([
                'menu_code' => 'master',
                'menu_name' => 'Master',
                'menu_url' => null,
                'menu_icon' => 'bi-database',
                'parent_id' => null,
                'menu_order' => 2,
                'unique_id' => (string) Str::uuid(),
                'created_by' => $adminUserId,
                'is_active' => '1',
            ]);
            echo "✅ Created menu: Master\n";
        } else {
            $masterMenu->update(['menu_order' => 2]);
            echo "ℹ️  Menu already exists: Master\n";
        }

        // 2.1 Brands
        $brandsMenu = Menu::where('menu_code', 'brands')->first();
        if ($brandsMenu) {
            $brandsMenu->update([
                'parent_id' => $masterMenu->menu_id,
                'menu_order' => 1,
            ]);
            echo "✅ Updated menu: Brands (moved under Master)\n";
        } else {
            Menu::create([
                'menu_code' => 'brands',
                'menu_name' => 'Brands',
                'menu_url' => '/brands',
                'menu_icon' => 'bi-tag',
                'parent_id' => $masterMenu->menu_id,
                'menu_order' => 1,
                'unique_id' => (string) Str::uuid(),
                'created_by' => $adminUserId,
                'is_active' => '1',
            ]);
            echo "✅ Created menu: Brands\n";
        }

        // 2.2 Dealers
        $dealersMenu = Menu::where('menu_code', 'dealers')->first();
        if ($dealersMenu) {
            $dealersMenu->update([
                'parent_id' => $masterMenu->menu_id,
                'menu_order' => 2,
            ]);
            echo "✅ Updated menu: Dealers (moved under Master)\n";
        } else {
            Menu::create([
                'menu_code' => 'dealers',
                'menu_name' => 'Dealers',
                'menu_url' => '/dealers',
                'menu_icon' => 'bi-shop',
                'parent_id' => $masterMenu->menu_id,
                'menu_order' => 2,
                'unique_id' => (string) Str::uuid(),
                'created_by' => $adminUserId,
                'is_active' => '1',
            ]);
            echo "✅ Created menu: Dealers\n";
        }

        // ========================================
        // 3. Transactions (Parent Menu)
        // ========================================
        $transactionsMenu = Menu::where('menu_code', 'transactions')->first();
        if (!$transactionsMenu) {
            $transactionsMenu = Menu::create([
                'menu_code' => 'transactions',
                'menu_name' => 'Transactions',
                'menu_url' => null,
                'menu_icon' => 'bi-receipt',
                'parent_id' => null,
                'menu_order' => 3,
                'unique_id' => (string) Str::uuid(),
                'created_by' => $adminUserId,
                'is_active' => '1',
            ]);
            echo "✅ Created menu: Transactions\n";
        } else {
            $transactionsMenu->update([
                'menu_url' => null, // Make it parent menu
                'menu_order' => 3,
            ]);
            echo "✅ Updated menu: Transactions (converted to parent menu)\n";
        }

        // 3.1 Master Transaction (Transaction Header)
        $masterTransactionMenu = Menu::where('menu_code', 'master-transaction')->first();
        if (!$masterTransactionMenu) {
            Menu::create([
                'menu_code' => 'master-transaction',
                'menu_name' => 'Master Transaction',
                'menu_url' => '/transactions',
                'menu_icon' => 'bi-file-earmark-text',
                'parent_id' => $transactionsMenu->menu_id,
                'menu_order' => 1,
                'unique_id' => (string) Str::uuid(),
                'created_by' => $adminUserId,
                'is_active' => '1',
            ]);
            echo "✅ Created menu: Master Transaction\n";
        } else {
            echo "ℹ️  Menu already exists: Master Transaction\n";
        }

        // 3.2 Detail Transaction (Transaction Body)
        $detailTransactionMenu = Menu::where('menu_code', 'detail-transaction')->first();
        if (!$detailTransactionMenu) {
            Menu::create([
                'menu_code' => 'detail-transaction',
                'menu_name' => 'Detail Transaction',
                'menu_url' => '/transaction-body',
                'menu_icon' => 'bi-list-ul',
                'parent_id' => $transactionsMenu->menu_id,
                'menu_order' => 2,
                'unique_id' => (string) Str::uuid(),
                'created_by' => $adminUserId,
                'is_active' => '1',
            ]);
            echo "✅ Created menu: Detail Transaction\n";
        } else {
            echo "ℹ️  Menu already exists: Detail Transaction\n";
        }

        // ========================================
        // 4. History (Parent Menu)
        // ========================================
        $historyMenu = Menu::where('menu_code', 'history')->first();
        if (!$historyMenu) {
            $historyMenu = Menu::create([
                'menu_code' => 'history',
                'menu_name' => 'History',
                'menu_url' => null,
                'menu_icon' => 'bi-clock-history',
                'parent_id' => null,
                'menu_order' => 4,
                'unique_id' => (string) Str::uuid(),
                'created_by' => $adminUserId,
                'is_active' => '1',
            ]);
            echo "✅ Created menu: History\n";
        } else {
            echo "ℹ️  Menu already exists: History\n";
        }

        // 4.1 Search History
        $searchHistoryMenu = Menu::where('menu_code', 'search-history')->first();
        if ($searchHistoryMenu) {
            $searchHistoryMenu->update([
                'parent_id' => $historyMenu->menu_id,
                'menu_order' => 1,
            ]);
            echo "✅ Updated menu: Search History (moved under History)\n";
        } else {
            Menu::create([
                'menu_code' => 'search-history',
                'menu_name' => 'Search History',
                'menu_url' => '/search-history',
                'menu_icon' => 'bi-search',
                'parent_id' => $historyMenu->menu_id,
                'menu_order' => 1,
                'unique_id' => (string) Str::uuid(),
                'created_by' => $adminUserId,
                'is_active' => '1',
            ]);
            echo "✅ Created menu: Search History\n";
        }

        // 4.2 Import History
        $importHistoryMenu = Menu::where('menu_code', 'import-history')->first();
        if ($importHistoryMenu) {
            $importHistoryMenu->update([
                'parent_id' => $historyMenu->menu_id,
                'menu_order' => 2,
            ]);
            echo "✅ Updated menu: Import History (moved under History)\n";
        } else {
            Menu::create([
                'menu_code' => 'import-history',
                'menu_name' => 'Import History',
                'menu_url' => '/import-history',
                'menu_icon' => 'bi-file-earmark-arrow-up',
                'parent_id' => $historyMenu->menu_id,
                'menu_order' => 2,
                'unique_id' => (string) Str::uuid(),
                'created_by' => $adminUserId,
                'is_active' => '1',
            ]);
            echo "✅ Created menu: Import History\n";
        }

        // ========================================
        // 3. Assign Permissions & Menus to Admin Role
        // ========================================
        
        $adminRole = Role::where('role_code', 'ADMIN')->first();
        
        if ($adminRole) {
            // Assign new permissions to admin role
            foreach ($createdPermissions as $permission) {
                $exists = DB::table('ms_role_permissions')
                    ->where('role_id', $adminRole->role_id)
                    ->where('permission_id', $permission->permission_id)
                    ->exists();
                
                if (!$exists) {
                    DB::table('ms_role_permissions')->insert([
                        'role_id' => $adminRole->role_id,
                        'permission_id' => $permission->permission_id,
                        'unique_id' => (string) Str::uuid(),
                        'created_by' => $adminUserId,
                        'created_date' => now(),
                        'is_active' => '1',
                    ]);
                    echo "✅ Assigned permission to Admin: {$permission->permission_code}\n";
                }
            }

            // Assign new menus to admin role
            $newMenus = Menu::whereIn('menu_code', [
                'master', 'history', 'master-transaction', 'detail-transaction'
            ])->get();

            foreach ($newMenus as $menu) {
                $exists = DB::table('ms_role_menus')
                    ->where('role_id', $adminRole->role_id)
                    ->where('menu_id', $menu->menu_id)
                    ->exists();
                
                if (!$exists) {
                    DB::table('ms_role_menus')->insert([
                        'role_id' => $adminRole->role_id,
                        'menu_id' => $menu->menu_id,
                        'unique_id' => (string) Str::uuid(),
                        'created_by' => $adminUserId,
                        'created_date' => now(),
                        'is_active' => '1',
                    ]);
                    echo "✅ Assigned menu to Admin: {$menu->menu_name}\n";
                }
            }
        }

        echo "\n✅ Menu structure updated successfully!\n";
        echo "\nFinal Menu Structure:\n";
        echo "1. User Management\n";
        echo "   1.1 Users\n";
        echo "   1.2 Roles\n";
        echo "   1.3 Permissions\n";
        echo "   1.4 Menus\n";
        echo "2. Master\n";
        echo "   2.1 Brands\n";
        echo "   2.2 Dealers\n";
        echo "3. Transactions\n";
        echo "   3.1 Master Transaction\n";
        echo "   3.2 Detail Transaction\n";
        echo "4. History\n";
        echo "   4.1 Search History\n";
        echo "   4.2 Import History\n";
    }
}
