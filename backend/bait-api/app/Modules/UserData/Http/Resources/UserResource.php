<?php

namespace App\Modules\UserData\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'username'      => $this->username,
            'name'        => $this->name,
            'email'       => $this->email,
            'role_id'        => $this->role_id,
            'state_id'       => $this->state_id,
            'role'  => $this->role?->name,
            'state' => $this->state?->name,
            'created_at'   => $this->created_at,
            'updated_at' => $this->updated_at
            ];
    }
}
