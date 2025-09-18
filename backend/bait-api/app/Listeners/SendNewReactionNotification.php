<?php

namespace App\Listeners;

use App\Events\NewReactionEvent;
use App\Notifications\NewReactionNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendNewReactionNotification
{
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
        $postOwner = $event->post->user;
        $reactingUser = $event->user;

        // Sends notification only if another user reacts to the post
        if ($postOwner->id !== $reactingUser->id) {
            $postOwner->notify(new NewReactionNotification($reactingUser, $event->post));
        }
    }
}
