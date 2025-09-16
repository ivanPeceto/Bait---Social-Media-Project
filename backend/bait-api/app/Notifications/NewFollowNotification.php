<?php

namespace App\Notifications;

use App\Modules\UserData\Domain\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewFollowNotification extends Notification implements ShouldBroadcast
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public User $follower)
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
    public function toArray(object $notifiable): array
    {
        return [
            'type_notifications' => 'new_follower',
            'content_notifications' => json_encode([
                'follower_id' => $this->follower->id,
                'follower_name' => $this->follower->name_users,
                'message' => "{$this->follower->name_users} ha comenzado a seguirte."
            ]),
        ]; 
    }

    public function toBroadcast(object $notifiable): array {
        return [
            'data' => [
                'follower_id' => $this->follower->id,
                'follower_name' => $this->follower->name_users,
                'message' => "{$this->follower->name_users} ha comenzado a seguirte."
            ]
        ];
    }
}
