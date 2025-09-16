<?php

use Illuminate\Support\Facades\Broadcast;

if (! config('broadcasting.connections.reverb.key')) {
    return;
}


Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
