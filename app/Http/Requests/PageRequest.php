<?php

namespace App\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        $rules = [
            'plugin_slug' => 'required|string',
            'name' => 'required|string',
            'background_color' => 'nullable|string',
            'border_color' => 'nullable|string',
            'border_radius' => 'nullable',
            'component_limit' => 'nullable',
            'persistent_footer_buttons' => 'nullable',
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
                Rule::unique('appfiy_page')->where(function ($query) {
                    return $query->where('plugin_slug', $this->plugin_slug);
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
                Rule::unique('appfiy_page')
                    ->where(function ($query) {
                        return $query->where('plugin_slug', $this->plugin_slug);
                    })
                    ->ignore($this->route('page'), 'id'), // Ensure 'id' is the primary key
            ],
        ];
    }

    public function messages()
    {
        return [
            'plugin_slug.required' => 'Plugin is required.',
            'name.required' => 'Page name is required.',
            'slug.required' => 'Page slug is required.',
            'slug.unique' => 'The combination of slug and plugin already exists.',
        ];
    }
}

