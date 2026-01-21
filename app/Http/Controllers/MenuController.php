<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Http\Requests\MenuRequest;
use Illuminate\Support\Str;

class MenuController extends Controller
{
    public function index()
    {
        $menus = Menu::with('parent')->where('is_active', '1')->orderBy('menu_order')->paginate(10);
        return view('menus.index', compact('menus'));
    }

    public function create()
    {
        $parentMenus = Menu::where('is_active', '1')->whereNull('parent_id')->orderBy('menu_order')->get();
        return view('menus.create', compact('parentMenus'));
    }

    public function store(MenuRequest $request)
    {
        $data = $request->validated();
        $data['unique_id'] = (string) Str::uuid();
        $data['created_by'] = auth()->id();

        Menu::create($data);

        return redirect()->route('menus.index')->with('success', 'Menu created successfully.');
    }

    public function edit(Menu $menu)
    {
        $parentMenus = Menu::where('is_active', '1')
            ->whereNull('parent_id')
            ->where('menu_id', '!=', $menu->menu_id)
            ->orderBy('menu_order')
            ->get();
        return view('menus.edit', compact('menu', 'parentMenus'));
    }

    public function update(MenuRequest $request, Menu $menu)
    {
        $data = $request->validated();
        $data['updated_by'] = auth()->id();
        $menu->update($data);

        return redirect()->route('menus.index')->with('success', 'Menu updated successfully.');
    }

    public function destroy(Menu $menu)
    {
        $menu->update(['is_active' => '0', 'updated_by' => auth()->id()]);
        return redirect()->route('menus.index')->with('success', 'Menu deleted successfully.');
    }
}
