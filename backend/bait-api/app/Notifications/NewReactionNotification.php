<?php

namespace App\Notifications;

use App\Modules\Multimedia\Domain\Models\Post;
use App\Modules\Multimedia\Domain\Models\ReactionType;
use App\Modules\UserData\Domain\Models\User;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Notification;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Notifications\Messages\BroadcastMessage;

class NewReactionNotification extends Notification implements ShouldBroadcast
{
    /**
     * Create a new notification instance.
     */
    public function __construct(public User $user, public Post $post)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the array representation of the notification for the database.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        // Devuelve solo el contenido. El "type" se manejará automáticamente.
        return [
            'follower_id' => $this->user->id,
            'follower_name' => $this->user->name,
            'message' => "{$this->user->name} reaccionó a tu publicación."
        ]; 
    }


    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        \Log::info('📢 Broadcasting NewReactionNotification para usuario ' . $notifiable->id);

        return new BroadcastMessage([
            'id' => (string) \Str::uuid(),
            'type' => static::class,
            'data' => [
                'user_id' => $this->user->id,
                'post_id' => $this->post->id,
                'message' => "{$this->user->name} reaccionó a tu publicación."
            ],
            'read_at' => null,
            'created_at' => now()->toISOString(),
        ]);
    }

    public function broadcastOn(): PrivateChannel
    {
        $userId = $this->post->user->id;

        \Log::info('➡️ Canal broadcast: users.' . $userId);

        return new PrivateChannel('users.' . $userId);
    }
    
}
