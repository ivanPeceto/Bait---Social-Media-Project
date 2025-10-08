<?php

namespace Tests\Feature\UserData;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Modules\UserData\Domain\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Nos aseguramos de que todos los datos base existan antes de cada test.
        $this->seed(\Database\Seeders\UserRolesSeeder::class);
        $this->seed(\Database\Seeders\UserStateSeeder::class);
        $this->seed(\Database\Seeders\AvatarSeeder::class);
        $this->seed(\Database\Seeders\BannerSeeder::class);
    }

    #[Test]
    public function it_registers_a_user_successfully()
    {
        $payload = [
            'username' => 'TestUser',
            'name' => 'Test Name',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson(route('auth.register'), $payload);

        $response->assertStatus(200); // 200 porque el login es exitoso.
        $response->assertJsonStructure([
            'access_token',
            'token_type',
            'expires_in',
            'user' => [
                'id', 'username', 'name', 'email'
            ]
        ]);
        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
    }

    #[Test]
    public function it_logs_in_a_user_and_returns_a_token_with_user_data()
    {
        User::factory()->create([
            'email' => 'login@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson(route('auth.login'), [
            'email' => 'login@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['access_token', 'user']);
        $response->assertJsonPath('user.email', 'login@example.com');
    }

    #[Test]
    public function it_fails_to_login_with_invalid_credentials()
    {
        $response = $this->postJson(route('auth.login'), [
            'email' => 'nonexistent@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401);
        $response->assertJson(['message' => 'Invalid credentials']);
    }

    #[Test]
    public function it_logs_out_an_authenticated_user()
    {
        $user = User::factory()->create();
        $token = auth('api')->fromUser($user);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
                         ->postJson(route('auth.logout'));

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Successfully logged out']);
    }

    #[Test]
    public function it_refreshes_a_token_for_an_authenticated_user()
    {
        $user = User::factory()->create();
        $token = auth('api')->fromUser($user);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
                         ->postJson(route('auth.refresh'));
                         
        $response->assertStatus(200);
        $response->assertJsonStructure(['access_token', 'user']);
        $this->assertNotEquals($token, $response->json('access_token'));
    }

    /* #[Test]
    public function it_returns_the_authenticated_user_data()
    {
        $user = User::factory()->create();
        $token = auth('api')->fromUser($user);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
                         ->getJson(route('auth.me'));

        $response->assertStatus(200);
        $response->assertJsonPath('data.email', $user->email);
    } */
}
