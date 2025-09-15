<?php

namespace Tests\Feature\UserInteractions;

use Tests\TestCase;
use App\Modules\UserData\Domain\Models\User;
use App\Modules\UserInteractions\Domain\Models\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Traits\WithUser;

class NotificationsTest extends TestCase
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
    public function a_user_can_view_their_notifications(): void
    {
        // Arrange
        [$user, $headers] = $this->actingAsUser();
        Notification::factory()->count(3)->create(['user_id' => $user->id]);
        Notification::factory()->count(2)->create(); // Notificaciones de otros usuarios

        // Act
        $response = $this->getJson('/api/notifications', $headers);

        // Assert
        $response->assertStatus(200)
                 ->assertJsonCount(3, 'data'); // Asegura que solo se devuelvan las notificaciones del usuario
    }

    /** @test */
    public function a_user_can_view_a_single_notification_they_own(): void
    {
        // Arrange
        [$user, $headers] = $this->actingAsUser();
        $notification = Notification::factory()->create(['user_id' => $user->id]);

        // Act
        $response = $this->getJson('/api/notifications/' . $notification->id, $headers);

        // Assert
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         'id',
                         'type',
                         'content',
                         'is_read',
                         'user' => ['id', 'username'],
                         'created_at',
                     ]
                 ]);
    }
    
    /** @test */
    public function a_user_cannot_view_a_notification_they_do_not_own(): void
    {
        // Arrange
        [$user, $headers] = $this->actingAsUser();
        $notification = Notification::factory()->create(); // Notificación de otro usuario

        // Act
        $response = $this->getJson('/api/notifications/' . $notification->id, $headers);

        // Assert
        $response->assertStatus(403);
    }

    /** @test */
    public function a_user_can_mark_a_notification_as_read(): void
    {
        // Arrange
        [$user, $headers] = $this->actingAsUser();
        $notification = Notification::factory()->create(['user_id' => $user->id, 'is_read_notifications' => false]);

        $updateData = [
            // Corregido: Se usa el nombre del campo del modelo
            'is_read_notifications' => true,
        ];

        // Act
        $response = $this->putJson('/api/notifications/' . $notification->id, $updateData, $headers);

        // Assert
        $response->assertStatus(200)
                 ->assertJson(['data' => ['is_read' => true]]);
        
        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id,
            'is_read_notifications' => true,
        ]);
    }
    
    /** @test */
    public function a_user_cannot_update_a_notification_they_do_not_own(): void
    {
        // Arrange
        [$user, $headers] = $this->actingAsUser();
        $notification = Notification::factory()->create(); // Notificación de otro usuario

        $updateData = [
            'is_read_notifications' => true,
        ];

        // Act
        $response = $this->putJson('/api/notifications/' . $notification->id, $updateData, $headers);

        // Assert
        $response->assertStatus(403);
    }

    /** @test */
    public function update_notification_request_validation(): void
    {
        // Arrange
        [$user, $headers] = $this->actingAsUser();
        $notification = Notification::factory()->create(['user_id' => $user->id, 'is_read_notifications' => false]);
        
        // Act (sin el campo 'is_read_notifications')
        $response1 = $this->putJson('/api/notifications/' . $notification->id, [], $headers);
        $response1->assertStatus(422)
                  ->assertJsonValidationErrors(['is_read_notifications']);

        // Act (con un valor no booleano)
        $response2 = $this->putJson('/api/notifications/' . $notification->id, ['is_read_notifications' => 'not_a_boolean'], $headers);
        $response2->assertStatus(422)
                  ->assertJsonValidationErrors(['is_read_notifications']);
    }
}