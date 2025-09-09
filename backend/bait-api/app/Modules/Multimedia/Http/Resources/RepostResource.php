<?php

namespace App\Modules\Multimedia\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\UserData\Http\Resources\UserResource;
use App\Modules\Multimedia\Http\Resources\PostResource;

class RepostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => new UserResource($this->user),
            'post' => new PostResource($this->post),
            'created_at' => $this->created_at,
        ];
    }
}