<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Http\Requests\BrandRequest;
use Illuminate\Support\Str;

class BrandController extends Controller
{
    public function index()
    {
        $query = Brand::where('is_active', '1')->orderBy('brand_name');
        
        // Search functionality
        if (request()->has('search') && request('search') != '') {
            $search = request('search');
            $query->where(function($q) use ($search) {
                $q->where('brand_code', 'like', $search . '%')
                  ->orWhere('brand_name', 'like', $search . '%')
                  ->orWhere('brand_group', 'like', $search . '%')
                  ->orWhere('country_origin', 'like', $search . '%');
            });
        }
        
        $brands = $query->paginate(10)->withQueryString();
        return view('brands.index', compact('brands'));
    }

    public function create()
    {
        // Double check permission
        if (!auth()->user()->hasPermission('brands.create')) {
            abort(403, 'Unauthorized action.');
        }
        
        return view('brands.create');
    }

    public function store(BrandRequest $request)
    {
        // Double check permission
        if (!auth()->user()->hasPermission('brands.create')) {
            abort(403, 'Unauthorized action.');
        }
        
        $data = $request->validated();
        $data['unique_id'] = (string) Str::uuid();
        $data['created_by'] = auth()->id();
        $data['is_active'] = '1'; // Default active

        Brand::create($data);

        return redirect()->route('brands.index')->with('success', 'Brand created successfully.');
    }

    public function edit(Brand $brand)
    {
        // Double check permission
        if (!auth()->user()->hasPermission('brands.edit')) {
            abort(403, 'Unauthorized action.');
        }
        
        return view('brands.edit', compact('brand'));
    }

    public function update(BrandRequest $request, Brand $brand)
    {
        // Double check permission
        if (!auth()->user()->hasPermission('brands.edit')) {
            abort(403, 'Unauthorized action.');
        }
        
        $data = $request->validated();
        $data['updated_by'] = auth()->id();
        $brand->update($data);

        return redirect()->route('brands.index')->with('success', 'Brand updated successfully.');
    }

    public function destroy(Brand $brand)
    {
        // Double check permission
        if (!auth()->user()->hasPermission('brands.delete')) {
            abort(403, 'Unauthorized action.');
        }
        
        $brand->update(['is_active' => '0', 'updated_by' => auth()->id()]);
        return redirect()->route('brands.index')->with('success', 'Brand deleted successfully.');
    }
}
