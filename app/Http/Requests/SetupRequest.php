<?php

namespace App\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SetupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        $rules = [
            'value' => 'required|string',
            'key' => ['required', 'regex:/^[a-zA-Z0-9_]+$/u'],
            'is_active' => 'nullable|boolean',
        ];

        if ($this->isMethod('POST')) {
            $rules = array_merge($rules, $this->storeRules());
        }

        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules = array_merge($rules, $this->updateRules());
        }

        return $rules;
    }

    protected function storeRules()
    {
        return [];
    }

    protected function updateRules()
    {
        return [];
    }

    public function messages()
    {
        return [
            'name.required' => 'Value is required.',
            'key.required' => 'Key name is required.',
        ];
    }
}

