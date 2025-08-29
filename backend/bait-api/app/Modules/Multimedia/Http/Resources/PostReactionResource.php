<?php

namespace App\Modules\Multimedia\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\UserData\Http\Resources\UserResource;
use App\Modules\Multimedia\Http\Resources\ReactionTypeResource;

class PostReactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => new UserResource($this->whenLoaded('user')),
            'post_id' => $this->post_id,
            'reaction_type' => new ReactionTypeResource($this->whenLoaded('reactionType')),
            'created_at' => $this->created_at,
        ];
    }
}