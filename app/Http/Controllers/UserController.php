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
        $users = User::with('roles')->where('is_active', '1')->paginate(10);
        return view('users.index', compact('users'));
    }

    public function create()
    {
        $roles = Role::where('is_active', '1')->get();
        return view('users.create', compact('roles'));
    }

    public function store(UserRequest $request)
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);
        $data['unique_id'] = (string) Str::uuid();
        $data['created_by'] = auth()->id();

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
        $roles = Role::where('is_active', '1')->get();
        $userRoles = $user->roles->pluck('role_id')->toArray();
        return view('users.edit', compact('user', 'roles', 'userRoles'));
    }

    public function update(UserRequest $request, User $user)
    {
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
        $user->update(['is_active' => '0', 'updated_by' => auth()->id()]);
        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }
}
