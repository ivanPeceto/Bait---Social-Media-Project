<?php

namespace App\Modules\UserData\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class  BannerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $banner_path = $this->url_avatar ?? 'banner/default.jpg';

        if (!Storage::disk('public')->exists($banner_path)){
            $banner_path = 'banner/default.jpg';
        }

        return [
            'id' => $this->id,
            'url_banner' => Storage::url($banner_path),
            ];
    }
}
