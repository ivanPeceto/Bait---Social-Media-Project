<?php

namespace App\Listeners;

use App\Events\NewRepost;
use App\Notifications\NewRepostNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendNewRepostNotification
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
        $postOwner = $event->post->user;
        $repostingUser = $event->user;

        if($postOwner->id !== $repostingUser->id){
            $postOwner->notify(new NewRepostNotification($repostingUser, $event->post));
        }
    }
}
