<?php

namespace App\Events;

use App\Modules\Multimedia\Domain\Models\Post;
use App\Modules\Multimedia\Http\Resources\PostResource;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewPost implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Post $post;

    /**
     * Create a new event instance.
     */
    public function __construct(Post $post)
    {
        $this->post = $post;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $author = $this->post->user;

        $followers = $author->followers()->get();

        
        $channels = $followers->map(function ($follower) {
            return new PrivateChannel('App.Models.User.' . $follower->id);
        })->all();

        
        $channels[] = new PrivateChannel('App.Models.User.' . $author->id);

        return $channels;
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return (new PostResource($this->post->load('user.avatar')))->resolve();
    }

    /**
     * Name of the event to broadcast.
     */
    public function broadcastAs(): string
    {
        return 'NewPost';
    }
}