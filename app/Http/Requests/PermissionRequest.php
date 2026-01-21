<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $permissionId = $this->route('permission');
        
        return [
            'permission_code' => 'required|string|max:100|unique:ms_permissions,permission_code,' . $permissionId . ',permission_id',
            'permission_name' => 'required|string|max:150',
            'is_active' => 'required|in:0,1',
        ];
    }
}
