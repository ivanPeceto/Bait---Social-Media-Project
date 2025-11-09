<?php

use App\Modules\UserInteractions\Domain\Models\Chat;
use Illuminate\Support\Facades\Broadcast;


if (! config('broadcasting.connections.reverb.key')) {
    return;
}

Broadcast::routes(['middleware' => ['auth:api']]);

Broadcast::channel('users.{id}', function ($user, $id) {
    logger("Broadcast auth user:", ['user' => $user]);
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chats.{chat_id}', function ($user, $chat_id){
    $chat = Chat::find($chat_id);
    return $chat && $chat->users->contains($user);
});