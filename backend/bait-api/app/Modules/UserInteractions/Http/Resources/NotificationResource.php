<?php

namespace App\Modules\UserInteractions\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\UserData\Http\Resources\UserResource;

class NotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type_notifications,
            'content' => $this->content_notifications,
            'is_read' => $this->is_read_notifications,
            'user' => new UserResource($this->user),
            'created_at' => $this->created_at,
        ];
    }
}