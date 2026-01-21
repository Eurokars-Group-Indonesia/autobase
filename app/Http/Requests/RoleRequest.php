<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $roleId = $this->route('role') ? $this->route('role')->role_id : null;
        
        $rules = [
            'role_name' => 'required|string|max:50',
            'role_description' => 'required|string|max:200',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:ms_permissions,permission_id',
            'menus' => 'nullable|array',
            'menus.*' => 'exists:ms_menus,menu_id',
        ];

        // role_code validation
        if ($roleId) {
            $rules['role_code'] = 'required|string|max:10|unique:ms_role,role_code,' . $roleId . ',role_id';
        } else {
            $rules['role_code'] = 'required|string|max:10|unique:ms_role,role_code';
        }

        // is_active only for update
        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $rules['is_active'] = 'nullable|in:0,1';
        }
        
        return $rules;
    }
}
