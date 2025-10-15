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
 * @OA\Property(property="email", type="string", format="email", description="The user's email. Only visible to the owner of the profile.", example="john.doe@example.com"),
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
        return [
            'id' => $this->id,
            'username' => $this->username,
            'name' => $this->name,

            // --- INICIO DEL CAMBIO NECESARIO POR PRIVACIDAD ---
            // Usamos 'mergeWhen' para fusionar el campo 'email' en la respuesta
            // SOLO SI la condición es verdadera. La condición comprueba si el usuario
            // autenticado es el mismo que el del perfil que se está viendo.
            // Para todas las demás vistas (perfiles públicos), el email no aparecerá.
            $this->mergeWhen(auth()->check() && auth()->id() === $this->id, [
                'email' => $this->email,
            ]),
            // --- FIN DEL CAMBIO NECESARIO ---

            'role' => $this->whenLoaded('role', fn() => $this->role->name),
            'state' => $this->whenLoaded('state', fn() => $this->state->name),
            'avatar' => new AvatarResource($this->whenLoaded('avatar')),
            'banner' => new BannerResource($this->whenLoaded('banner')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
