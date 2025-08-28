<?php

namespace App\Modules\UserInteractions\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\UserInteractions\Domain\Models\Chat;
use App\Modules\UserInteractions\Http\Requests\Chat\CreateChatRequest;
use App\Modules\UserInteractions\Http\Resources\ChatResource;
use App\Modules\UserInteractions\Domain\Models\ChatUser;
use Illuminate\Http\JsonResponse;

class ChatController extends Controller
{
    /**
     * Display a listing of the chats for the authenticated user.
     */
    public function index(): JsonResponse
    {
        $user = auth()->user();
        $chats = $user->chats()->with('users')->withLastMessage()->get();
        return response()->json(ChatResource::collection($chats));
    }

    /**
     * Store a newly created chat in storage.
     */
    public function store(CreateChatRequest $request): JsonResponse
    {
        $participants = $request->validated('participants');
        
        $chat = Chat::create();
        
        // Agregar al usuario autenticado como participante
        $chat->users()->attach(auth()->id());
        
        // Agregar a los demás participantes
        $chat->users()->attach($participants);

        $chat->load('users');

        return response()->json(new ChatResource($chat), 201);
    }

    /**
     * Display the specified chat.
     */
    public function show(Chat $chat): JsonResponse
    {
        // Verificar si el usuario actual es un participante del chat
        if (!$chat->users()->where('user_id', auth()->id())->exists()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $chat->load('users', 'messages');
        return response()->json(new ChatResource($chat));
    }
}