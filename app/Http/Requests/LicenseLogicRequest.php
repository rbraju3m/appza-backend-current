<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LicenseLogicRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare inputs before validation.
     */
    protected function prepareForValidation()
    {
        $event = $this->input('event');

        if (in_array($event, ['expiration', 'grace'])) {
            // Build composite key
            $this->merge([
                'event_combination' => $event . '_' .
                    $this->input('direction') . '_' .
                    $this->input('from_days') . '_' .
                    $this->input('to_days'),
            ]);
        } else {
            // For invalid type, only slug is unique
            $this->merge([
                'event_combination' => null,
                'direction' => null,
                'from_days' => null,
                'to_days' => null,
            ]);
        }
    }

    public function rules()
    {
        $rules = [
            'name' => 'required|string',
            'slug' => [
                'required',
                'string',
                'regex:/^[A-Za-z0-9_]+$/',
                Rule::unique('license_logics')->ignore($this->route('id'), 'id'),
            ],
            'event' => 'required|string',
            'direction' => 'nullable',
            'from_days' => 'nullable',
            'to_days' => 'nullable',
        ];

        $eventVal = $this->input('event');

        if (in_array($eventVal, ['expiration', 'grace'])) {
            // Required fields
            $rules['direction'] = 'required|string';
            $rules['from_days'] = 'required|integer';
            $rules['to_days'] = 'required|integer';

            // Unique composite
            $rules['event_combination'] = [
                Rule::unique('license_logics', 'event_combination')
                    ->ignore($this->route('id'), 'id'),
            ];
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'name.required' => 'Name is required.',

            'slug.required' => 'Identifier is required.',
            'slug.unique' => 'The identifier already exists.',
            'slug.regex' => 'The identifier may only contain letters, numbers, and underscores (_).',

            'event.required' => 'Event is required.',

            'direction.required' => 'Direction is required for this event.',
            'from_days.required' => 'From days is required for this event.',
            'to_days.required' => 'To days is required for this event.',

            'event_combination.unique' => 'The combination of Event, Direction, From Days, and To Days already exists.',
        ];
    }
}
