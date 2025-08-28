<?php

namespace App\Modules\UserInteractions\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\UserInteractions\Domain\Models\Message;
use App\Modules\UserInteractions\Http\Requests\Message\CreateMessageRequest;
use App\Modules\UserInteractions\Http\Resources\MessageResource;
use Illuminate\Http\JsonResponse;

class MessageController extends Controller
{
    public function index(int $chatId): JsonResponse
    {
        $messages = Message::where('chat_id', $chatId)
            ->with('user')
            ->latest()
            ->paginate(20);
        return response()->json(MessageResource::collection($messages));
    }

    public function store(CreateMessageRequest $request): JsonResponse
    {
        $message = Message::create([
            'content_messages' => $request->validated('content'),
            'chat_id' => $request->validated('chat_id'),
            'user_id' => auth()->id(),
        ]);
        return response()->json(new MessageResource($message), 201);
    }
}