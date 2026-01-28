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
        
        // Get selected year from request, default to current year
        $selectedYear = request()->get('year', now()->year);
        
        $data = [
            'totalUsers' => User::where('is_active', '1')->count(),
            'totalTransactionHeaders' => TransactionHeader::whereIn('brand_code', $userBrandCodes)
                ->where('is_active', '1')
                ->count(),
            'totalTransactionBodies' => TransactionBody::whereIn('brand_code', $userBrandCodes)
                ->where('is_active', '1')
                ->count(),
            'selectedYear' => $selectedYear,
        ];

        // Get transaction header data by invoice_date for selected year
        $headerData = TransactionHeader::whereIn('brand_code', $userBrandCodes)
            ->where('is_active', '1')
            ->whereNotNull('invoice_date')
            ->whereYear('invoice_date', $selectedYear)
            ->selectRaw('MONTH(invoice_date) as month, COUNT(*) as count')
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get();

        // Get transaction body data by date_decard for selected year
        $bodyData = TransactionBody::whereIn('brand_code', $userBrandCodes)
            ->where('is_active', '1')
            ->whereNotNull('date_decard')
            ->whereYear('date_decard', $selectedYear)
            ->selectRaw('MONTH(date_decard) as month, COUNT(*) as count')
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get();

        // Prepare chart data
        $data['chartLabels'] = [];
        $data['chartHeaderData'] = [];
        $data['chartBodyData'] = [];

        // Generate all 12 months
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        
        for ($i = 1; $i <= 12; $i++) {
            $data['chartLabels'][] = $months[$i - 1];
            
            // Find header count for this month
            $headerCount = $headerData->firstWhere('month', $i);
            $data['chartHeaderData'][] = $headerCount ? $headerCount->count : 0;
            
            // Find body count for this month
            $bodyCount = $bodyData->firstWhere('month', $i);
            $data['chartBodyData'][] = $bodyCount ? $bodyCount->count : 0;
        }

        return view('dashboard.index', $data);
    }

    public function getChartData()
    {
        // Get user's brand IDs
        $userBrandIds = auth()->user()->getBrandIds();
        
        // Get brand codes for user's brands
        $userBrandCodes = Brand::whereIn('brand_id', $userBrandIds)
            ->pluck('brand_code')
            ->toArray();
        
        // Get selected year from request
        $selectedYear = request()->get('year', now()->year);

        // Get transaction header data by invoice_date for selected year
        $headerData = TransactionHeader::whereIn('brand_code', $userBrandCodes)
            ->where('is_active', '1')
            ->whereNotNull('invoice_date')
            ->whereYear('invoice_date', $selectedYear)
            ->selectRaw('MONTH(invoice_date) as month, COUNT(*) as count')
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get();

        // Get transaction body data by date_decard for selected year
        $bodyData = TransactionBody::whereIn('brand_code', $userBrandCodes)
            ->where('is_active', '1')
            ->whereNotNull('date_decard')
            ->whereYear('date_decard', $selectedYear)
            ->selectRaw('MONTH(date_decard) as month, COUNT(*) as count')
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get();

        // Prepare chart data
        $chartLabels = [];
        $chartHeaderData = [];
        $chartBodyData = [];

        // Generate all 12 months
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        
        for ($i = 1; $i <= 12; $i++) {
            $chartLabels[] = $months[$i - 1];
            
            // Find header count for this month
            $headerCount = $headerData->firstWhere('month', $i);
            $chartHeaderData[] = $headerCount ? $headerCount->count : 0;
            
            // Find body count for this month
            $bodyCount = $bodyData->firstWhere('month', $i);
            $chartBodyData[] = $bodyCount ? $bodyCount->count : 0;
        }

        return response()->json([
            'labels' => $chartLabels,
            'headerData' => $chartHeaderData,
            'bodyData' => $chartBodyData,
            'year' => $selectedYear
        ]);
    }
}
