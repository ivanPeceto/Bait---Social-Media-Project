<?php

namespace App\Modules\UserInteractions\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\UserData\Http\Resources\UserResource;

/**
 * @OA\Schema(
 * schema="FollowResource",
 * title="Follow Resource",
 * description="Represents a follow relationship between two users.",
 * @OA\Property(property="id", type="integer", description="The unique identifier for the follow relationship.", example=1),
 * @OA\Property(property="follower", ref="#/components/schemas/UserResource", description="The user who is performing the follow action."),
 * @OA\Property(property="following", ref="#/components/schemas/UserResource", description="The user who is being followed."),
 * @OA\Property(property="created_at", type="string", format="date-time", description="Timestamp when the follow action occurred.", example="2025-10-04T16:28:00.000000Z")
 * )
 */
class FollowResource extends JsonResource
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
            'follower' => new UserResource($this->whenLoaded('follower')),
            'following' => new UserResource($this->whenLoaded('following')),
            'created_at' => $this->created_at,
        ];
    }
}