<?php

namespace App\Docs;

/**
 * @OA\Schema(
 * schema="PaginatedMetaSchema",
 * type="object",
 * title="Paginated Meta Schema",
 * description="Defines the meta object for paginated responses.",
 * @OA\Property(property="current_page", type="integer", example=1),
 * @OA\Property(property="from", type="integer", example=1),
 * @OA\Property(property="last_page", type="integer", example=1),
 * @OA\Property(property="path", type="string", format="url", example="http://localhost:8001/api/chats/1/messages"),
 * @OA\Property(property="per_page", type="integer", example=20),
 * @OA\Property(property="to", type="integer", example=1),
 * @OA\Property(property="total", type="integer", example=1)
 * )
 */
class PaginatedMetaSchema
{
}