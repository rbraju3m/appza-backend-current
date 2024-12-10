<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class IosBuildRequest extends FormRequest
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
        $rules = [
            'site_url' => 'required',
            'license_key' => 'required',
            'ios_issuer_id' => 'required|string',
            'ios_key_id' => 'required|string',
            'ios_p8_file_content' => 'required|string',
            'ios_team_id' => 'required|string',
        ];

        return $rules;
    }
}
