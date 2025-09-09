<?php

namespace App\Modules\UserData\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class AvatarResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Corregido: columna y ruta por defecto a plural.
        $avatar_path = $this->url_avatars ?? 'avatars/default.jpg';

        if (!Storage::disk('public')->exists($avatar_path)) {
            $avatar_path = 'avatars/default.jpg';
        }

        return [
            'id' => $this->id,
            // Corregido: la clave de la respuesta también.
            'url_avatars' => Storage::url($avatar_path),
        ];
    }
}