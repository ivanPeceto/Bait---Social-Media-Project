<?php

namespace App\Modules\UserInteractions\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\UserData\Http\Resources\UserResource;

/**
 * @OA\Schema(
 * schema="ChatResource",
 * title="Chat Resource",
 * description="Represents a chat conversation.",
 * @OA\Property(property="id", type="integer", description="The chat's unique identifier.", example=1),
 * @OA\Property(
 * property="participants",
 * type="array",
 * description="List of users participating in the chat.",
 * @OA\Items(ref="#/components/schemas/UserResource")
 * ),
 * @OA\Property(
 * property="last_message",
 * ref="#/components/schemas/MessageResource",
 * nullable=true,
 * description="The most recent message in the chat."
 * ),
 * @OA\Property(
 * property="messages",
 * type="array",
 * description="A collection of messages in the chat (often included in detailed views).",
 * @OA\Items(ref="#/components/schemas/MessageResource")
 * ),
 * @OA\Property(property="created_at", type="string", format="date-time", description="Timestamp when the chat was created.", example="2025-10-05T10:00:00.000000Z")
 * )
 */
class ChatResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'participants' => UserResource::collection($this->whenLoaded('users')),
            'last_message' => $this->whenLoaded('messages', function () {
                $lastMessage = $this->messages->sortByDesc('created_at')->first();
                return $lastMessage ? new MessageResource($lastMessage) : null;
            }),
            'messages' => MessageResource::collection($this->whenLoaded('messages')),
            'created_at' => $this->created_at,
        ];
    }
}