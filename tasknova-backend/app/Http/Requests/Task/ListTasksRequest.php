<?php

namespace App\Http\Requests\Task;

use App\Models\Task;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListTasksRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'scope' => ['nullable', Rule::in(['all', 'owned', 'shared'])],
            'status' => ['nullable', Rule::in([...Task::STATUSES, 'overdue'])],
            'priority' => ['nullable', Rule::in(Task::PRIORITIES)],
            'category_id' => ['nullable', 'integer'],
            'subject' => ['nullable', 'string', 'max:255'],
            'search' => ['nullable', 'string', 'max:150'],
            'sort' => ['nullable', Rule::in(['due_date', 'title', 'priority', 'status'])],
            'direction' => ['nullable', Rule::in(['asc', 'desc'])],
            'due_from' => ['nullable', 'date_format:Y-m-d'],
            'due_to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:due_from'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
