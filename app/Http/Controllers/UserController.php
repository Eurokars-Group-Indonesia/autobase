<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Http\Requests\UserRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function index()
    {
        $query = User::with(['roles', 'brand', 'dealer'])->where('is_active', '1');
        
        // Search functionality
        if (request()->has('search') && request('search') != '') {
            $search = request('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', $search . '%')
                  ->orWhere('email', 'like', $search . '%')
                  ->orWhere('full_name', 'like', $search . '%')
                  ->orWhere('phone', 'like', $search . '%');
            });
        }
        
        $users = $query->paginate(10)->withQueryString();
        return view('users.index', compact('users'));
    }

    public function create()
    {
        // Double check permission
        if (!auth()->user()->hasPermission('users.create')) {
            abort(403, 'Unauthorized action.');
        }
        
        $roles = Role::where('is_active', '1')->get();
        $brands = \App\Models\Brand::where('is_active', '1')->orderBy('brand_name')->get();
        $dealers = \App\Models\Dealer::where('is_active', '1')->orderBy('dealer_name')->get();
        return view('users.create', compact('roles', 'brands', 'dealers'));
    }

    public function store(UserRequest $request)
    {
        // Double check permission
        if (!auth()->user()->hasPermission('users.create')) {
            abort(403, 'Unauthorized action.');
        }
        
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);
        $data['unique_id'] = (string) Str::uuid();
        $data['created_by'] = auth()->id();
        $data['is_active'] = '1'; // Default active

        $user = User::create($data);

        if ($request->has('roles')) {
            foreach ($request->roles as $roleId) {
                $user->roles()->attach($roleId, [
                    'unique_id' => (string) Str::uuid(),
                    'assigned_date' => now(),
                    'created_by' => auth()->id(),
                    'created_date' => now(),
                    'is_active' => '1',
                ]);
            }
        }

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        // Double check permission
        if (!auth()->user()->hasPermission('users.edit')) {
            abort(403, 'Unauthorized action.');
        }
        
        $user->load('roles');
        $roles = Role::where('is_active', '1')->get();
        $brands = \App\Models\Brand::where('is_active', '1')->orderBy('brand_name')->get();
        $dealers = \App\Models\Dealer::where('is_active', '1')->orderBy('dealer_name')->get();
        $userRoles = $user->roles->pluck('role_id')->toArray();
        return view('users.edit', compact('user', 'roles', 'brands', 'dealers', 'userRoles'));
    }

    public function update(UserRequest $request, User $user)
    {
        // Double check permission
        if (!auth()->user()->hasPermission('users.edit')) {
            abort(403, 'Unauthorized action.');
        }
        
        $data = $request->validated();
        
        if ($request->filled('password')) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }
        
        $data['updated_by'] = auth()->id();
        $user->update($data);

        // Sync roles
        $user->roles()->detach();
        if ($request->has('roles')) {
            foreach ($request->roles as $roleId) {
                $user->roles()->attach($roleId, [
                    'unique_id' => (string) Str::uuid(),
                    'assigned_date' => now(),
                    'created_by' => auth()->id(),
                    'created_date' => now(),
                    'is_active' => '1',
                ]);
            }
        }

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        // Double check permission
        if (!auth()->user()->hasPermission('users.delete')) {
            abort(403, 'Unauthorized action.');
        }
        
        // Check if user has Admin role
        if ($user->hasRole('ADMIN')) {
            return redirect()->route('users.index')->with('error', 'Cannot delete user with Admin role.');
        }
        
        // Prevent deleting yourself
        if ($user->user_id === auth()->id()) {
            return redirect()->route('users.index')->with('error', 'You cannot delete your own account.');
        }
        
        $user->update(['is_active' => '0', 'updated_by' => auth()->id()]);
        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }
}
