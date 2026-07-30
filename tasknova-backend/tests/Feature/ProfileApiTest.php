<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProfileApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_endpoints_require_authentication(): void
    {
        $this->getJson('/api/profile')->assertUnauthorized();
        $this->putJson('/api/profile', $this->profilePayload())->assertUnauthorized();
    }

    public function test_an_authenticated_user_can_create_and_retrieve_own_profile(): void
    {
        $user = User::factory()->create(['email' => 'ana@example.com']);
        Sanctum::actingAs($user);

        $this->putJson('/api/profile', $this->profilePayload())
            ->assertOk()
            ->assertJsonPath('data.email', 'ana@example.com')
            ->assertJsonPath('data.first_name', 'Ana')
            ->assertJsonPath('data.gender', 'female')
            ->assertJsonPath('data.sex', 'female')
            ->assertJsonPath('data.academic_cycle', 'VI')
            ->assertJsonPath('data.cycle', 'VI');

        $this->assertDatabaseHas('user_profiles', [
            'user_id' => $user->id,
            'first_name' => 'Ana',
            'gender' => 'female',
            'academic_cycle' => 'VI',
        ]);

        $this->getJson('/api/profile')
            ->assertOk()
            ->assertJsonPath('data.user_id', $user->id)
            ->assertJsonPath('data.last_name', 'Torres');
    }

    public function test_profile_endpoints_never_return_another_users_profile(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $owner->profile()->create([
            'first_name' => 'Propietaria',
            'last_name' => 'Uno',
        ]);

        Sanctum::actingAs($otherUser);

        $this->getJson('/api/profile')
            ->assertOk()
            ->assertJsonPath('data.user_id', $otherUser->id)
            ->assertJsonPath('data.first_name', null);
    }

    public function test_profile_update_requires_the_complete_profile_data(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->putJson('/api/profile', ['first_name' => 'Ana'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'last_name', 'phone', 'birth_date', 'gender', 'institution', 'education_level',
                'career', 'grade', 'academic_cycle', 'department', 'province', 'district',
            ]);
    }

    public function test_profile_accepts_a_multipart_put_with_an_avatar(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $payload = array_merge($this->profilePayload(), [
            '_method' => 'PUT',
            'avatar' => UploadedFile::fake()->create('avatar.webp', 120, 'image/webp'),
        ]);

        $this->post('/api/profile', $payload)
            ->assertOk()
            ->assertJsonPath('data.first_name', 'Ana')
            ->assertJsonPath('data.avatar', fn ($value) => str_starts_with($value, 'avatars/'));
    }

    /**
     * @return array<string, string>
     */
    private function profilePayload(): array
    {
        return [
            'first_name' => 'Ana',
            'last_name' => 'Torres',
            'phone' => '987654321',
            'birth_date' => '2000-05-18',
            'sex' => 'female',
            'institution' => 'Universidad Nacional',
            'education_level' => 'university',
            'career' => 'Ingeniería de Sistemas',
            'grade' => 'Pregrado',
            'cycle' => 'VI',
            'department' => 'Lima',
            'province' => 'Lima',
            'district' => 'Miraflores',
        ];
    }
}
