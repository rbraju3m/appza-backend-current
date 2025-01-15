<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class StyleGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        $rules = [
            'plugin_slug' => 'required|array',
            'name' => 'required|string',
            'slug' => 'required|string',
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
                Rule::unique('appfiy_style_group')->where(function ($query) {
                    return $query->where('slug', $this->slug);
                }),
            ]
        ];
    }

    protected function updateRules()
    {
        return [
            'slug' => [
                'required',
                'string',
                Rule::unique('appfiy_style_group')
                    ->where(function ($query) {
                        return $query->where('slug', $this->slug);
                    })
                    ->ignore($this->route('id'), 'id'),
            ],
        ];
    }

    public function messages()
    {
        return [
            'plugin_slug.required' => 'Plugin is required.',
            'plugin_slug.array' => 'Plugin must be an array.',
            'name.required' => 'Style name is required.',
            'slug.required' => 'Style slug is required.',
            'slug.unique' => 'The style slug already exists.',
        ];
    }
}

