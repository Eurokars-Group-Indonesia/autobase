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
        $query = Role::withCount('permissions')->where('is_active', '1');
        
        // Search functionality
        if (request()->has('search') && request('search') != '') {
            $search = request('search');
            $query->where(function($q) use ($search) {
                $q->where('role_code', 'like', $search . '%')
                  ->orWhere('role_name', 'like', $search . '%')
                  ->orWhere('role_description', 'like', $search . '%');
            });
        }
        
        $roles = $query->paginate(10)->withQueryString();
        return view('roles.index', compact('roles'));
    }

    public function create()
    {
        // Double check permission
        if (!auth()->user()->hasPermission('roles.create')) {
            abort(403, 'Unauthorized action.');
        }
        
        $permissions = Permission::where('is_active', '1')->get();
        $menus = Menu::where('is_active', '1')
            ->whereNull('parent_id')
            ->with(['children' => function($query) {
                $query->where('is_active', '1');
            }])
            ->orderBy('menu_order')
            ->get();
        return view('roles.create', compact('permissions', 'menus'));
    }

    public function store(RoleRequest $request)
    {
        // Double check permission
        if (!auth()->user()->hasPermission('roles.create')) {
            abort(403, 'Unauthorized action.');
        }
        
        $data = $request->validated();
        $uniqueId = (string) Str::uuid();
        $userId = auth()->id();        

        // Call stored procedure sp_add_ms_role
        $result = \DB::select('CALL sp_add_ms_role(?, ?, ?, ?, ?)', [
            $data['role_code'],
            $data['role_name'],
            $data['role_description'],
            $userId,
            $uniqueId
        ]);


        // Check result from stored procedure
        if (empty($result)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create role. No response from database.');
        }
        
        $response = $result[0];

        // Handle response based on return_code
        if ($response->return_code == 200) 
        {
            // Get the created user by unique_id
            $role = Role::where('unique_id', $uniqueId)->first();

            if ($role) {

                // Attach permissions
                if ($request->has('permissions')) {

                    foreach ($request->permissions as $permissionId) {
                        \DB::select('CALL sp_add_ms_role_permission(?, ?, ?, ?)', [
                            $role->role_id,
                            $permissionId,
                            $userId,
                            (string) Str::uuid(),
                        ]);
                    }
                }

                // Attach menus
                if ($request->has('menus')) {
                    foreach ($request->menus as $menuId) {
                        \DB::select('CALL sp_add_ms_role_menu(?, ?, ?, ?)', [
                            $role->role_id,
                            $menuId,
                            $userId,
                            (string) Str::uuid(),
                        ]);
                    }
                }

                return redirect()->route('roles.index')
                    ->with('success', 'Role Menu created successfully.');
            } elseif ($response->return_code == 404) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', $response->return_message);
            } elseif ($response->return_code == 409) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['email' => $response->return_message]);
            } else {
                return redirect()->back()
                    ->withInput()
                    ->with('error', $response->return_message ?? 'An error occurred while creating role menu.');
            }
        }
    }

    public function edit(Role $role)
    {
        // Double check permission
        if (!auth()->user()->hasPermission('roles.edit')) {
            abort(403, 'Unauthorized action.');
        }
        
        $role->load('permissions', 'menus');
        $permissions = Permission::where('is_active', '1')->get();
        $menus = Menu::where('is_active', '1')
            ->whereNull('parent_id')
            ->with(['children' => function($query) {
                $query->where('is_active', '1');
            }])
            ->orderBy('menu_order')
            ->get();
        $rolePermissions = $role->permissions->pluck('permission_id')->toArray();
        $roleMenus = $role->menus->pluck('menu_id')->toArray();
        
        return view('roles.edit', compact('role', 'permissions', 'menus', 'rolePermissions', 'roleMenus'));
    }

    public function update(RoleRequest $request, Role $role)
    {
        // Double check permission
        if (!auth()->user()->hasPermission('roles.edit')) {
            abort(403, 'Unauthorized action.');
        }
        
        $data = $request->validated();
        $data['updated_by'] = auth()->id();
        $role->update($data);

        // Sync permissions (soft delete approach)
        $requestedPermissions = $request->has('permissions') ? $request->permissions : [];
        
        // Get existing permissions
        $existingPermissions = \DB::table('ms_role_permissions')
            ->where('role_id', $role->role_id)
            ->get();
        
        // Deactivate unchecked permissions
        foreach ($existingPermissions as $existing) {
            if (!in_array($existing->permission_id, $requestedPermissions)) {
                \DB::table('ms_role_permissions')
                    ->where('role_permission_id', $existing->role_permission_id)
                    ->update([
                        'is_active' => '0',
                        'updated_by' => auth()->id(),
                        'updated_date' => now()
                    ]);
            }
        }
        
        // Activate or insert checked permissions
        foreach ($requestedPermissions as $permissionId) {
            $existing = \DB::table('ms_role_permissions')
                ->where('role_id', $role->role_id)
                ->where('permission_id', $permissionId)
                ->first();
            
            if ($existing) {
                // Reactivate if exists
                \DB::table('ms_role_permissions')
                    ->where('role_permission_id', $existing->role_permission_id)
                    ->update([
                        'is_active' => '1',
                        'updated_by' => auth()->id(),
                        'updated_date' => now()
                    ]);
            } else {
                // Insert new
                \DB::table('ms_role_permissions')->insert([
                    'role_id' => $role->role_id,
                    'permission_id' => $permissionId,
                    'unique_id' => (string) Str::uuid(),
                    'created_by' => auth()->id(),
                    'created_date' => now(),
                    'is_active' => '1',
                ]);
            }
        }

        // Sync menus (soft delete approach)
        $requestedMenus = $request->has('menus') ? $request->menus : [];
        
        // Get existing menus
        $existingMenus = \DB::table('ms_role_menus')
            ->where('role_id', $role->role_id)
            ->get();
        
        // Deactivate unchecked menus
        foreach ($existingMenus as $existing) {
            if (!in_array($existing->menu_id, $requestedMenus)) {
                \DB::table('ms_role_menus')
                    ->where('role_menu_id', $existing->role_menu_id)
                    ->update([
                        'is_active' => '0',
                        'updated_by' => auth()->id(),
                        'updated_date' => now()
                    ]);
            }
        }
        
        // Activate or insert checked menus
        foreach ($requestedMenus as $menuId) {
            $existing = \DB::table('ms_role_menus')
                ->where('role_id', $role->role_id)
                ->where('menu_id', $menuId)
                ->first();
            
            if ($existing) {
                // Reactivate if exists
                \DB::table('ms_role_menus')
                    ->where('role_menu_id', $existing->role_menu_id)
                    ->update([
                        'is_active' => '1',
                        'updated_by' => auth()->id(),
                        'updated_date' => now()
                    ]);
            } else {
                // Insert new
                \DB::table('ms_role_menus')->insert([
                    'role_id' => $role->role_id,
                    'menu_id' => $menuId,
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
        // Double check permission
        if (!auth()->user()->hasPermission('roles.delete')) {
            abort(403, 'Unauthorized action.');
        }
        
        $role->update(['is_active' => '0', 'updated_by' => auth()->id()]);
        return redirect()->route('roles.index')->with('success', 'Role deleted successfully.');
    }
}
