<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class BuildResponseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'build_message' => 'nullable|string',
        ];
    }

    /**
     * Customize the error messages for validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'apk_url.required_if' => 'The apk_url field is required when status is completed.',
            'message.required_unless' => 'The message field is required unless status is completed.',
        ];
    }
}
