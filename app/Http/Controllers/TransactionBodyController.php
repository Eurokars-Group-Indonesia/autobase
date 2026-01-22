<?php

namespace App\Http\Controllers;

use App\Models\TransactionBody;
use Illuminate\Http\Request;

class TransactionBodyController extends Controller
{
    public function index(Request $request)
    {
        // Generate cache key based on user and search parameters
        $userId = auth()->id();
        $search = $request->get('search', '');
        $dateFrom = $request->get('date_from', '');
        $dateTo = $request->get('date_to', '');
        $perPage = $request->get('per_page', 10);
        $page = $request->get('page', 1);
        
        $cacheKey = "body:{$userId}:{$search}:{$dateFrom}:{$dateTo}:{$perPage}:{$page}";
        
        // Try to get from cache (1 hour), if not found execute query and cache it
        $transactions = cache()->remember($cacheKey, now()->addHour(), function () use ($request, $perPage) {
            $query = TransactionBody::where('is_active', '1')->orderBy('created_date', 'desc');
            
            // Search by text (part_no, invoice_no, wip_no, description)
            // Using 'search%' pattern to utilize B-tree index efficiently
            if ($request->has('search') && $request->search != '') {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('part_no', 'like', $search . '%')
                      ->orWhere('invoice_no', 'like', $search . '%')
                      ->orWhere('wip_no', 'like', $search . '%')
                      ->orWhere('description', 'like', $search . '%');
                });
            }
            
            // Filter by date range (date_decard)
            if ($request->has('date_from') && $request->date_from != '') {
                $query->whereDate('date_decard', '>=', $request->date_from);
            }
            
            if ($request->has('date_to') && $request->date_to != '') {
                $query->whereDate('date_decard', '<=', $request->date_to);
            }
            
            // Pagination with per_page option
            $perPageValue = in_array($perPage, [10, 25, 50, 100]) ? $perPage : 10;
            
            return $query->paginate($perPageValue)->withQueryString();
        });
        
        return view('transaction-body.index', compact('transactions'));
    }
}
