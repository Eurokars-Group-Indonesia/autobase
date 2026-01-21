<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $permission = $this->route('permission');
        
        $rules = [
            'permission_name' => 'required|string|max:150',
        ];

        // permission_code validation
        if ($permission) {
            $rules['permission_code'] = [
                'required',
                'string',
                'max:100',
                Rule::unique('ms_permissions', 'permission_code')->ignore($permission->permission_id, 'permission_id')
            ];
        } else {
            $rules['permission_code'] = 'required|string|max:100|unique:ms_permissions,permission_code';
        }

        // is_active only for update
        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $rules['is_active'] = 'nullable|in:0,1';
        }
        
        return $rules;
    }
}
