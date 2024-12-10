<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BuildOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
//        return $this->user() && $this->user()->tokenCan('build:make');
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'package_name' => 'required|string|max:120',
            'app_name' => 'required|string|max:120',
            'domain' => 'required|string|max:120',
            'base_suffix' => 'required|string|max:120',
            'base_url' => 'required|url|max:120',
            'build_number' => 'required|string|max:50',
            'icon_url' => 'required|string|max:120',

            // Android Only Properties
            'jks_url' => 'url|required_unless:build_target,ios',
            'key_properties_url' => 'url|required_unless:build_target,ios',

            // iOS Only Properties
            'issuer_id' => 'string|required_unless:build_target,android',
            "key_id" => 'string|required_unless:build_target,android',
            "api_key_url" => 'url|required_unless:build_target,android',
            "team_id" => 'string|required_unless:build_target,android',
            "app_identifier" => 'string|required_unless:build_target,android',
        ];
    }

    // Prepare data before validation
    protected function prepareForValidation(): void
    {
        $this->merge(['build_target' => $this->target]);
    }
}
