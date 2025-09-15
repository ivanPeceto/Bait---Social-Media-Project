<?php

namespace App\Modules\UserInteractions\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\UserData\Http\Resources\UserResource;
use App\Modules\UserInteractions\Http\Resources\MessageResource;

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
                return optional($lastMessage)->toArray();
            }),
            'messages' => MessageResource::collection($this->whenLoaded('messages')),
            'created_at' => $this->created_at,
        ];
    }
}