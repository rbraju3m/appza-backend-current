<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LicenseMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        $rules = [
            'product_id' => 'required|integer|exists:appza_fluent_informations,id',
            'addon_id' => 'nullable|integer',
            'license_logic_id' => 'required|integer|exists:license_logics,id',
            'license_type' => 'required|string',
            'message_user' => 'nullable|string',
            'message_admin' => 'nullable|string',
            'message_special' => 'nullable|string',
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
            // unique combination of product_id + license_logic_id + license_type
            'product_id' => [
                'required',
                'integer',
                Rule::unique('license_messages')
                    ->where(function ($query) {
                        return $query->where('license_logic_id', $this->license_logic_id)
                            ->where('license_type', $this->license_type);
                    }),
            ],

            // at least one message must be provided
            'message_user' => 'required_without_all:message_admin,message_special|string|nullable',
            'message_admin' => 'required_without_all:message_user,message_special|string|nullable',
            'message_special' => 'required_without_all:message_user,message_admin|string|nullable',
        ];
    }

    protected function updateRules()
    {
        return [
            // unique combination, ignoring current record
            'product_id' => [
                'required',
                'integer',
                Rule::unique('license_messages')
                    ->where(function ($query) {
                        return $query->where('license_logic_id', $this->license_logic_id)
                            ->where('license_type', $this->license_type);
                    })
                    ->ignore($this->route('license_message'), 'id'),
            ],

            // at least one message must be provided
            'message_user' => 'required_without_all:message_admin,message_special|string|nullable',
            'message_admin' => 'required_without_all:message_user,message_special|string|nullable',
            'message_special' => 'required_without_all:message_user,message_admin|string|nullable',
        ];
    }

    public function messages()
    {
        return [
            'product_id.required' => 'Product is required.',
            'license_logic_id.required' => 'Matrix is required.',
            'license_type.required' => 'License type is required.',
            'product_id.unique' => 'This Product + Matrix + License type already exists.',
            'message_user.required_without_all' => 'At least one message (User, Admin, or Special) is required.',
            'message_admin.required_without_all' => 'At least one message (User, Admin, or Special) is required.',
            'message_special.required_without_all' => 'At least one message (User, Admin, or Special) is required.',
        ];
    }

}
