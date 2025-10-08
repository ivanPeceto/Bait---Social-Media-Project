<?php

namespace App\Notifications;

use App\Modules\UserData\Domain\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Notification;
use App\Notifications\Channels\CustomDatabaseChannel;


class NewFollowNotification extends Notification implements ShouldBroadcast
{
    use Queueable;

    protected $follower;
    /**
     * Create a new notification instance.
     */
    public function __construct(public User $follwr)
    {
        $this->follower = $follwr;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */

    public function via(object $notifiable): array
    {
        return [CustomDatabaseChannel::class, 'broadcast'];
    }


    /**
     * Get the array representation of the notification for the database.
     *
     * @return array<string, mixed>
     */
    public function toCustomDatabase(object $notifiable): array
    {
        return [
            'follower_id' => $this->follower->id,
            'follower_name' => $this->follower->name,
            'message' => "{$this->follower->name} ha comenzado a seguirte."
        ]; 
    }

    public function toBroadcast(object $notifiable): array {
        return [
            'data' => [
                'follower_id' => $this->follower->id,
                'follower_name' => $this->follower->name,
                'message' => "{$this->follower->name} ha comenzado a seguirte."
            ]
        ];
    }
}
