<?php

namespace Tests\Feature\Multimedia;

use Tests\TestCase;
use App\Modules\UserData\Domain\Models\User;
use App\Modules\Multimedia\Domain\Models\Post;
use App\Modules\Multimedia\Domain\Models\Comment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Traits\WithUser;

class CommentsTest extends TestCase
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
    public function a_user_can_create_a_comment(): void
    {
        [$user, $headers] = $this->actingAsUser();
        $post = Post::factory()->create();

        $commentData = [
            'content_comments' => 'Este es el contenido de un comentario de prueba.',
            'post_id' => $post->id,
        ];

        $response = $this->postJson('/api/comments', $commentData, $headers);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                    'id',
                    'content',
                    'user' => [
                        'name'
                    ],
                    'created_at',
                    'updated_at',
                 ]);

        $this->assertDatabaseHas('comments', [
            'content_comments' => $commentData['content_comments'],
            'user_id' => $user->id,
            'post_id' => $post->id,
        ]);
    }

    #[Test]
    public function comment_creation_fails_with_invalid_data(): void
    {
        [$user, $headers] = $this->actingAsUser();
        
        $response = $this->postJson('/api/comments', ['content_comments' => ''], $headers);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['content_comments', 'post_id']);
    }
    
    #[Test]
    public function a_user_can_update_their_own_comment(): void
    {
        [$user, $headers] = $this->actingAsUser();
        $comment = Comment::factory()->create(['user_id' => $user->id]);
        $updatedContent = 'Contenido del comentario actualizado.';

        $response = $this->putJson('/api/comments/' . $comment->id, ['content_comments' => $updatedContent], $headers);

        $response->assertStatus(200);
        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'content_comments' => $updatedContent,
        ]);
    }

    #[Test]
    public function a_user_cannot_update_another_users_comment(): void
    {
        [$owner, $headers] = $this->actingAsUser();
        $otherUser = User::factory()->create();
        $comment = Comment::factory()->create(['user_id' => $owner->id]);
        
        $response = $this->actingAs($otherUser)->putJson('/api/comments/' . $comment->id, ['content_comments' => 'Contenido malicioso.']);

        $response->assertStatus(403);
        $this->assertDatabaseHas('comments', ['id' => $comment->id, 'content_comments' => $comment->content_comments]);
    }

    #[Test]
    public function a_user_can_delete_their_own_comment(): void
    {
        [$user, $headers] = $this->actingAsUser();
        $comment = Comment::factory()->create(['user_id' => $user->id]);

        $response = $this->deleteJson('/api/comments/' . $comment->id, [], $headers);

        $response->assertStatus(204);
        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    }

    #[Test]
    public function a_user_cannot_delete_another_users_comment(): void
    {
        [$owner, $headers] = $this->actingAsUser();
        $intruder = User::factory()->create();
        $comment = Comment::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAs($intruder)->deleteJson('/api/comments/' . $comment->id);

        $response->assertStatus(403);
        $this->assertDatabaseHas('comments', ['id' => $comment->id]);
    }
}