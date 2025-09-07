<?php

namespace App\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;

class FreeTrialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        $rules = [
            'name' => 'required|string',
            'email' => ['required', 'email'],
            'site_url' => ['required', 'url'],
        ];
        return $rules;
    }

    public function messages()
    {
        return [
            'name.required'     => 'Your Name field is empty. Please give your name & submit again.',
            'email.required'    => 'Your Email field is empty. Please give your email & submit again.',
            'email.email'       => 'The email address is invalid or does not exist, please try with proper mail.',
            'site_url.required'       => 'Your site url field is empty. Please give your site url & submit again.',
            'site_url.url'       => 'Your site url is not valid. Please give your site url & submit again.',
        ];
    }
}

