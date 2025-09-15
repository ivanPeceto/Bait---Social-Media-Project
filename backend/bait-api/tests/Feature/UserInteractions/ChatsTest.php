<?php

namespace Tests\Feature\UserInteractions;

use Tests\TestCase;
use App\Modules\UserData\Domain\Models\User;
use App\Modules\UserInteractions\Domain\Models\Chat;
use App\Modules\UserInteractions\Domain\Models\Message;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Traits\WithUser;

class ChatsTest extends TestCase
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
    }

    /** @test */
    public function a_user_can_view_their_list_of_chats(): void
    {
        // Arrange
        [$user, $headers] = $this->actingAsUser();
        $otherUser = User::factory()->create();
        $chat = Chat::factory()->create();
        $chat->users()->attach([$user->id, $otherUser->id]);
        Message::factory()->create(['chat_id' => $chat->id, 'user_id' => $otherUser->id]);

        // Act
        $response = $this->getJson('/api/chats', $headers);

        // Assert
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         '*' => [
                             'id',
                             'participants' => [
                                 '*' => [
                                     'id',
                                     'username'
                                 ]
                             ],
                             'last_message' => [
                                 'id',
                                 'content_messages',
                             ],
                             'created_at'
                         ]
                     ]
                 ]);
    }
    
    /** @test */
    public function a_user_can_create_a_chat(): void
    {
        // Arrange
        [$user, $headers] = $this->actingAsUser();
        $otherUser = User::factory()->create();

        $chatData = [
            'participants' => [$otherUser->id]
        ];

        // Act
        $response = $this->postJson('/api/chats', $chatData, $headers);

        // Assert
        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'data' => [
                         'id',
                         'participants' => [
                             '*' => [
                                 'id',
                                 'username'
                             ]
                         ],
                         'last_message', // Null si no hay mensajes en un chat nuevo
                         'created_at'
                     ]
                 ]);
        
        $this->assertDatabaseHas('chat_users', [
            'user_id' => $user->id,
            'chat_id' => $response->json('data.id')
        ]);
        
        $this->assertDatabaseHas('chat_users', [
            'user_id' => $otherUser->id,
            'chat_id' => $response->json('data.id')
        ]);
    }

    /** @test */
    public function a_user_cannot_create_a_chat_without_participants(): void
    {
        // Arrange
        [$user, $headers] = $this->actingAsUser();
        $chatData = [
            'participants' => []
        ];

        // Act
        $response = $this->postJson('/api/chats', $chatData, $headers);

        // Assert
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['participants']);
    }

    /** @test */
    public function a_user_can_view_a_chat_they_are_a_participant_of(): void
    {
        // Arrange
        [$user, $headers] = $this->actingAsUser();
        $otherUser = User::factory()->create();
        $chat = Chat::factory()->create();
        $chat->users()->attach([$user->id, $otherUser->id]);
        Message::factory()->count(2)->create(['chat_id' => $chat->id, 'user_id' => $user->id]);
        
        // Act
        $response = $this->getJson('/api/chats/' . $chat->id, $headers);
        
        // Assert
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         'id',
                         'participants' => [
                             '*' => ['id', 'username']
                         ],
                         'messages' => [
                             '*' => [
                                 'id',
                                 'content_messages',
                             ]
                         ]
                     ]
                 ]);
    }

    /** @test */
    public function a_user_cannot_view_a_chat_they_are_not_a_participant_of(): void
    {
        // Arrange
        [$user, $headers] = $this->actingAsUser();
        $intruder = User::factory()->create();
        $chat = Chat::factory()->create();
        $chat->users()->attach($intruder->id);
        
        // Act
        $response = $this->getJson('/api/chats/' . $chat->id, $headers);
        
        // Assert
        $response->assertStatus(403);
    }
}