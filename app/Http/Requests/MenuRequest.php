<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MenuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $menuId = $this->route('menu');
        
        return [
            'menu_code' => 'required|string|max:50|unique:ms_menus,menu_code,' . $menuId . ',menu_id',
            'menu_name' => 'required|string|max:100',
            'menu_url' => 'nullable|string|max:255',
            'menu_icon' => 'nullable|string|max:50',
            'parent_id' => 'nullable|exists:ms_menus,menu_id',
            'menu_order' => 'required|integer|min:0',
            'is_active' => 'required|in:0,1',
        ];
    }
}
