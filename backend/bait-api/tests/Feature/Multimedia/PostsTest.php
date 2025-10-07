<?php

namespace Tests\Feature\Multimedia;

use Tests\TestCase;
use App\Modules\UserData\Domain\Models\User;
use App\Modules\Multimedia\Domain\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Traits\WithUser;

class PostsTest extends TestCase
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
    
    #[Test]
    public function a_user_can_create_a_post(): void
    {
        [$user, $headers] = $this->actingAsUser();

        $postData = [
            'content_posts' => 'Este es el contenido de un post de prueba.',
            'user_id' => $user->id,
        ];

        $response = $this->postJson('/api/posts', $postData, $headers);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                    'id',
                    'content_posts',
                    'user_id',
                    'created_at',
                    'updated_at',
                 ]);

        $this->assertDatabaseHas('posts', [
            'content_posts' => $postData['content_posts'],
            'user_id' => $user->id,
        ]);
    }

    #[Test]
    public function post_creation_fails_with_invalid_data(): void
    {
        
        [$user, $headers] = $this->actingAsUser();
        
        $response = $this->postJson('/api/posts', ['content_posts' => ''], $headers);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['content_posts']);
    }

    #[Test]
    public function a_user_can_update_their_own_post(): void
    {
        [$user, $headers] = $this->actingAsUser();
        $post = Post::factory()->create(['user_id' => $user->id]);
        $updatedContent = 'Contenido del post actualizado.';

        $response = $this->patchJson('/api/posts/' . $post->id, ['content_posts' => $updatedContent], $headers);

        $response->assertStatus(200);
        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'content_posts' => $updatedContent,
        ]);
    }

    #[Test]
    public function a_user_cannot_update_another_users_post(): void
    {
        [$owner, $headers] = $this->actingAsUser();
        $otherUser = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $owner->id]);
        
        $response = $this->actingAs($otherUser)->patchJson('/api/posts/' . $post->id, ['content_posts' => 'Contenido malicioso.']);

        $response->assertStatus(403);
        $this->assertDatabaseHas('posts', ['id' => $post->id, 'content_posts' => $post->content_posts]);
    }
    
    #[Test]
    public function a_user_can_delete_their_own_post(): void
    {
        [$user, $headers] = $this->actingAsUser();
        $post = Post::factory()->create(['user_id' => $user->id]);

        $response = $this->deleteJson('/api/posts/' . $post->id, [], $headers);

        $response->assertStatus(204);
        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

    #[Test]
    public function a_user_cannot_delete_another_users_post(): void
    {
        [$owner, $headers] = $this->actingAsUser();
        $intruder = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAs($intruder)->deleteJson('/api/posts/' . $post->id);

        $response->assertStatus(403);
        $this->assertDatabaseHas('posts', ['id' => $post->id]);
    }

    #[Test]
    public function a_post_belongs_to_a_user(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);
        $this->assertEquals($user->username, $post->user->username);
    }
}