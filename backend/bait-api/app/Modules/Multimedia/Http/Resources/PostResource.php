<?php

namespace App\Modules\Multimedia\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\UserData\Http\Resources\UserResource;

class PostResource extends JsonResource
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
            'content_posts' => $this->content_posts,
            'user_id' => $this->user_id, 
            'user' => new UserResource($this->whenLoaded('user')),
            'reactions_count' => $this->whenCounted('reactions'),
            'comments_count' => $this->whenCounted('comments'),
            'reposts_count' => $this->whenCounted('reposts'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'is_liked_by_user' => (bool) $this->liked_by_user,
            'multimedia_contents' => MultimediaContentResource::collection($this->whenLoaded('multimedia_contents')),
            'type' => 'post',
        ];
    }
}