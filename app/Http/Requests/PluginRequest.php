<?php

namespace App\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PluginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        $rules = [
            'name' => 'required|string',
            'prefix' => 'required|string',
            'title' => 'nullable|string',
            'description' => 'nullable|string',
            'others' => 'nullable|string',
            'status' => 'boolean',
            'is_disable' => 'boolean',
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
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'slug' => [
                'required',
                'string',
                Rule::unique('appza_supports_plugin')->where(function ($query) {
                    return $query->where('slug', $this->slug);
                }),
            ]
        ];
    }

    protected function updateRules()
    {
        return [
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'slug' => [
                'required',
                'string',
                Rule::unique('appza_supports_plugin')
                    ->where(function ($query) {
                        return $query->where('slug', $this->slug);
                    })
                    ->ignore($this->route('plugin'), 'id'), // Ensure 'id' is the primary key
            ],
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Plugin name is required.',
            'prefix.required' => 'Plugin prefix is required.',
            'slug.required' => 'Plugin slug is required.',
            'slug.unique' => 'The slug already exists.',
        ];
    }
}

