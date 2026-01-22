<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SearchHistoryMenuSeeder extends Seeder
{
    public function run(): void
    {
        // Insert menu for Search History
        DB::table('ms_menus')->insert([
            'menu_code' => 'search-history',
            'menu_name' => 'Search History',
            'menu_url' => '/search-history',
            'menu_icon' => 'bi bi-clock-history',
            'parent_id' => null,
            'menu_order' => 100,
            'is_active' => '1',
            'created_by' => 1,
            'created_date' => now(),
            'unique_id' => (string) Str::uuid(),
        ]);

        echo "Search History menu created successfully!\n";
        
        // Get the menu ID
        $menu = DB::table('ms_menus')
            ->where('menu_url', '/search-history')
            ->first();

        if (!$menu) {
            echo "Error: Menu not found after creation!\n";
            return;
        }

        // Assign menu to Administrator role (role_id = 1)
        DB::table('ms_role_menus')->insert([
            'role_id' => 1,
            'menu_id' => $menu->menu_id,
            'created_by' => 1,
            'created_date' => now(),
            'unique_id' => (string) Str::uuid(),
        ]);

        echo "Menu assigned to Administrator role successfully!\n";
    }
}
