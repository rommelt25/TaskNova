<?php

namespace App\Http\Requests\Preference;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserPreferenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'theme' => ['sometimes', 'string', Rule::in(['system', 'light', 'dark'])],
            'language' => ['sometimes', 'string', 'max:10'],
            'notifications_enabled' => ['sometimes', 'boolean'],
            'timezone' => ['sometimes', 'timezone'],
            'week_start' => ['sometimes', 'string', Rule::in(['monday', 'sunday'])],
            'default_view' => ['sometimes', 'string', Rule::in(['list', 'calendar', 'board'])],
        ];
    }
}
