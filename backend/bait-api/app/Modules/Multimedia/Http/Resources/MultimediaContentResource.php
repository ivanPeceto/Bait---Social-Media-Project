<?php

namespace App\Modules\Multimedia\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MultimediaContentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'url' => $this->url_multimedia_contents,
            'type' => $this->type_multimedia_contents,
            'created_at' => $this->created_at,
        ];
    }
}