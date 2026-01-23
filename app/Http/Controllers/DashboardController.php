<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\TransactionHeader;
use App\Models\TransactionBody;

class DashboardController extends Controller
{
    public function index()
    {
        $data = [
            'totalUsers' => User::where('is_active', '1')->count(),
            'totalTransactionHeaders' => TransactionHeader::count(),
            'totalTransactionBodies' => TransactionBody::count(),
        ];

        return view('dashboard.index', $data);
    }
}
