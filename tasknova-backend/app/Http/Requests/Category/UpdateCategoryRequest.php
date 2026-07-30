<?php

namespace App\Http\Requests\Category;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->id === $this->route('category')?->user_id; }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:60', Rule::unique('categories')->where('user_id', $this->user()?->id)->ignore($this->route('category'))],
            'color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'icon' => ['nullable', 'string', 'max:20'],
        ];
    }
}
