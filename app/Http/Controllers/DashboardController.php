<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Brand;
use App\Models\TransactionHeader;
use App\Models\TransactionBody;

class DashboardController extends Controller
{
    public function index()
    {
        // Get user's brand IDs
        $userBrandIds = auth()->user()->getBrandIds();
        
        // Get brand codes for user's brands
        $userBrandCodes = Brand::whereIn('brand_id', $userBrandIds)
            ->pluck('brand_code')
            ->toArray();
        
        $data = [
            'totalUsers' => User::where('is_active', '1')->count(),
            'totalTransactionHeaders' => TransactionHeader::whereIn('brand_code', $userBrandCodes)
                ->where('is_active', '1')
                ->count(),
            'totalTransactionBodies' => TransactionBody::whereIn('brand_code', $userBrandCodes)
                ->where('is_active', '1')
                ->count(),
        ];

        return view('dashboard.index', $data);
    }
}
