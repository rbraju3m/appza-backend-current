<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules()
    {
        /*return [
            'plugin_slug' => 'required|string',
            'name' => 'required|string',
            'slug' => 'required|string',
            'background_color' => 'nullable|string',
            'border_color' => 'nullable|string',
            'border_radius' => 'nullable',
            'page_scope' => 'nullable',
            'component_limit' => 'nullable',
            'persistent_footer_buttons' => 'nullable',
        ];*/
        return [
            'plugin_slug' => 'required|string',
            'name' => 'required|string',
            'slug' => [
                'required',
                'string',
                Rule::unique('appfiy_page')->where(function ($query) {
                    return $query->where('plugin_slug', $this->plugin_slug);
                })
            ],
            'background_color' => 'nullable|string',
            'border_color' => 'nullable|string',
            'border_radius' => 'nullable',
            'page_scope' => 'nullable',
            'component_limit' => 'nullable',
            'persistent_footer_buttons' => 'nullable',
        ];
    }

    public function messages()
    {
        return [
            'plugin_slug.required' => 'Plugin is required.',
            'name.required' => 'Page name is required.',
            'slug.required' => 'Page slug is required.',
        ];
    }
}
