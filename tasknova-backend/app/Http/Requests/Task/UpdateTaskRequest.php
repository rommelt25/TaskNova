<?php

namespace App\Http\Requests\Task;

use App\Models\Task;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:150'],
            'description' => ['sometimes', 'nullable', 'string'],
            'subject' => ['sometimes', 'required', 'string', 'max:255'],
            'category_id' => ['sometimes', 'nullable', Rule::exists('categories', 'id')->where('user_id', $this->user()?->id)],
            'priority' => ['sometimes', 'required', Rule::in(Task::PRIORITIES)],
            'status' => ['sometimes', 'required', Rule::in(Task::STATUSES)],
            'due_date' => ['sometimes', 'required', 'date_format:Y-m-d'],
            'due_time' => ['sometimes', 'nullable', 'date_format:H:i'],
        ];
    }
}
