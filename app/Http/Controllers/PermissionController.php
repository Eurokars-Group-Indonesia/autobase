<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Http\Requests\PermissionRequest;
use Illuminate\Support\Str;

class PermissionController extends Controller
{
    public function index()
    {
        $query = Permission::where('is_active', '1');
        
        // Search functionality
        if (request()->has('search') && request('search') != '') {
            $search = request('search');
            $query->where(function($q) use ($search) {
                $q->where('permission_code', 'like', $search . '%')
                  ->orWhere('permission_name', 'like', $search . '%');
            });
        }
        
        $permissions = $query->paginate(10)->withQueryString();
        return view('permissions.index', compact('permissions'));
    }

    public function create()
    {
        // Double check permission
        if (!auth()->user()->hasPermission('permissions.create')) {
            abort(403, 'Unauthorized action.');
        }
        
        return view('permissions.create');
    }

    public function store(PermissionRequest $request)
    {
        // Double check permission
        if (!auth()->user()->hasPermission('permissions.create')) {
            abort(403, 'Unauthorized action.');
        }
        
        $data = $request->validated();
        $data['unique_id'] = (string) Str::uuid();
        $data['created_by'] = auth()->id();
        $data['is_active'] = '1'; // Default active

        Permission::create($data);

        return redirect()->route('permissions.index')->with('success', 'Permission created successfully.');
    }

    public function edit(Permission $permission)
    {
        // Double check permission
        if (!auth()->user()->hasPermission('permissions.edit')) {
            abort(403, 'Unauthorized action.');
        }
        
        return view('permissions.edit', compact('permission'));
    }

    public function update(PermissionRequest $request, Permission $permission)
    {
        // Double check permission
        if (!auth()->user()->hasPermission('permissions.edit')) {
            abort(403, 'Unauthorized action.');
        }
        
        $data = $request->validated();
        $data['updated_by'] = auth()->id();
        $permission->update($data);

        return redirect()->route('permissions.index')->with('success', 'Permission updated successfully.');
    }

    public function destroy(Permission $permission)
    {
        // Double check permission
        if (!auth()->user()->hasPermission('permissions.delete')) {
            abort(403, 'Unauthorized action.');
        }
        
        $permission->update(['is_active' => '0', 'updated_by' => auth()->id()]);
        return redirect()->route('permissions.index')->with('success', 'Permission deleted successfully.');
    }
}
