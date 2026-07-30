<?php

namespace App\Http\Requests\Calendar;

use App\Models\Task;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListCalendarRequest extends FormRequest
{
    public function authorize(): bool { return $this->user() !== null; }

    public function rules(): array
    {
        return [
            'month' => ['required', 'date_format:Y-m'],
            'category_id' => ['nullable', Rule::exists('categories', 'id')->where('user_id', $this->user()?->id)],
            'priority' => ['nullable', Rule::in(Task::PRIORITIES)],
            'status' => ['nullable', Rule::in([...Task::STATUSES, 'overdue'])],
        ];
    }
}
