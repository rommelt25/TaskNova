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

    public function test_api_registration_does_not_enter_the_stateful_csrf_flow(): void
    {
        config()->set('sanctum.stateful', ['frontend.tasknova.test']);

        $this->withHeader('Origin', 'https://frontend.tasknova.test')
            ->postJson('/api/register', [
                'name' => 'Ana Torres',
                'email' => 'ana@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ])
            ->assertCreated();
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

    public function test_a_bearer_token_authenticates_a_protected_api_route(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('vue-web')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/user')
            ->assertOk()
            ->assertJsonPath('data.id', $user->id);
    }

    public function test_logout_revokes_only_the_bearer_token_used_for_the_request(): void
    {
        $user = User::factory()->create();
        $currentToken = $user->createToken('vue-web')->plainTextToken;
        $otherToken = $user->createToken('flutter-app')->plainTextToken;

        $this->withToken($currentToken)
            ->postJson('/api/logout')
            ->assertOk();

        $this->app['auth']->forgetGuards();

        $this->withToken($currentToken)
            ->getJson('/api/user')
            ->assertUnauthorized();

        $this->withToken($otherToken)
            ->getJson('/api/user')
            ->assertOk()
            ->assertJsonPath('data.id', $user->id);
    }

    public function test_logout_all_revokes_every_bearer_token_for_the_user(): void
    {
        $user = User::factory()->create();
        $currentToken = $user->createToken('vue-web')->plainTextToken;
        $otherToken = $user->createToken('flutter-app')->plainTextToken;

        $this->withToken($currentToken)
            ->postJson('/api/logout-all')
            ->assertOk();

        $this->assertDatabaseCount('personal_access_tokens', 0);
        $this->app['auth']->forgetGuards();

        $this->withToken($otherToken)
            ->getJson('/api/user')
            ->assertUnauthorized();
    }
}
