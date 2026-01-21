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
        $roleId = $this->route('role');
        
        return [
            'role_code' => 'required|string|max:10|unique:ms_role,role_code,' . $roleId . ',role_id',
            'role_name' => 'required|string|max:50',
            'role_description' => 'required|string|max:200',
            'is_active' => 'required|in:0,1',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:ms_permissions,permission_id',
            'menus' => 'nullable|array',
            'menus.*' => 'exists:ms_menus,menu_id',
        ];
    }
}
