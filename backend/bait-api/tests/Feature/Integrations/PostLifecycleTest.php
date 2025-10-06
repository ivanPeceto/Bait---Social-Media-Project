<?php

namespace Tests\Feature\Integration;

use App\Modules\Multimedia\Domain\Models\Post;
use App\Modules\Multimedia\Domain\Models\ReactionType;
use App\Modules\UserData\Domain\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostLifecycleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Prepara el entorno de la base de datos para cada prueba.
     */
    protected function setUp(): void
    {
        parent::setUp();
        // Ejecutamos todos los seeders que nos darán los datos básicos necesarios (roles, estados, y tipos de reacción).
        $this->seed(\Database\Seeders\UserRolesSeeder::class);
        $this->seed(\Database\Seeders\UserStateSeeder::class);
        $this->seed(\Database\Seeders\AvatarSeeder::class);
        $this->seed(\Database\Seeders\BannerSeeder::class);
        $this->seed(\Database\Seeders\ReactionTypesSeeder::class); // ¡Muy importante para las reacciones!
    }

    /**
     * Prueba el ciclo de vida completo de un post: creación, comentario, reacción y repost.
     */
    #[Test]
    public function it_simulates_the_full_lifecycle_of_a_post_with_interactions(): void
    {
        // === ARRANGE: PREPARAR EL ESCENARIO COMPLETO ===
        // Creamos los diferentes actores que participarán en la historia.
        $postAuthor = User::factory()->create();
        $commenter = User::factory()->create();
        $reactor = User::factory()->create();
        $reposter = User::factory()->create();

        // === PASO 1: Un usuario crea un post ===
        $postContent = 'Este es un post que tendrá un ciclo de vida completo.';
        $postResponse = $this->actingAs($postAuthor, 'api')
            ->postJson('/api/posts', ['content_posts' => $postContent]);

        $postResponse->assertStatus(201);
        $this->assertDatabaseHas('posts', [
            'user_id' => $postAuthor->id,
            'content_posts' => $postContent
        ]);
        // Guardamos el ID del post creado para usarlo en los siguientes pasos.
        $postId = $postResponse->json('id');


        // === PASO 2: Otro usuario comenta el post ===
        $commentContent = '¡Este es el primer comentario!';
        $this->actingAs($commenter, 'api')
            ->postJson('/api/comments', [
                'post_id' => $postId,
                'content_comments' => $commentContent,
            ])
            ->assertStatus(201);

        $this->assertDatabaseHas('comments', [
            'post_id' => $postId,
            'user_id' => $commenter->id,
            'content_comments' => $commentContent,
        ]);


        // === PASO 3: Un tercer usuario reacciona al post ===
        // Obtenemos el ID de la reacción "like" que creó nuestro Seeder.
        $likeReactionType = ReactionType::where('name_reaction_types', 'like')->first();

        $this->actingAs($reactor, 'api')
            ->postJson('/api/post-reactions', [
                'post_id' => $postId,
                'reaction_type_id' => $likeReactionType->id,
            ])
            ->assertStatus(201);

        $this->assertDatabaseHas('post_reactions', [
            'post_id' => $postId,
            'user_id' => $reactor->id,
            'reaction_type_id' => $likeReactionType->id,
        ]);


        // === PASO 4: Un cuarto usuario hace repost del post ===
        $this->actingAs($reposter, 'api')
            ->postJson('/api/reposts', ['post_id' => $postId])
            ->assertStatus(201);

        $this->assertDatabaseHas('reposts', [
            'post_id' => $postId,
            'user_id' => $reposter->id,
        ]);
    }
}