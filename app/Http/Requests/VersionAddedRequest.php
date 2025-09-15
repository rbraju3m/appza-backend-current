<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VersionAddedRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        $rules = [
            'version'         => 'required|string',
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
            // Only allow ZIP files (max 10 MB, adjust if needed)
            'addon_file' => 'required|file|mimes:zip|max:10240',
        ];
    }

    protected function updateRules()
    {
        return [
            'addon_file' => 'nullable|file|mimes:zip|max:10240',
        ];
    }

    public function messages()
    {
        return [
            'addon_file.required' => 'Addon file is required.',
            'addon_file.mimes'    => 'Only ZIP files are allowed.',
            'addon_file.max'      => 'The addon file may not be greater than 10 MB.',
            'slug.required'       => 'Plugin slug is required.',
            'slug.unique'         => 'The slug already exists.',
        ];
    }
}
