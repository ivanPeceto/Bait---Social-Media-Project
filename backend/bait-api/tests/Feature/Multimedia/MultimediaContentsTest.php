<?php

namespace Tests\Feature\Multimedia;

use Tests\TestCase;
use App\Modules\UserData\Domain\Models\User;
use App\Modules\Multimedia\Domain\Models\Post;
use App\Modules\Multimedia\Domain\Models\MultimediaContent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Traits\WithUser;

class MultimediaContentsTest extends TestCase
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
    public function a_user_can_create_multimedia_content_for_their_own_post(): void
    {
        [$user, $headers] = $this->actingAsUser();
        $post = Post::factory()->create(['user_id' => $user->id]);

        $contentData = [
            'url_multimedia_contents' => 'https://example.com/image.jpg',
            'type_multimedia_contents' => 'image',
            'post_id' => $post->id,
        ];

        $response = $this->postJson('/api/multimedia-contents', $contentData, $headers);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                    'id',
                    'url',
                    'type',
                    'created_at',
                 ]);

        $this->assertDatabaseHas('multimedia_contents', [
            'url_multimedia_contents' => $contentData['url_multimedia_contents'],
            'type_multimedia_contents' => $contentData['type_multimedia_contents'],
            'post_id' => $post->id,
        ]);
    }
    
    #[Test]
    public function a_user_cannot_create_multimedia_content_for_another_users_post(): void
    {
        [$owner, $headers] = $this->actingAsUser();
        $otherUser = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $owner->id]);

        $contentData = [
            'url_multimedia_contents' => 'https://example.com/malicious.jpg',
            'type_multimedia_contents' => 'image',
            'post_id' => $post->id,
        ];

        $response = $this->actingAs($otherUser)->postJson('/api/multimedia-contents', $contentData);

        $response->assertStatus(403)
                 ->assertJson(['message' => 'Unauthorized action.']);

        $this->assertDatabaseMissing('multimedia_contents', ['post_id' => $post->id]);
    }

    #[Test]
    public function a_user_can_delete_their_own_multimedia_content(): void
    {
        [$user, $headers] = $this->actingAsUser();
        $post = Post::factory()->create(['user_id' => $user->id]);
        $multimediaContent = MultimediaContent::factory()->create(['post_id' => $post->id]);

        $response = $this->deleteJson('/api/multimedia-contents/' . $multimediaContent->id, [], $headers);

        $response->assertStatus(204);
        $this->assertDatabaseMissing('multimedia_contents', ['id' => $multimediaContent->id]);
    }

    #[Test]
    public function a_user_cannot_delete_another_users_multimedia_content(): void
    {
        [$owner, $headers] = $this->actingAsUser();
        $otherUser = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $owner->id]);
        $multimediaContent = MultimediaContent::factory()->create(['post_id' => $post->id]);

        $response = $this->actingAs($otherUser)->deleteJson('/api/multimedia-contents/' . $multimediaContent->id);

        $response->assertStatus(403);
        $this->assertDatabaseHas('multimedia_contents', ['id' => $multimediaContent->id]);
    }
}