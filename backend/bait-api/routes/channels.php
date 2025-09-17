<?php

use App\Modules\UserInteractions\Domain\Models\Chat;
use Illuminate\Support\Facades\Broadcast;


if (! config('broadcasting.connections.reverb.key')) {
    return;
}


Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chat.{chat_id}', function ($user, $chat_id){
    $chat = Chat::find($chat_id);
    return $chat && $chat->users->contains($user);
});