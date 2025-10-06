<?php

namespace Tests\Feature\UserData;

use App\Modules\UserData\Domain\Models\User;
use Database\Seeders\AvatarSeeder;
use Database\Seeders\BannerSeeder;
use Database\Seeders\UserRolesSeeder;
use Database\Seeders\UserStateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(UserRolesSeeder::class);
        $this->seed(UserStateSeeder::class);
        $this->seed(AvatarSeeder::class);
        $this->seed(BannerSeeder::class);

        $this->user = User::factory()->create([
            'password' => Hash::make('current-password-123'),
        ]);
    }

    #[Test]
    public function an_authenticated_user_can_view_their_profile()
    {
        $response = $this->actingAs($this->user, 'api')->getJson(route('profile.show'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id', 'username', 'name', 'email', 'avatar', 'banner'
            ]
        ]);
        $response->assertJsonFragment(['username' => $this->user->username]);
    }

    #[Test]
    public function an_authenticated_user_can_update_their_profile()
    {
        $updateData = [
            'username' => 'new_username',
            'name' => 'New Name',
        ];

        $response = $this->actingAs($this->user, 'api')->putJson(route('profile.update'), $updateData);

        $response->assertStatus(200);
        $response->assertJsonFragment(['username' => 'new_username']);
        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'name' => 'New Name',
        ]);
    }

    #[Test]
    public function updating_profile_with_existing_username_fails()
    {
        User::factory()->create(['username' => 'existing_user']);

        $updateData = ['username' => 'existing_user'];
        $response = $this->actingAs($this->user, 'api')->putJson(route('profile.update'), $updateData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('username');
    }

    #[Test]
    public function an_authenticated_user_can_change_their_password()
    {
        $passwordData = [
            'current_password' => 'current-password-123',
            'new_password' => 'new-secure-password-456',
            'new_password_confirmation' => 'new-secure-password-456',
        ];

        $response = $this->actingAs($this->user, 'api')->putJson(route('profile.password'), $passwordData);

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Password updated successfully']);

        // Verificamos que la contraseña realmente cambió
        $this->user->refresh();
        $this->assertTrue(Hash::check('new-secure-password-456', $this->user->password));
    }

    #[Test]
    public function changing_password_with_incorrect_current_password_fails()
    {
        $passwordData = [
            'current_password' => 'wrong-password',
            'new_password' => 'new-secure-password-456',
            'new_password_confirmation' => 'new-secure-password-456',
        ];

        $response = $this->actingAs($this->user, 'api')->putJson(route('profile.password'), $passwordData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('current_password');
    }

    #[Test]
    public function changing_password_with_mismatched_new_password_fails()
    {
        $passwordData = [
            'current_password' => 'current-password-123',
            'new_password' => 'new-secure-password-456',
            'new_password_confirmation' => 'mistake-password-789',
        ];

        $response = $this->actingAs($this->user, 'api')->putJson(route('profile.password'), $passwordData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('new_password');
    }

    #[Test]
    public function an_unauthenticated_user_cannot_access_profile_endpoints()
    {
        $this->getJson(route('profile.show'))->assertStatus(401);
        $this->putJson(route('profile.update'), [])->assertStatus(401);
        $this->putJson(route('profile.password'), [])->assertStatus(401);
    }
}