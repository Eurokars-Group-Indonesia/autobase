<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DealerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $dealer = $this->route('dealer');
        
        $rules = [
            'dealer_name' => 'required|string|max:150',
            'city' => 'nullable|string|max:100',
        ];

        // dealer_code validation
        if ($dealer) {
            $rules['dealer_code'] = [
                'required',
                'string',
                'max:50',
                Rule::unique('ms_dealers', 'dealer_code')->ignore($dealer->dealer_id, 'dealer_id')
            ];
        } else {
            $rules['dealer_code'] = 'required|string|max:50|unique:ms_dealers,dealer_code';
        }

        // is_active only for update
        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $rules['is_active'] = 'nullable|in:0,1';
        }
        
        return $rules;
    }
}
