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
    /**
     * @OA\Get(
     * path="/api/chats/{chat}/messages",
     * operationId="getChatMessages",
     * tags={"Messages"},
     * summary="List messages in a specific chat",
     * description="Retrieves a paginated list of messages for a given chat. The authenticated user must be a participant of the chat.",
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="chat",
     * in="path",
     * required=true,
     * description="The ID of the chat.",
     * @OA\Schema(type="integer")
     * ),
     * @OA\Response(
     * response=200,
     * description="Successful operation",
     * @OA\JsonContent(
     * type="array",
     * @OA\Items(ref="#/components/schemas/MessageSchema")
     * )
     * ),
     * @OA\Response(response=401, description="Unauthenticated"),
     * @OA\Response(response=403, description="Forbidden - User is not a participant of this chat"),
     * @OA\Response(response=404, description="Chat not found")
     * )
     */
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

    /**
     * @OA\Post(
     * path="/api/chats/{chat}/messages",
     * operationId="sendMessageToChat",
     * tags={"Messages"},
     * summary="Send a new message to a chat",
     * description="Creates and broadcasts a new message in a chat. The authenticated user must be a participant of the chat.",
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="chat",
     * in="path",
     * required=true,
     * description="The ID of the chat where the message will be sent.",
     * @OA\Schema(type="integer")
     * ),
     * @OA\RequestBody(
     * required=true,
     * description="The content of the message.",
     * @OA\JsonContent(
     * required={"content_messages"},
     * @OA\Property(property="content_messages", type="string", maxLength=1000, example="This is a test message.")
     * )
     * ),
     * @OA\Response(
     * response=201,
     * description="Message created successfully",
     * @OA\JsonContent(ref="#/components/schemas/MessageSchema")
     * ),
     * @OA\Response(response=401, description="Unauthenticated"),
     * @OA\Response(response=403, description="Forbidden - User is not a participant of this chat"),
     * @OA\Response(response=404, description="Chat not found"),
     * @OA\Response(response=422, description="Validation error")
     * )
     */
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