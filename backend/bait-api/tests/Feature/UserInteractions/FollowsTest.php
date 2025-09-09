<?php

namespace Tests\Feature\UserInteractions;

use Tests\TestCase;
use App\Modules\UserData\Domain\Models\User;
use App\Modules\UserInteractions\Domain\Models\Follow;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Traits\WithUser;

class FollowsTest extends TestCase
{
    use RefreshDatabase;
    use WithUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\UserRolesSeeder::class);
        $this->seed(\Database\Seeders\UserStateSeeder::class);
        $this->seed(\Database\Seeders\AvatarSeeder::class);
        $this->seed(\Database\Seeders\BannerSeeder::class);
    }

    /** @test */
    public function a_user_can_follow_another_user(): void
    {
        // Arrange
        [$user, $headers] = $this->actingAsUser();
        $otherUser = User::factory()->create();

        $followData = [
            'following_id' => $otherUser->id,
        ];

        // Act
        $response = $this->postJson('/api/follows', $followData, $headers);

        // Assert
        $response->assertStatus(201)
                 ->assertJson(['message' => 'User followed successfully.']);
        
        $this->assertDatabaseHas('follows', [
            'follower_id' => $user->id,
            'following_id' => $otherUser->id,
        ]);
    }

    /** @test */
    public function a_user_cannot_follow_themself(): void
    {
        // Arrange
        [$user, $headers] = $this->actingAsUser();

        $followData = [
            'following_id' => $user->id,
        ];

        // Act
        $response = $this->postJson('/api/follows', $followData, $headers);

        // Assert
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['following_id']);
    }

    /** @test */
    public function a_user_cannot_follow_a_user_they_are_already_following(): void
    {
        // Arrange
        [$user, $headers] = $this->actingAsUser();
        $otherUser = User::factory()->create();
        Follow::create([
            'follower_id' => $user->id,
            'following_id' => $otherUser->id,
        ]);

        $followData = [
            'following_id' => $otherUser->id,
        ];

        // Act
        $response = $this->postJson('/api/follows', $followData, $headers);

        // Assert
        $response->assertStatus(409)
                 ->assertJson(['message' => 'Already following this user.']);
    }

    /** @test */
    public function a_user_can_unfollow_another_user(): void
    {
        // Arrange
        [$user, $headers] = $this->actingAsUser();
        $otherUser = User::factory()->create();
        Follow::create([
            'follower_id' => $user->id,
            'following_id' => $otherUser->id,
        ]);

        $unfollowData = [
            'following_id' => $otherUser->id,
        ];

        // Act
        $response = $this->deleteJson('/api/follows', $unfollowData, $headers);

        // Assert
        $response->assertStatus(204);
        
        $this->assertDatabaseMissing('follows', [
            'follower_id' => $user->id,
            'following_id' => $otherUser->id,
        ]);
    }
    
    /** @test */
    public function a_user_cannot_unfollow_a_user_they_are_not_following(): void
    {
        // Arrange
        [$user, $headers] = $this->actingAsUser();
        $otherUser = User::factory()->create();

        $unfollowData = [
            'following_id' => $otherUser->id,
        ];

        // Act
        $response = $this->deleteJson('/api/follows', $unfollowData, $headers);

        // Assert
        $response->assertStatus(404);
    }
}