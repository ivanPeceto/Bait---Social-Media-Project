<?php

namespace Tests\Feature\Multimedia;

use Tests\TestCase;
use App\Modules\UserData\Domain\Models\User;
use App\Modules\Multimedia\Domain\Models\Post;
use App\Modules\Multimedia\Domain\Models\PostReaction;
use App\Modules\Multimedia\Domain\Models\ReactionType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Traits\WithUser;

class PostReactionsTest extends TestCase
{
    use RefreshDatabase;
    use WithUser;

    protected function setUp(): void
    {
        parent::setUp();
        // Seeders necesarios para las pruebas.
        $this->seed(\Database\Seeders\UserRolesSeeder::class);
        $this->seed(\Database\Seeders\UserStateSeeder::class);
        $this->seed(\Database\Seeders\AvatarSeeder::class);
        $this->seed(\Database\Seeders\BannerSeeder::class);
        // Sembrar los tipos de reacción es crucial para las pruebas.
        $this->seed(\Database\Seeders\ReactionTypesSeeder::class);
    }
    
    #[Test]
    public function a_user_can_react_to_a_post(): void
    {
        // Arrange
        [$user, $headers] = $this->actingAsUser();
        $post = Post::factory()->create();
        $likeReaction = ReactionType::where('name_reaction_types', 'like')->first();

        // Act
        $reactionData = [
            'post_id' => $post->id,
            'reaction_type_id' => $likeReaction->id,
        ];

        $response = $this->postJson('/api/post-reactions', $reactionData, $headers);

        // Assert
        $response->assertStatus(201)
                 ->assertJsonStructure([
                    'id',
                    'user',
                    'post_id',
                    'reaction_type',
                    'created_at',
                 ]);

        $this->assertDatabaseHas('post_reactions', [
            'user_id' => $user->id,
            'post_id' => $post->id,
            'reaction_type_id' => $likeReaction->id,
        ]);
    }

    #[Test]
    public function a_user_can_update_their_reaction_to_a_post(): void
    {
        // Arrange
        [$user, $headers] = $this->actingAsUser();
        $post = Post::factory()->create();
        $likeReaction = ReactionType::where('name_reaction_types', 'like')->first();
        $loveReaction = ReactionType::where('name_reaction_types', 'love')->first();

        PostReaction::factory()->create([
            'user_id' => $user->id,
            'post_id' => $post->id,
            'reaction_type_id' => $likeReaction->id,
        ]);

        // Act
        $reactionData = [
            'post_id' => $post->id,
            'reaction_type_id' => $loveReaction->id,
        ];

        $response = $this->postJson('/api/post-reactions', $reactionData, $headers);

        // Assert
        $response->assertStatus(200);
        $this->assertDatabaseHas('post_reactions', [
            'user_id' => $user->id,
            'post_id' => $post->id,
            'reaction_type_id' => $loveReaction->id,
        ]);
    }

    #[Test]
    public function a_user_can_remove_their_reaction_to_a_post(): void
    {
        // Arrange
        [$user, $headers] = $this->actingAsUser();
        $post = Post::factory()->create();
        $likeReaction = ReactionType::where('name_reaction_types', 'like')->first();

        PostReaction::factory()->create([
            'user_id' => $user->id,
            'post_id' => $post->id,
            'reaction_type_id' => $likeReaction->id,
        ]);

        // Act
        $reactionData = [
            'post_id' => $post->id,
            'reaction_type_id' => $likeReaction->id,
        ];

        $response = $this->postJson('/api/post-reactions', $reactionData, $headers);

        // Assert
        $response->assertStatus(200)
                 ->assertJson(['message' => 'Reaction removed successfully.']);

        $this->assertDatabaseMissing('post_reactions', [
            'user_id' => $user->id,
            'post_id' => $post->id,
        ]);
    }
}