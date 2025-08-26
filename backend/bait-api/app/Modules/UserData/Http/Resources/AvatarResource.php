<?php

namespace App\Modules\UserData\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class AvatarResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $avatar_path = $this->url_avatar ?? 'avatar/default.jpg';

        if (!Storage::disk('public')->exists($avatar_path)){
            $avatar_path = 'avatar/default.jpg';
        }

        return [
            'id' => $this->id,
            'url_avatar' => Storage::url($avatar_path),
            ];
    }
}
