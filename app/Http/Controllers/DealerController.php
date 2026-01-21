<?php

namespace App\Http\Controllers;

use App\Models\Dealer;
use App\Http\Requests\DealerRequest;
use Illuminate\Support\Str;

class DealerController extends Controller
{
    public function index()
    {
        $query = Dealer::where('is_active', '1')->orderBy('dealer_name');
        
        // Search functionality
        if (request()->has('search') && request('search') != '') {
            $search = request('search');
            $query->where(function($q) use ($search) {
                $q->where('dealer_code', 'like', $search . '%')
                  ->orWhere('dealer_name', 'like', $search . '%')
                  ->orWhere('city', 'like', $search . '%');
            });
        }
        
        $dealers = $query->paginate(10)->withQueryString();
        return view('dealers.index', compact('dealers'));
    }

    public function create()
    {
        // Double check permission
        if (!auth()->user()->hasPermission('dealers.create')) {
            abort(403, 'Unauthorized action.');
        }
        
        return view('dealers.create');
    }

    public function store(DealerRequest $request)
    {
        // Double check permission
        if (!auth()->user()->hasPermission('dealers.create')) {
            abort(403, 'Unauthorized action.');
        }
        
        $data = $request->validated();
        $data['unique_id'] = (string) Str::uuid();
        $data['created_by'] = auth()->id();
        $data['is_active'] = '1'; // Default active

        Dealer::create($data);

        return redirect()->route('dealers.index')->with('success', 'Dealer created successfully.');
    }

    public function edit(Dealer $dealer)
    {
        // Double check permission
        if (!auth()->user()->hasPermission('dealers.edit')) {
            abort(403, 'Unauthorized action.');
        }
        
        return view('dealers.edit', compact('dealer'));
    }

    public function update(DealerRequest $request, Dealer $dealer)
    {
        // Double check permission
        if (!auth()->user()->hasPermission('dealers.edit')) {
            abort(403, 'Unauthorized action.');
        }
        
        $data = $request->validated();
        $data['updated_by'] = auth()->id();
        $dealer->update($data);

        return redirect()->route('dealers.index')->with('success', 'Dealer updated successfully.');
    }

    public function destroy(Dealer $dealer)
    {
        // Double check permission
        if (!auth()->user()->hasPermission('dealers.delete')) {
            abort(403, 'Unauthorized action.');
        }
        
        $dealer->update(['is_active' => '0', 'updated_by' => auth()->id()]);
        return redirect()->route('dealers.index')->with('success', 'Dealer deleted successfully.');
    }
}
