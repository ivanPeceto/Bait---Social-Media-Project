<?php

namespace App\Docs;

/**
 * @OA\Schema(
 *     schema="RepostSchema",
 *     title="Repost Schema",
 *     description="Represents a repost of an existing post by a user.",
 *     type="object",
 *     required={"id", "user_id", "post_id"},
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="user_id", type="integer", example=5),
 *     @OA\Property(property="post_id", type="integer", example=12),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-10-06T14:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-10-06T14:00:00Z")
 * )
 */


class RepostSchema
{

}
