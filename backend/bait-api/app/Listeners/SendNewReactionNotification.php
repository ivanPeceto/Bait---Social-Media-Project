<?php

namespace App\Listeners;

use App\Events\NewReactionEvent;
use App\Notifications\NewReactionNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendNewReactionNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(NewReactionEvent $event): void
    {
        $event->reaction->load('post.user', 'user');

        $postOwner = $event->reaction->post->user;
        $reactingUser = $event->reaction->user;

        // Sends notification only if another user reacts to the post
        if ($postOwner->id !== $reactingUser->id) {
            $postOwner->notify(new NewReactionNotification($reactingUser, $event->reaction->post));
        }
    }
}
