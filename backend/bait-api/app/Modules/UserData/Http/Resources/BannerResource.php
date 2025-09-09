<?php

namespace App\Modules\UserData\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class BannerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Corregido: de 'url_avatar' a 'url_banners' y ruta por defecto
        $banner_path = $this->url_banners ?? 'banners/default.jpg';

        if (!Storage::disk('public')->exists($banner_path)) {
            $banner_path = 'banners/default.jpg';
        }

        return [
            'id' => $this->id,
            // Corregido: la clave ahora es 'url_banners'
            'url_banners' => Storage::url($banner_path),
        ];
    }
}