<?php

namespace App\Listeners;

use App\Events\NewRepost;
use App\Notifications\NewRepostNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendNewRepostNotification implements ShouldQueue
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
    public function handle(NewRepost $event): void
    {
        $event->repost->load('post.user', 'user');

        $postOwner = $event->repost->post->user;
        $repostingUser = $event->repost->user;

        if($postOwner->id !== $repostingUser->id){
            $postOwner->notify(new NewRepostNotification($repostingUser, $event->repost->post));
        }
    }
}
