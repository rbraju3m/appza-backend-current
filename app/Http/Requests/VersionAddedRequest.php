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
            'addon_file' => [
                'required',
                'file',
                'mimes:zip',
                'max:10240',
                function ($attribute, $value, $fail) {
                    $filename = pathinfo($value->getClientOriginalName(), PATHINFO_FILENAME);
                    // Allow only a-z, A-Z, 0-9, dash, dot
                    if (!preg_match('/^[a-zA-Z0-9\.\-]+$/', $filename)) {
                        $fail("The $attribute may only contain letters, numbers, dots, and dashes (no spaces or special characters).");
                    }
                }
            ],
        ];
    }

    protected function updateRules()
    {
        return [
            'addon_file' => [
                'nullable',
                'file',
                'mimes:zip',
                'max:10240',
                function ($attribute, $value, $fail) {
                    $filename = pathinfo($value->getClientOriginalName(), PATHINFO_FILENAME);
                    // Allow only a-z, A-Z, 0-9, dash, dot
                    if (!preg_match('/^[a-zA-Z0-9\.\-]+$/', $filename)) {
                        $fail("The $attribute may only contain letters, numbers, dots, and dashes (no spaces or special characters).");
                    }
                }
            ],
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
