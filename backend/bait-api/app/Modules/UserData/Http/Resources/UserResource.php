<?php

namespace App\Modules\UserData\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->whenLoaded('role', fn() => $this->role->name),
            'state' => $this->whenLoaded('state', fn() => $this->state->name),
            'avatar' => new AvatarResource($this->whenLoaded('avatar')),
            'banner' => new BannerResource($this->whenLoaded('banner')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}