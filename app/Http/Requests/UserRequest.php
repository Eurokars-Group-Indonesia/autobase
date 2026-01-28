<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Rules\StrongPassword;

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
            'phone' => 'nullable|string|max:20|regex:/^[0-9+]+$/',
            'dealer_id' => 'nullable|exists:ms_dealers,dealer_id',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:ms_role,role_id',
            'brands' => 'nullable|array',
            'brands.*' => 'exists:ms_brand,brand_id',
        ];

        // Email validation - using unique_id for database check
        if ($user) {
            $rules['email'] = [
                'required',
                'email',
                'max:150',
                Rule::unique('ms_users', 'email')
                    ->ignore($user->user_id, 'user_id')
                    ->where(function ($query) {
                        return $query->where('is_active', '1');
                    })
            ];
        } else {
            $rules['email'] = [
                'required',
                'email',
                'max:150',
                Rule::unique('ms_users', 'email')
                    ->where(function ($query) {
                        return $query->where('is_active', '1');
                    })
            ];
        }

        // Password validation with strong password rules
        if ($this->isMethod('post')) {
            $rules['password'] = [
                'required',
                'string',
                'min:8',
                'max:255',
                'confirmed',
                new StrongPassword($this->input('full_name'))
            ];
        } else {
            // For update, only validate if password is provided
            if ($this->filled('password')) {
                $rules['password'] = [
                    'nullable',
                    'string',
                    'min:8',
                    'max:255',
                    'confirmed',
                    new StrongPassword($this->input('full_name'))
                ];
            } else {
                $rules['password'] = 'nullable|string|min:8|max:255|confirmed';
            }
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Email is required.',
            'email.email' => 'Invalid email format.',
            'email.unique' => 'Email already registered. Please use another email.',
            'email.max' => 'Email maximum 150 characters.',
            'phone.regex' => 'Phone can only contain numbers and + symbol.',
            'phone.max' => 'Phone maximum 20 characters.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
        ];
    }
}
