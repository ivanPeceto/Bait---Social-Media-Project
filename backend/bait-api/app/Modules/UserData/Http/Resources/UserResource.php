<?php

namespace App\Modules\UserData\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 * schema="UserResource",
 * title="User Resource",
 * description="Represents a user in the system.",
 * @OA\Property(property="id", type="integer", example=1),
 * @OA\Property(property="name", type="string", example="John Doe"),
 * @OA\Property(property="username", type="string", example="johndoe"),
 * @OA\Property(property="email", type="string", format="email", description="The user's email. Only visible to the owner of the profile or to admins/moderators.", example="john.doe@example.com"),
 * @OA\Property(property="role", type="object", description="User's role information"),
 * @OA\Property(property="state", type="object", description="User's account state"),
 * @OA\Property(property="avatar", type="object", description="User's avatar information"),
 * @OA\Property(property="banner", type="object", description="User's profile banner information"),
 * @OA\Property(property="created_at", type="string", format="date-time", example="2025-10-04T16:28:00.000000Z"),
 * @OA\Property(property="updated_at", type="string", format="date-time", example="2025-10-04T16:30:00.000000Z")
 * )
 */

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Se obtiene el usuario autenticado una sola vez para eficiencia.
        $authenticatedUser = auth()->user();

        return [
            'id' => $this->id,
            'username' => $this->username,
            'name' => $this->name,

            // --- INICIO DE LA MODIFICACIÓN ---
            // Ahora, el campo 'email' se mostrará si se cumple CUALQUIERA de estas condiciones:
            // 1. El usuario autenticado es el dueño del perfil que se está viendo.
            // 2. El usuario autenticado es un 'admin'.
            // 3. El usuario autenticado es un 'moderator'.
            $this->mergeWhen($authenticatedUser && (
                $authenticatedUser->id === $this->id ||
                $authenticatedUser->role->name === 'admin' ||
                $authenticatedUser->role->name === 'moderator'
            ), [
                'email' => $this->email,
            ]),
            // --- FIN DE LA MODIFICACIÓN ---

            'followers_count' => $this->followers()->count(),
            'following_count' => $this->following()->count(),

            'role' => $this->whenLoaded('role', fn() => $this->role->name),
            'state' => $this->whenLoaded('state', fn() => $this->state->name),
            'avatar' => new AvatarResource($this->whenLoaded('avatar')),
            'banner' => new BannerResource($this->whenLoaded('banner')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}