<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->optional()->paragraph(),
            'subject' => fake()->word(),
            'priority' => fake()->randomElement(Task::PRIORITIES),
            'status' => fake()->randomElement(Task::STATUSES),
            'due_date' => fake()->dateTimeBetween('today', '+1 month')->format('Y-m-d'),
        ];
    }
}
