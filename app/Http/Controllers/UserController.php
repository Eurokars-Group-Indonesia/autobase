<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Http\Requests\UserRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function index()
    {
        $query = User::with(['roles', 'brands', 'dealer'])->where('is_active', '1');
        
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
        
        try {
            $data = $request->validated();
            $uniqueId = (string) Str::uuid();
            $userId = auth()->id();
            $plainPassword = $data['password']; // Store plain password for email
            $hashedPassword = Hash::make($data['password']);

            // Call stored procedure sp_add_ms_user
            $result = \DB::select('CALL sp_add_ms_user(?, ?, ?, ?, ?, ?, ?, ?)', [
                $userId,
                $data['dealer_id'] ?? null,
                $data['name'],
                $data['email'],
                $data['full_name'],
                $hashedPassword,
                $data['phone'] ?? null,
                $uniqueId
            ]);

            // Check result from stored procedure
            if (empty($result)) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Failed to create user. No response from database.');
            }

            $response = $result[0];

            // Handle response based on return_code
            if ($response->return_code == 200) {
                // Get the created user by unique_id
                $user = User::where('unique_id', $uniqueId)->first();

                if ($user) {
                    // Attach roles
                    if ($request->has('roles')) {
                        foreach ($request->roles as $roleId) {
                            \DB::select('CALL sp_add_ms_user_role(?, ?, ?, ?)', [
                                $user->user_id,
                                $roleId,
                                $userId,
                                (string) Str::uuid(),
                            ]);
                        }
                    }

                    // Attach brands
                    if ($request->has('brands')) {
                        foreach ($request->brands as $brandId) {
                            \DB::select('CALL sp_add_ms_user_brand(?, ?, ?, ?)', [
                                $user->user_id,
                                $brandId,
                                $userId,
                                (string) Str::uuid(),
                            ]);
                        }
                    }

                    // Send welcome email
                    try {
                        \Mail::to($user->email)->send(new \App\Mail\WelcomeUserMail($user, $plainPassword));
                        \Log::info("Welcome email sent to user: {$user->email}");
                    } catch (\Exception $e) {
                        \Log::error("Failed to send welcome email to {$user->email}: " . $e->getMessage());
                        // Don't fail the user creation if email fails
                    }
                }

                // Flush cache
                Cache::flush();

                return redirect()->route('users.index')->with('success', 'User created successfully. Welcome email has been sent.');
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
                    ->with('error', $response->return_message ?? 'An error occurred while creating user.');
            }
        } catch (\Illuminate\Database\QueryException $e) {
            \Log::error('Database error in UserController@store: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Database error: ' . $e->getMessage());
        } catch (\Exception $e) {
            \Log::error('Error in UserController@store: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'An unexpected error occurred. Please try again.');
        }
    }

    public function edit(User $user)
    {
        // Double check permission
        if (!auth()->user()->hasPermission('users.edit')) {
            abort(403, 'Unauthorized action.');
        }
        
        $user->load(['roles', 'brands']);
        $roles = Role::where('is_active', '1')->get();
        $brands = \App\Models\Brand::where('is_active', '1')->orderBy('brand_name')->get();
        $dealers = \App\Models\Dealer::where('is_active', '1')->orderBy('dealer_name')->get();
        $userRoles = $user->roles->pluck('role_id')->toArray();
        $userBrands = $user->brands->pluck('brand_id')->toArray();
        return view('users.edit', compact('user', 'roles', 'brands', 'dealers', 'userRoles', 'userBrands'));
    }

    public function update(UserRequest $request, User $user)
    {
        // Double check permission
        if (!auth()->user()->hasPermission('users.edit')) {
            abort(403, 'Unauthorized action.');
        }
        
        try {
            $data = $request->validated();
            $userId = auth()->id();
            
            // Prepare password if provided
            if ($request->filled('password')) {
                $hashedPassword = Hash::make($data['password']);
            } else {
                $hashedPassword = ''; // Keep existing password
            }

            // Call stored procedure sp_update_ms_user
            $result = \DB::select('CALL sp_update_ms_user(?, ?, ?, ?, ?, ?, ?, ?)', [
                $userId,
                $data['dealer_id'] ?? null,
                $data['name'],
                $data['email'],
                $data['full_name'],
                $hashedPassword,
                $data['phone'] ?? null,
                $user->unique_id
            ]);

            // Check result from stored procedure
            if (empty($result)) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Failed to update user. No response from database.');
            }

            $response = $result[0];

            // Handle response based on return_code
            if ($response->return_code == 200) {
                // Soft delete existing roles (set is_active = '0')
                \DB::table('ms_user_roles')
                    ->where('user_id', $user->user_id)
                    ->where('is_active', '1')
                    ->update([
                        'is_active' => '0',
                        'updated_by' => $userId,
                        'updated_date' => now()
                    ]);

                // Add new roles
                if ($request->has('roles')) {
                    foreach ($request->roles as $roleId) {
                        try {
                            $roleResult = \DB::select('CALL sp_add_ms_user_role(?, ?, ?, ?)', [
                                $user->user_id,
                                $roleId,
                                $userId,
                                (string) Str::uuid(),
                            ]);
                            
                            // Check if role assignment failed (duplicate will return 409, which is ok to ignore)
                            if (!empty($roleResult) && $roleResult[0]->return_code != 200 && $roleResult[0]->return_code != 409) {
                                \Log::warning("Failed to assign role {$roleId} to user {$user->user_id}: " . $roleResult[0]->return_message);
                            }
                        } catch (\Exception $e) {
                            \Log::error("Error assigning role {$roleId} to user {$user->user_id}: " . $e->getMessage());
                        }
                    }
                }

                // Soft delete existing brands (set is_active = '0')
                \DB::table('ms_user_brand')
                    ->where('user_id', $user->user_id)
                    ->where('is_active', '1')
                    ->update([
                        'is_active' => '0',
                        'updated_by' => $userId,
                        'updated_date' => now()
                    ]);

                // Add new brands
                if ($request->has('brands')) {
                    foreach ($request->brands as $brandId) {
                        try {
                            $brandResult = \DB::select('CALL sp_add_ms_user_brand(?, ?, ?, ?)', [
                                $user->user_id,
                                $brandId,
                                $userId,
                                (string) Str::uuid(),
                            ]);
                            
                            // Check if brand assignment failed (duplicate will return 409, which is ok to ignore)
                            if (!empty($brandResult) && $brandResult[0]->return_code != 200 && $brandResult[0]->return_code != 409) {
                                \Log::warning("Failed to assign brand {$brandId} to user {$user->user_id}: " . $brandResult[0]->return_message);
                            }
                        } catch (\Exception $e) {
                            \Log::error("Error assigning brand {$brandId} to user {$user->user_id}: " . $e->getMessage());
                        }
                    }
                }

                // Flush cache
                Cache::flush();

                return redirect()->route('users.index')->with('success', 'User updated successfully.');
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
                    ->with('error', $response->return_message ?? 'An error occurred while updating user.');
            }
        } catch (\Illuminate\Database\QueryException $e) {
            \Log::error('Database error in UserController@update: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Database error: ' . $e->getMessage());
        } catch (\Exception $e) {
            \Log::error('Error in UserController@update: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'An unexpected error occurred. Please try again.');
        }
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
        
        // Flush cache
        Cache::flush();
        
        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }
}
