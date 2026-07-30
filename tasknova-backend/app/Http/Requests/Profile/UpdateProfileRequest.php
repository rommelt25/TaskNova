<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'gender' => $this->input('gender', $this->input('sex')),
            'academic_cycle' => $this->input('academic_cycle', $this->input('cycle')),
        ]);
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:20', 'regex:/^(?:\\+51\\s?)?9\\d{8}$/'],
            'birth_date' => ['required', 'date', 'before:today'],
            'gender' => ['required', Rule::in(['male', 'female', 'undisclosed'])],
            'institution' => ['required', 'string', 'max:160'],
            'education_level' => ['required', Rule::in(['primary', 'secondary', 'institute', 'university', 'postgraduate', 'other'])],
            'career' => ['required', 'string', 'max:160'],
            'grade' => ['required', 'string', 'max:80'],
            'academic_cycle' => ['required', 'string', 'max:40'],
            'department' => ['required', 'string', 'max:100'],
            'province' => ['required', 'string', 'max:100'],
            'district' => ['required', 'string', 'max:100'],
            'avatar' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }
}
