<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('user');
        $rules = [
            'name' => 'required|string|max:150',
            'email' => 'nullable|email|max:150|unique:ms_users,email,' . $userId . ',user_id',
            'full_name' => 'required|string|max:150',
            'phone' => 'nullable|string|max:20',
            'is_active' => 'required|in:0,1',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:ms_role,role_id',
        ];

        if ($this->isMethod('post')) {
            $rules['password'] = 'required|string|min:8|confirmed';
        } else {
            $rules['password'] = 'nullable|string|min:8|confirmed';
        }

        return $rules;
    }
}
