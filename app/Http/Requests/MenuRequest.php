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
        $menuId = $this->route('menu') ? $this->route('menu')->menu_id : null;
        
        $rules = [
            'menu_name' => 'required|string|max:100',
            'menu_url' => 'nullable|string|max:255',
            'menu_icon' => 'nullable|string|max:50',
            'parent_id' => 'nullable|exists:ms_menus,menu_id',
            'menu_order' => 'required|integer|min:0',
        ];

        // menu_code validation
        if ($menuId) {
            $rules['menu_code'] = 'required|string|max:50|unique:ms_menus,menu_code,' . $menuId . ',menu_id';
        } else {
            $rules['menu_code'] = 'required|string|max:50|unique:ms_menus,menu_code';
        }

        // is_active only for update
        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $rules['is_active'] = 'nullable|in:0,1';
        }
        
        return $rules;
    }
}
