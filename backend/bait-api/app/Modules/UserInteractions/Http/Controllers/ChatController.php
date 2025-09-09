<?php

namespace App\Modules\UserInteractions\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\UserInteractions\Domain\Models\Chat;
use App\Modules\UserInteractions\Http\Requests\Chat\CreateChatRequest;
use App\Modules\UserInteractions\Http\Resources\ChatResource;
use App\Modules\UserInteractions\Domain\Models\ChatUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ChatController extends Controller
{
    public function index(): AnonymousResourceCollection|JsonResponse
    {
        $user = auth()->user();
        $chats = $user->chats()->with('users', 'messages')->get();
        return ChatResource::collection($chats);
    }

    public function store(CreateChatRequest $request): ChatResource|JsonResponse
    {
        $participants = $request->validated('participants');
        
        $chat = Chat::create();
        
        $chat->users()->attach(auth()->id());
        
        $chat->users()->attach($participants);
        
        $chat->load('users', 'messages');

        return new ChatResource($chat); 
    }

    public function show(Chat $chat): ChatResource|JsonResponse
    {
        if (!$chat->users()->where('user_id', auth()->id())->exists()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $chat->load('users', 'messages');
        return new ChatResource($chat);
    }
}