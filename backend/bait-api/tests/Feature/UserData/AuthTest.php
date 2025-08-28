<?php

namespace Tests\Feature\UserData;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Traits\WithUser;
use App\Modules\UserData\Domain\Models\User;

class AuthTest extends TestCase
{
    use RefreshDatabase;
    use WithUser;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function it_registers_a_user_and_returns_a_token()
    {
        $payload = [
            'name' => 'Test',
            'email' => 'pepe@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ];

        $response = $this->postJson('/api/auth/register', $payload);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'access_token',
                     'token_type',
                     'expires_in'
                 ]);

        $this->assertDatabaseHas('users', ['email' => 'pepe@example.com']);
    }

    /** @test */
    public function it_logs_in_and_returns_a_token()
    {
        $user = User::factory()->create([
            'email' => 'login@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'login@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['access_token']);
    }

    /** @test */
    public function it_fails_to_login_with_invalid_credentials()
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function it_refreshes_a_token()
    {
        [$user, $headers] = $this->actingAsUser();

        $response = $this->postJson('/api/auth/refresh', [], $headers);

        $response->assertStatus(200)
                 ->assertJsonStructure(['access_token']);
    }

    /** @test */
    public function it_logs_out_a_user()
    {
        [$user, $headers] = $this->actingAsUser();

        $response = $this->postJson('/api/auth/logout', [], $headers);

        $response->assertStatus(200);
    }

    /** @test */
    public function it_returns_authenticated_user_data()
    {
        [$user, $headers] = $this->actingAsUser();

        $response = $this->getJson('/api/auth/me', $headers);

        $response->assertStatus(200)
                 ->assertJsonFragment(['email' => $user->email]);
    }
}
