<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LicenseLogicRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        $rules = [
            'name' => 'required|string',
            'slug' => 'required|string|regex:/^[A-Za-z0-9_]+$/',
            'event' => 'required|string',
            'direction' => 'nullable',
            'from_days' => 'nullable',
            'to_days' => 'nullable',
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
        return [
            'slug' => [
                'required',
                'string',
                'regex:/^[A-Za-z0-9_]+$/', // only letters, numbers, underscore
                Rule::unique('license_logics'),
            ],
        ];
    }

    protected function updateRules()
    {
        return [
            'slug' => [
                'required',
                'string',
                'regex:/^[A-Za-z0-9_]+$/', // only letters, numbers, underscore
                Rule::unique('license_logics')
                    ->ignore($this->route('logic'), 'id'),
            ],
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Name is required.',
            'slug.required' => 'Identifier is required.',
            'slug.unique' => 'The identifier already exists.',
            'slug.regex' => 'The identifier may only contain letters, numbers, and underscores (_).',
        ];
    }
}
