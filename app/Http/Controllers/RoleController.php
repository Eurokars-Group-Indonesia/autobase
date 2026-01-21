<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Permission;
use App\Models\Menu;
use App\Http\Requests\RoleRequest;
use Illuminate\Support\Str;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::where('is_active', '1')->paginate(10);
        return view('roles.index', compact('roles'));
    }

    public function create()
    {
        $permissions = Permission::where('is_active', '1')->get();
        $menus = Menu::where('is_active', '1')->whereNull('parent_id')->with('children')->orderBy('menu_order')->get();
        return view('roles.create', compact('permissions', 'menus'));
    }

    public function store(RoleRequest $request)
    {
        $data = $request->validated();
        $data['unique_id'] = (string) Str::uuid();
        $data['created_by'] = auth()->id();

        $role = Role::create($data);

        // Attach permissions
        if ($request->has('permissions')) {
            foreach ($request->permissions as $permissionId) {
                $role->permissions()->attach($permissionId, [
                    'unique_id' => (string) Str::uuid(),
                    'created_by' => auth()->id(),
                    'created_date' => now(),
                    'is_active' => '1',
                ]);
            }
        }

        // Attach menus
        if ($request->has('menus')) {
            foreach ($request->menus as $menuId) {
                $role->menus()->attach($menuId, [
                    'unique_id' => (string) Str::uuid(),
                    'created_by' => auth()->id(),
                    'created_date' => now(),
                    'is_active' => '1',
                ]);
            }
        }

        return redirect()->route('roles.index')->with('success', 'Role created successfully.');
    }

    public function edit(Role $role)
    {
        $permissions = Permission::where('is_active', '1')->get();
        $menus = Menu::where('is_active', '1')->whereNull('parent_id')->with('children')->orderBy('menu_order')->get();
        $rolePermissions = $role->permissions->pluck('permission_id')->toArray();
        $roleMenus = $role->menus->pluck('menu_id')->toArray();
        
        return view('roles.edit', compact('role', 'permissions', 'menus', 'rolePermissions', 'roleMenus'));
    }

    public function update(RoleRequest $request, Role $role)
    {
        $data = $request->validated();
        $data['updated_by'] = auth()->id();
        $role->update($data);

        // Sync permissions
        $role->permissions()->detach();
        if ($request->has('permissions')) {
            foreach ($request->permissions as $permissionId) {
                $role->permissions()->attach($permissionId, [
                    'unique_id' => (string) Str::uuid(),
                    'created_by' => auth()->id(),
                    'created_date' => now(),
                    'is_active' => '1',
                ]);
            }
        }

        // Sync menus
        $role->menus()->detach();
        if ($request->has('menus')) {
            foreach ($request->menus as $menuId) {
                $role->menus()->attach($menuId, [
                    'unique_id' => (string) Str::uuid(),
                    'created_by' => auth()->id(),
                    'created_date' => now(),
                    'is_active' => '1',
                ]);
            }
        }

        return redirect()->route('roles.index')->with('success', 'Role updated successfully.');
    }

    public function destroy(Role $role)
    {
        $role->update(['is_active' => '0', 'updated_by' => auth()->id()]);
        return redirect()->route('roles.index')->with('success', 'Role deleted successfully.');
    }
}
