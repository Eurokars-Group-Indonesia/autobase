<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Menu;

class RBACSeeder extends Seeder
{
    public function run(): void
    {
        // Create Admin User first (without created_by) with generated ID
        $adminUserId = 'USR00001';
        $adminUser = User::create([
            'user_id' => $adminUserId,
            'name' => 'admin',
            'email' => 'admin@example.com',
            'full_name' => 'System Administrator',
            'password' => Hash::make('password'),
            'unique_id' => (string) Str::uuid(),
            'is_active' => '1',
        ]);

        // Create Permissions
        $permissions = [
            ['permission_code' => 'users.view', 'permission_name' => 'View Users'],
            ['permission_code' => 'users.create', 'permission_name' => 'Create Users'],
            ['permission_code' => 'users.edit', 'permission_name' => 'Edit Users'],
            ['permission_code' => 'users.delete', 'permission_name' => 'Delete Users'],
            ['permission_code' => 'roles.view', 'permission_name' => 'View Roles'],
            ['permission_code' => 'roles.create', 'permission_name' => 'Create Roles'],
            ['permission_code' => 'roles.edit', 'permission_name' => 'Edit Roles'],
            ['permission_code' => 'roles.delete', 'permission_name' => 'Delete Roles'],
            ['permission_code' => 'permissions.view', 'permission_name' => 'View Permissions'],
            ['permission_code' => 'permissions.create', 'permission_name' => 'Create Permissions'],
            ['permission_code' => 'permissions.edit', 'permission_name' => 'Edit Permissions'],
            ['permission_code' => 'permissions.delete', 'permission_name' => 'Delete Permissions'],
            ['permission_code' => 'menus.view', 'permission_name' => 'View Menus'],
            ['permission_code' => 'menus.create', 'permission_name' => 'Create Menus'],
            ['permission_code' => 'menus.edit', 'permission_name' => 'Edit Menus'],
            ['permission_code' => 'menus.delete', 'permission_name' => 'Delete Menus'],
        ];

        $createdPermissions = [];
        $permissionCounter = 1;
        foreach ($permissions as $permission) {
            $permissionId = 'PRM' . str_pad($permissionCounter, 5, '0', STR_PAD_LEFT);
            $createdPermissions[] = Permission::create([
                'permission_id' => $permissionId,
                'permission_code' => $permission['permission_code'],
                'permission_name' => $permission['permission_name'],
                'unique_id' => (string) Str::uuid(),
                'created_by' => $adminUser->user_id,
                'is_active' => '1',
            ]);
            $permissionCounter++;
        }

        // Create Menus
        $menuCounter = 1;
        $userManagementId = 'MNU' . str_pad($menuCounter++, 5, '0', STR_PAD_LEFT);
        $userManagement = Menu::create([
            'menu_id' => $userManagementId,
            'menu_code' => 'user_management',
            'menu_name' => 'User Management',
            'menu_url' => null,
            'menu_icon' => 'bi-people',
            'parent_id' => null,
            'menu_order' => 1,
            'unique_id' => (string) Str::uuid(),
            'created_by' => $adminUser->user_id,
            'is_active' => '1',
        ]);

        Menu::create([
            'menu_id' => 'MNU' . str_pad($menuCounter++, 5, '0', STR_PAD_LEFT),
            'menu_code' => 'users',
            'menu_name' => 'Users',
            'menu_url' => '/users',
            'menu_icon' => 'bi-person',
            'parent_id' => $userManagement->menu_id,
            'menu_order' => 1,
            'unique_id' => (string) Str::uuid(),
            'created_by' => $adminUser->user_id,
            'is_active' => '1',
        ]);

        Menu::create([
            'menu_id' => 'MNU' . str_pad($menuCounter++, 5, '0', STR_PAD_LEFT),
            'menu_code' => 'roles',
            'menu_name' => 'Roles',
            'menu_url' => '/roles',
            'menu_icon' => 'bi-shield-check',
            'parent_id' => $userManagement->menu_id,
            'menu_order' => 2,
            'unique_id' => (string) Str::uuid(),
            'created_by' => $adminUser->user_id,
            'is_active' => '1',
        ]);

        Menu::create([
            'menu_id' => 'MNU' . str_pad($menuCounter++, 5, '0', STR_PAD_LEFT),
            'menu_code' => 'permissions',
            'menu_name' => 'Permissions',
            'menu_url' => '/permissions',
            'menu_icon' => 'bi-key',
            'parent_id' => $userManagement->menu_id,
            'menu_order' => 3,
            'unique_id' => (string) Str::uuid(),
            'created_by' => $adminUser->user_id,
            'is_active' => '1',
        ]);

        Menu::create([
            'menu_id' => 'MNU' . str_pad($menuCounter++, 5, '0', STR_PAD_LEFT),
            'menu_code' => 'menus',
            'menu_name' => 'Menus',
            'menu_url' => '/menus',
            'menu_icon' => 'bi-menu-button-wide',
            'parent_id' => $userManagement->menu_id,
            'menu_order' => 4,
            'unique_id' => (string) Str::uuid(),
            'created_by' => $adminUser->user_id,
            'is_active' => '1',
        ]);

        // Create Admin Role
        $adminRoleId = 'ROL00001';
        $adminRole = Role::create([
            'role_id' => $adminRoleId,
            'role_code' => 'ADMIN',
            'role_name' => 'Administrator',
            'role_description' => 'Full system access',
            'unique_id' => (string) Str::uuid(),
            'created_by' => $adminUser->user_id,
            'is_active' => '1',
        ]);

        // Attach all permissions to admin role
        $rolePermissionCounter = 1;
        foreach ($createdPermissions as $permission) {
            $adminRole->permissions()->attach($permission->permission_id, [
                'role_permission_id' => 'RPM' . str_pad($rolePermissionCounter++, 5, '0', STR_PAD_LEFT),
                'unique_id' => (string) Str::uuid(),
                'created_by' => $adminUser->user_id,
                'created_date' => now(),
                'is_active' => '1',
            ]);
        }

        // Attach all menus to admin role
        $allMenus = Menu::all();
        $roleMenuCounter = 1;
        foreach ($allMenus as $menu) {
            $adminRole->menus()->attach($menu->menu_id, [
                'role_menu_id' => 'RMN' . str_pad($roleMenuCounter++, 5, '0', STR_PAD_LEFT),
                'unique_id' => (string) Str::uuid(),
                'created_by' => $adminUser->user_id,
                'created_date' => now(),
                'is_active' => '1',
            ]);
        }

        // Assign admin role to admin user
        $adminUser->roles()->attach($adminRole->role_id, [
            'user_role_id' => 'URO00001',
            'unique_id' => (string) Str::uuid(),
            'assigned_date' => now(),
            'created_by' => $adminUser->user_id,
            'created_date' => now(),
            'is_active' => '1',
        ]);
    }
}
