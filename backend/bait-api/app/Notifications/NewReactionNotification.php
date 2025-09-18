<?php

namespace App\Notifications;

use App\Modules\Multimedia\Domain\Models\Post;
use App\Modules\Multimedia\Domain\Models\ReactionType;
use App\Modules\UserData\Domain\Models\User;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Notification;

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
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'type_notifications' => 'new_reaction',
            'content_notifications' => json_encode([
                'user_id' => $this->user->id,
                'post_id' => $this->post->id,
                'message' => "{$this->user->name} reaccionó a tu publicación."
            ]),
        ]; 
    }


    public function toBroadcast(object $notifiable): array {
        return [
            'data' => [
                'user_id' => $this->user->id,
                'post_id' => $this->post->id,
                'message' => "{$this->user->name} reaccionó a tu publicación."
            ]
        ];
    }
}
