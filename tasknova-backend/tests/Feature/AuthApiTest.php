<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_can_register_and_receive_a_bearer_token(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Ana Torres',
            'email' => 'ana@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'device_name' => 'vue-web',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.user.email', 'ana@example.com')
            ->assertJsonPath('data.token_type', 'Bearer')
            ->assertJsonStructure(['data' => ['user' => ['id', 'name', 'email'], 'token']]);

        $this->assertDatabaseHas('users', ['email' => 'ana@example.com']);
    }

    public function test_a_user_can_log_in_with_valid_credentials(): void
    {
        $user = User::factory()->create(['email' => 'ana@example.com']);

        $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
            'device_name' => 'flutter-app',
        ])
            ->assertOk()
            ->assertJsonPath('data.user.id', $user->id)
            ->assertJsonPath('data.token_type', 'Bearer');
    }

    public function test_login_rejects_invalid_credentials(): void
    {
        User::factory()->create(['email' => 'ana@example.com']);

        $this->postJson('/api/login', [
            'email' => 'ana@example.com',
            'password' => 'incorrect-password',
        ])
            ->assertUnauthorized()
            ->assertJsonPath('message', 'Las credenciales son incorrectas.');
    }
}
