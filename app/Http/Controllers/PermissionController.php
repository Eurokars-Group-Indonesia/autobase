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
        return view('permissions.create');
    }

    public function store(PermissionRequest $request)
    {
        $data = $request->validated();
        $data['unique_id'] = (string) Str::uuid();
        $data['created_by'] = auth()->id();

        Permission::create($data);

        return redirect()->route('permissions.index')->with('success', 'Permission created successfully.');
    }

    public function edit(Permission $permission)
    {
        return view('permissions.edit', compact('permission'));
    }

    public function update(PermissionRequest $request, Permission $permission)
    {
        $data = $request->validated();
        $data['updated_by'] = auth()->id();
        $permission->update($data);

        return redirect()->route('permissions.index')->with('success', 'Permission updated successfully.');
    }

    public function destroy(Permission $permission)
    {
        $permission->update(['is_active' => '0', 'updated_by' => auth()->id()]);
        return redirect()->route('permissions.index')->with('success', 'Permission deleted successfully.');
    }
}
