<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Menu;

class DashboardController extends Controller
{
    public function index()
    {
        $data = [
            'totalUsers' => User::where('is_active', '1')->count(),
            'totalRoles' => Role::where('is_active', '1')->count(),
            'totalPermissions' => Permission::where('is_active', '1')->count(),
            'totalMenus' => Menu::where('is_active', '1')->count(),
        ];

        return view('dashboard.index', $data);
    }
}
