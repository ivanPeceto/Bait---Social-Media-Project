<?php

namespace App\Modules\UserInteractions\Http\Controllers;

use App\Events\NewMessage;
use App\Http\Controllers\Controller;
use App\Modules\UserInteractions\Domain\Models\Message;
use App\Modules\UserInteractions\Domain\Models\Chat;
use App\Modules\UserInteractions\Http\Requests\Message\CreateMessageRequest;
use App\Modules\UserInteractions\Http\Resources\MessageResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MessageController extends Controller
{
    public function index(Chat $chat): AnonymousResourceCollection|JsonResponse
    {
        if (!$chat->users()->where('user_id', auth()->id())->exists()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $messages = $chat->messages()
            ->with('user')
            ->latest()
            ->paginate(20);
        return MessageResource::collection($messages);
    }

    public function store(Chat $chat, CreateMessageRequest $request): MessageResource|JsonResponse
    {
        if (!$chat->users()->where('user_id', auth()->id())->exists()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $message = Message::create([
            'content_messages' => $request->validated('content_messages'),
            'chat_id' => $chat->id,
            'user_id' => auth()->id(),
        ]);

        $message->load('user');

        broadcast(new NewMessage($message))->toOthers();

        return new MessageResource($message); 
    }
}