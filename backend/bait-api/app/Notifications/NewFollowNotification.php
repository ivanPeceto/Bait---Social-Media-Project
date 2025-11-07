<?php

namespace App\Notifications;

use App\Modules\UserData\Domain\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Notification;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Notifications\Messages\BroadcastMessage;

class NewFollowNotification extends Notification implements ShouldBroadcast
{
    use Queueable;

    public function __construct(public User $follower, public User $followedUser)
    {
    }

   
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    
    public function toDatabase(object $notifiable): array
    {
        return [
            'follower_id' => $this->follower->id,
            'follower_name' => $this->follower->name,
            'message' => "{$this->follower->name} ha comenzado a seguirte."
        ];
    }

    
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        \Log::info('📢 Broadcasting NewFollowNotification para usuario ' . $notifiable->id);

        return new BroadcastMessage([
            'id' => (string) \Str::uuid(),
            'type' => static::class,
            'data' => [
                'follower_id' => $this->follower->id,
                'follower_name' => $this->follower->name,
                'message' => "{$this->follower->name} ha comenzado a seguirte."
            ],
            'read_at' => null,
            'created_at' => now()->toISOString(),
        ]);
    }

    
    public function broadcastOn(): PrivateChannel
    {
        $userId = $this->followedUser->id;

        \Log::info('➡️ Canal broadcast: users.' . $userId);

        return new PrivateChannel('users.' . $userId);
    }
}
