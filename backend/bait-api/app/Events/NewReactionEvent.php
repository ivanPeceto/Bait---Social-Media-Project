<?php

namespace App\Events;

use App\Modules\Multimedia\Domain\Models\Post;
use App\Modules\Multimedia\Domain\Models\ReactionType;
use App\Modules\UserData\Domain\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Modules\Multimedia\Domain\Models\PostReaction;
use App\Modules\Multimedia\Http\Resources\PostResource;

class NewReactionEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $reaction;

    /**
     * Create a new event instance.
     */
    public function __construct(PostReaction $reaction)
    {
        $this->reaction = $reaction;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('post.' . $this->reaction->post_id),
        ];
    }

    /**
     * Name of the event to broadcast.
     */
    public function broadcastAs(): string
    {
        return 'PostReactionUpdated';
    }

    /**
     * Event's payload.
     * It sends the updated post.
     */
    public function broadcastWith(): array
    {
        $post = Post::withCount(['reactions', 'reposts', 'comments'])
                    ->find($this->reaction->post_id);

        return ['post' => new PostResource($post)];
    }
}
