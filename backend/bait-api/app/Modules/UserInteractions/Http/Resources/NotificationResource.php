<?php

namespace App\Modules\UserInteractions\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\UserData\Http\Resources\UserResource;

/**
 * @OA\Schema(
 * schema="NotificationResource",
 * title="Notification Resource",
 * description="Represents a user notification.",
 * @OA\Property(property="id", type="string", format="uuid", description="The notification's unique identifier.", example="9a7c132-45a6-41a7-9385-4f39b3385890"),
 * @OA\Property(property="type", type="string", description="The type of notification.", example="App\Notifications\NewFollowNotification"),
 * @OA\Property(property="content", type="object", description="The data associated with the notification.", example={"follower_name": "John Doe", "message": "John Doe is now following you."}),
 * @OA\Property(property="is_read", type="boolean", description="Indicates if the notification has been read.", example=false),
 * @OA\Property(property="user", ref="#/components/schemas/UserResource", description="The user who received the notification."),
 * @OA\Property(property="created_at", type="string", format="date-time", description="Timestamp when the notification was created.", example="2025-10-05T18:45:00.000000Z")
 * )
 */
class NotificationResource extends JsonResource
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
            'type' => $this->type, 
            'data' => $this->data,
            'read_at' => $this->read_at,
            'user' => new UserResource($this->whenLoaded('notifiable')),
            'created_at' => $this->created_at,
        ];
    }
}