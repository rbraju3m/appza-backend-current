<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        return [
            'api_url' => 'required|url',
            'item_id' => 'required|numeric',
        ];
    }

    public function messages()
    {
        return [
            'api_url.required' => 'API URL is required.',
            'api_url.url' => 'API URL must be a valid URL.',
            'item_id.required' => 'Item ID is required.',
            'item_id.numeric' => 'Item ID must be a number.',
        ];
    }
}
