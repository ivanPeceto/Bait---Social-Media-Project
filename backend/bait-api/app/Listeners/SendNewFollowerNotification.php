<?php

namespace App\Listeners;

use App\Events\UserFollowed;
use App\Notifications\NewFollowNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendNewFollowerNotification
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
    public function handle(UserFollowed $event): void
    {
        $event->followedUser->notify(new NewFollowNotification($event->follower));
    }
}
