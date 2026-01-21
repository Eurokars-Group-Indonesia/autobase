<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $user = $this->route('user');
        
        $rules = [
            'name' => 'required|string|max:150',
            'full_name' => 'required|string|max:150',
            'phone' => 'nullable|string|max:20',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:ms_role,role_id',
        ];

        // is_active only for update
        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $rules['is_active'] = 'nullable|in:0,1';
        }

        // Email validation - using unique_id for database check
        if ($user) {
            $rules['email'] = [
                'required',
                'email',
                'max:150',
                Rule::unique('ms_users', 'email')->ignore($user->user_id, 'user_id')
            ];
        } else {
            $rules['email'] = 'required|email|max:150|unique:ms_users,email';
        }

        // Password validation
        if ($this->isMethod('post')) {
            $rules['password'] = 'required|string|min:8|max:255|confirmed';
        } else {
            $rules['password'] = 'nullable|string|min:8|max:255|confirmed';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar. Silakan gunakan email lain.',
            'email.max' => 'Email maksimal 150 karakter.',
        ];
    }
}
