<?php

namespace App\Modules\UserInteractions\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\UserData\Http\Resources\UserResource;

/**
 * @OA\Schema(
 * schema="MessageResource",
 * title="Message Resource",
 * description="Represents a message within a chat.",
 * @OA\Property(property="id", type="integer", description="The message's unique identifier.", example=101),
 * @OA\Property(property="content_messages", type="string", description="The text content of the message.", example="Hello, how are you?"),
 * @OA\Property(property="user", ref="#/components/schemas/UserResource", description="The user who sent the message."),
 * @OA\Property(property="created_at", type="string", format="date-time", description="Timestamp when the message was sent.", example="2025-10-05T12:30:00.000000Z")
 * )
 */
class MessageResource extends JsonResource
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
            'content_messages' => $this->content_messages,
            'user' => new UserResource($this->whenLoaded('user')),
            'created_at' => $this->created_at,
        ];
    }
}