<?php

namespace App\Docs;

/**
 * @OA\Schema(
 * schema="PaginatedLinksSchema",
 * type="object",
 * title="Paginated Links Schema",
 * description="Defines the links object for paginated responses.",
 * @OA\Property(property="first", type="string", format="url", example="http://localhost:8001/api/chats/1/messages?page=1"),
 * @OA\Property(property="last", type="string", format="url", example="http://localhost:8001/api/chats/1/messages?page=1"),
 * @OA\Property(property="prev", type="string", format="url", nullable=true, example=null),
 * @OA\Property(property="next", type="string", format="url", nullable=true, example=null)
 * )
 */
class PaginatedLinksSchema
{
}