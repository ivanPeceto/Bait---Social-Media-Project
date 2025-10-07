<?php

namespace Tests\Feature\UserInteractions;

use Tests\TestCase;
use App\Modules\UserData\Domain\Models\User;
use App\Modules\UserInteractions\Domain\Models\Chat;
use App\Modules\UserInteractions\Domain\Models\Message;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Traits\WithUser;

class MessagesTest extends TestCase
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
    public function a_user_can_create_a_message_in_their_chat(): void
    {
        [$user, $headers] = $this->actingAsUser();
        $otherUser = User::factory()->create();
        $chat = Chat::factory()->create();
        $chat->users()->attach([$user->id, $otherUser->id]);

        $messageData = [
            'content_messages' => 'Hola, este es un mensaje de prueba.',
        ];

        $response = $this->postJson('/api/chats/' . $chat->id . '/messages', $messageData, $headers);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                    'data' => [
                        'id',
                        'content_messages',
                        'user' => [
                            'id',
                            'username'
                        ],
                        'created_at',
                    ]
                 ]);

        $this->assertDatabaseHas('messages', [
            'content_messages' => $messageData['content_messages'],
            'chat_id' => $chat->id,
            'user_id' => $user->id,
        ]);
    }

    #[Test]
    public function a_user_cannot_create_a_message_in_a_chat_they_are_not_a_participant_of(): void
    {
        [$user, $headers] = $this->actingAsUser();
        $otherUser = User::factory()->create();
        $chat = Chat::factory()->create();
        $chat->users()->attach($otherUser->id);

        $messageData = [
            'content_messages' => 'Hola, soy un intruso.',
        ];

        $response = $this->postJson('/api/chats/' . $chat->id . '/messages', $messageData, $headers);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('messages', ['chat_id' => $chat->id, 'user_id' => $user->id]);
    }

    #[Test]
    public function a_user_can_view_their_messages_in_a_chat(): void
    {
        [$user, $headers] = $this->actingAsUser();
        $otherUser = User::factory()->create();
        $chat = Chat::factory()->create();
        $chat->users()->attach([$user->id, $otherUser->id]);
        Message::factory()->count(2)->create(['chat_id' => $chat->id, 'user_id' => $user->id]);

        $response = $this->getJson('/api/chats/' . $chat->id . '/messages', $headers);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'content_messages',
                    'user' => ['id', 'username'],
                    'created_at',
                ]
            ],
            'links' => [
                'first',
                'last',
                'prev',
                'next',
            ],
            'meta' => [
                'current_page',
                'from',
                'last_page',
                'links',
                'path',
                'per_page',
                'to',
                'total',
            ],
        ]);
    }

    #[Test]
    public function a_user_cannot_create_a_message_with_invalid_data(): void
    {
        [$user, $headers] = $this->actingAsUser();
        $otherUser = User::factory()->create();
        $chat = Chat::factory()->create();
        $chat->users()->attach([$user->id, $otherUser->id]);

        $invalidData = [
            'content_messages' => '', 
        ];

        $response = $this->postJson('/api/chats/' . $chat->id . '/messages', $invalidData, $headers);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['content_messages']);

        $longMessageData = [
            'content_messages' => str_repeat('a', 1001),
        ];

        $response = $this->postJson('/api/chats/' . $chat->id . '/messages', $longMessageData, $headers);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['content_messages']);
    }
}