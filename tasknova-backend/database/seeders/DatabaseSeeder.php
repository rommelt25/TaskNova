<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (! filter_var(env('SEED_DEMO_DATA', false), FILTER_VALIDATE_BOOL)) {
            return;
        }

        \App\Models\User::factory()->create();
    }
}
