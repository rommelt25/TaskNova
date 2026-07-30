<?php

namespace App\Http\Requests\Task;

use App\Models\Task;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'subject' => ['nullable', 'string', 'max:255', 'required_without:category_id'],
            'category_id' => ['nullable', Rule::exists('categories', 'id')->where('user_id', $this->user()?->id)],
            'priority' => ['nullable', Rule::in(Task::PRIORITIES)],
            'status' => ['nullable', Rule::in(Task::STATUSES)],
            'due_date' => ['required', 'date_format:Y-m-d'],
            'due_time' => ['nullable', 'date_format:H:i'],
        ];
    }
}
