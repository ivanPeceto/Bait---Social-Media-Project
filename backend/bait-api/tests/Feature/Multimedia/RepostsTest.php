<?php

namespace Tests\Feature\Multimedia;

use Tests\TestCase;
use App\Modules\UserData\Domain\Models\User;
use App\Modules\Multimedia\Domain\Models\Post;
use App\Modules\Multimedia\Domain\Models\Repost;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Traits\WithUser;

class RepostsTest extends TestCase
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
    public function a_user_can_create_a_repost(): void
    {
        [$user, $headers] = $this->actingAsUser();
        $post = Post::factory()->create();

        $repostData = [
            'post_id' => $post->id,
        ];

        $response = $this->postJson('/api/reposts', $repostData, $headers);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                    'id',
                    'user' => [
                        'name'
                    ],
                    'post' => [
                        'id'
                    ],
                    'created_at',
                 ]);

        $this->assertDatabaseHas('reposts', [
            'user_id' => $user->id,
            'post_id' => $post->id,
        ]);
    }

    /** @test */
    public function a_user_cannot_repost_the_same_post_twice(): void
    {
        [$user, $headers] = $this->actingAsUser();
        $post = Post::factory()->create();

        Repost::factory()->create([
            'user_id' => $user->id,
            'post_id' => $post->id,
        ]);

        $repostData = [
            'post_id' => $post->id,
        ];

        $response = $this->postJson('/api/reposts', $repostData, $headers);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['post_id']);
    }

    /** @test */
    public function a_user_can_delete_their_own_repost(): void
    {
        [$user, $headers] = $this->actingAsUser();
        $repost = Repost::factory()->create(['user_id' => $user->id]);

        $response = $this->deleteJson('/api/reposts/' . $repost->id, [], $headers);

        $response->assertStatus(204);
        $this->assertDatabaseMissing('reposts', ['id' => $repost->id]);
    }

    /** @test */
    public function a_user_cannot_delete_another_users_repost(): void
    {
        [$owner, $headers] = $this->actingAsUser();
        $intruder = User::factory()->create();
        $repost = Repost::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAs($intruder)->deleteJson('/api/reposts/' . $repost->id);

        $response->assertStatus(403);
        $this->assertDatabaseHas('reposts', ['id' => $repost->id]);
    }
}