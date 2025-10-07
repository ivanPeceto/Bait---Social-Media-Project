<?php

namespace Tests\Feature\Integration;

use App\Modules\UserData\Domain\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatFlowTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Prepara el entorno de la base de datos para cada prueba.
     */
    protected function setUp(): void
    {
        parent::setUp();
        // Ejecutamos los seeders básicos necesarios para crear usuarios.
        $this->seed(\Database\Seeders\UserRolesSeeder::class);
        $this->seed(\Database\Seeders\UserStateSeeder::class);
        $this->seed(\Database\Seeders\AvatarSeeder::class);
        $this->seed(\Database\Seeders\BannerSeeder::class);
    }

    /**
     * Prueba el flujo completo de una conversación de chat:
     * 1. Usuario A inicia un chat con Usuario B.
     * 2. Usuario A envía un mensaje.
     * 3. Usuario B responde al mensaje.
     */
    #[Test]
    public function it_simulates_a_full_chat_conversation(): void
    {
        // === ARRANGE: PREPARAR LOS PARTICIPANTES ===
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        // === PASO 1: Usuario A inicia una conversación con Usuario B ===
        $chatResponse = $this->actingAs($userA, 'api')
            ->postJson('/api/chats', [
                // Basado en tu CreateChatRequest, el campo es 'participants' y es un array.
                'participants' => [$userB->id],
            ]);
        
        // Verificamos que el chat se creó correctamente.
        // Tu controlador devuelve un Resource, que por defecto tiene un status 200.
        $chatResponse->assertStatus(201);
        
        // Extraemos el ID del chat creado para los siguientes pasos.
        $chatId = $chatResponse->json('data.id');

        // Verificamos que ambos usuarios están en la tabla pivote 'chat_users' asociados al nuevo chat.
        $this->assertDatabaseHas('chat_users', [
            'chat_id' => $chatId,
            'user_id' => $userA->id,
        ]);
        $this->assertDatabaseHas('chat_users', [
            'chat_id' => $chatId,
            'user_id' => $userB->id,
        ]);

        // === PASO 2: Usuario A envía el primer mensaje ===
        $firstMessage = '¡Hola, Usuario B!';
        $this->actingAs($userA, 'api')
            // El endpoint para enviar mensajes incluye el ID del chat en la URL.
            ->postJson("/api/chats/{$chatId}/messages", [
                'content_messages' => $firstMessage,
            ])
            ->assertStatus(201); // El controlador de mensajes devuelve 201 Created.

        // Verificamos que el mensaje se guardó correctamente en la base de datos.
        $this->assertDatabaseHas('messages', [
            'chat_id' => $chatId,
            'user_id' => $userA->id,
            'content_messages' => $firstMessage,
        ]);


        // === PASO 3: Usuario B responde al mensaje ===
        $replyMessage = '¡Hola, Usuario A! ¿Todo bien?';
        $this->actingAs($userB, 'api')
            ->postJson("/api/chats/{$chatId}/messages", [
                'content_messages' => $replyMessage,
            ])
            ->assertStatus(201);

        // Verificamos que la respuesta también se guardó en la base de datos.
        $this->assertDatabaseHas('messages', [
            'chat_id' => $chatId,
            'user_id' => $userB->id,
            'content_messages' => $replyMessage,
        ]);
    }
}