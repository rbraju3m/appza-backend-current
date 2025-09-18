<?php
/*
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        $rules = [
            'product_id'      => 'required|string',
            'addon_name'      => 'required|string',
            'addon_slug'      => 'required|string',
            'version'         => 'required|string',
            'addon_json_info' => 'required|json',
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

            'addon_slug' => [
                'required',
                'string',
                Rule::unique('appza_product_addons')->where(function ($query) {
                    return $query->where('addon_slug', $this->addon_slug)->where('product_id', $this->product_id);
                }),
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

            'addon_slug' => [
                'required',
                'string',
                Rule::unique('appza_product_addons')
                    ->where(function ($query) {
                        return $query->where('addon_slug', $this->addon_slug);
                    })
                    ->ignore($this->route('plugin'), 'id'),
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
}*/


namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        $rules = [
            'product_id' => 'required|string',
            'addon_name' => 'required|string',
            'addon_slug' => 'required|string',
            'version' => 'required|string',
            'addon_json_info' => 'required|json',
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
            'addon_file' => [
                'required',
                'file',
                'mimes:zip',
                'max:10240',
                function ($attribute, $value, $fail) {
                    $filename = pathinfo($value->getClientOriginalName(), PATHINFO_FILENAME);

                    // Must match "plugin-slug-x.y.z"
                    if (!preg_match('/^[a-z0-9\-]+-\d+\.\d+\.\d+$/', $filename)) {
                        $fail("The $attribute name must follow the pattern: plugin-slug-x.y.z.zip (e.g. my-awesome-plugin-1.2.0.zip).");
                        return;
                    }

                    // Extract version part from filename
                    preg_match('/-(\d+\.\d+\.\d+)$/', $filename, $matches);
                    $fileVersion = $matches[1] ?? null;

                    if ($fileVersion !== $this->version) {
                        $fail("The version in the file name ($fileVersion) must match the version field ({$this->version}).");
                    }
                }
            ],

            'addon_slug' => [
                'required',
                'string',
                Rule::unique('appza_product_addons')->where(function ($query) {
                    return $query->where('addon_slug', $this->addon_slug)
                        ->where('product_id', $this->product_id);
                }),
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

                    if (!preg_match('/^[a-z0-9\-]+-\d+\.\d+\.\d+$/', $filename)) {
                        $fail("The $attribute name must follow the pattern: plugin-slug-x.y.z.zip (e.g. my-awesome-plugin-1.2.0.zip).");
                        return;
                    }

                    preg_match('/-(\d+\.\d+\.\d+)$/', $filename, $matches);
                    $fileVersion = $matches[1] ?? null;

                    if ($fileVersion !== $this->version) {
                        $fail("The version in the file name ($fileVersion) must match the version field ({$this->version}).");
                    }
                }
            ],

            'addon_slug' => [
                'required',
                'string',
                Rule::unique('appza_product_addons')
                    ->where(function ($query) {
                        return $query->where('addon_slug', $this->addon_slug);
                    })
                    ->ignore($this->route('plugin'), 'id'),
            ],
        ];
    }

    public function messages()
    {
        return [
            'addon_file.required' => 'Addon file is required.',
            'addon_file.mimes' => 'Only ZIP files are allowed.',
            'addon_file.max' => 'The addon file may not be greater than 10 MB.',
            'addon_slug.required' => 'Plugin slug is required.',
            'addon_slug.unique' => 'The slug already exists.',
        ];
    }
}
