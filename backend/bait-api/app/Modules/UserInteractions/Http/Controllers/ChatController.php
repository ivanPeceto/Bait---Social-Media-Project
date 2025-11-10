<?php

namespace App\Modules\UserInteractions\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\UserInteractions\Domain\Models\Chat;
use App\Modules\UserInteractions\Http\Requests\Chat\CreateChatRequest;
use App\Modules\UserInteractions\Http\Resources\ChatResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Modules\UserData\Domain\Models\User; 
use App\Modules\UserData\Http\Resources\UserResource;

class ChatController extends Controller
{
    /**
     * @OA\Get(
     * path="/api/chats",
     * operationId="getUserChats",
     * tags={"Chats"},
     * summary="List all chats for the authenticated user",
     * description="Retrieves a list of chats where the authenticated user is a participant.",
     * security={{"bearerAuth":{}}},
     * @OA\Response(
     * response=200,
     * description="Successful operation",
     * @OA\JsonContent(
     * type="array",
     * @OA\Items(ref="#/components/schemas/ChatSchema")
     * )
     * ),
     * @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function index(): AnonymousResourceCollection|JsonResponse
    {
        $user = auth()->user();
        $chats = $user->chats()->with(['users.avatar', 'messages'])->latest()->get();
        return ChatResource::collection($chats);
    }

    /**
     * @OA\Post(
     * path="/api/chats",
     * operationId="createChat",
     * tags={"Chats"},
     * summary="Create a new chat",
     * description="Creates a new chat and adds the authenticated user and specified participants. Rejects if a chat with the exact same participants already exists.",
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     * required=true,
     * description="Array of participant user IDs to include in the chat.",
     * @OA\JsonContent(
     * required={"participants"},
     * @OA\Property(
     * property="participants",
     * type="array",
     * description="An array of user IDs.",
     * @OA\Items(type="integer", example=2)
     * )
     * )
     * ),
     * @OA\Response(
     * response=201,
     * description="Chat created successfully",
     * @OA\JsonContent(ref="#/components/schemas/ChatSchema")
     * ),
     * @OA\Response(
     * response=409,
     * description="Conflict - Chat already created"
     * ),
     * @OA\Response(response=401, description="Unauthenticated"),
     * @OA\Response(response=422, description="Validation Error")
     * )
     */
    public function store(CreateChatRequest $request): ChatResource|JsonResponse
    {
        /** @var User $user */
        $user = auth()->user();
        $participants = $request->validated('participants');
        $allParticipants = array_merge($participants, [$user->id]);
        $uniqueParticipants = array_unique($allParticipants);
        sort($uniqueParticipants);
        $participantCount = count($uniqueParticipants);

        // Buscamos chats potenciales: aquellos donde el usuario actual participa
        //    Y que tienen el MISMO NÚMERO de participantes.
        $potentialChats = $user->chats()
                               ->with('users:id') 
                               ->withCount('users')
                               ->where('users_count', $participantCount)
                               ->get();
        
        // Filtramos los chats para encontrar una coincidencia
        foreach ($potentialChats as $chat) {
            $chatUserIds = $chat->users->pluck('id')->all();
            sort($chatUserIds);
            
            if ($chatUserIds === $uniqueParticipants) {
                // Encontramos un chat existente con los mismos participantes
                return response()->json(['message' => 'Chat already created.'], 409);
            }
        }

        // Si no se encontró ningún chat, creamos uno nuevo
        $chat = Chat::create();
        
        // Attach all participants
        $chat->users()->attach($uniqueParticipants);

        $chat->load(['users.avatar', 'messages']);

        return new ChatResource($chat);
    }
    /**
     * @OA\Get(
     * path="/api/chats/{chat}",
     * operationId="getChatById",
     * tags={"Chats"},
     * summary="Get details of a specific chat",
     * description="Retrieves the details of a single chat, including participants and messages. Authenticated user must be a participant.",
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
     * @OA\JsonContent(ref="#/components/schemas/ChatSchema")
     * ),
     * @OA\Response(response=401, description="Unauthenticated"),
     * @OA\Response(response=403, description="Forbidden - User is not a participant of this chat"),
     * @OA\Response(response=404, description="Chat not found")
     * )
     */
    public function show(Chat $chat): ChatResource|JsonResponse
    {
        if (!$chat->users()->where('user_id', auth()->id())->exists()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $chat->load(['users.avatar', 'messages']);
        return new ChatResource($chat);
    }

    /**
     * @OA\Get(
     * path="/api/chats/chattable-users",
     * operationId="getChattableUsers",
     * tags={"Chats"},
     * summary="List users available to chat",
     * description="Retrieves a list of users that the authenticated user mutually follows (follows and is followed by).",
     * security={{"bearerAuth":{}}},
     * @OA\Response(
     * response=200,
     * description="Successful operation",
     * @OA\JsonContent(
     * type="array",
     * @OA\Items(ref="#/components/schemas/UserSchema")
     * )
     * ),
     * @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function getChattableUsers(): AnonymousResourceCollection|JsonResponse
    {
        /** @var User $user */
        $user = auth()->user();

        $followingIds = $user->following()->pluck('users.id');

        $mutuals = $user->followers()
                         ->whereIn('users.id', $followingIds)
                         ->with('avatar') 
                         ->get();

        return UserResource::collection($mutuals);
    }
}