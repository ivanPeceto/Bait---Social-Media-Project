<?php

namespace App\Modules\UserData\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class SearchResource extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => UserResource::collection($this->collection),
            'pagination' => [
                'total' => $this->total(),
                'per_page' => $this->perPage(),
                'current_page' => $this->currentPage(),
                'last_page' => $this->lastPage(),
                'from' => $this->firstItem(),
                'to' => $this->lastItem(),
            ],
        ];
    }
}
