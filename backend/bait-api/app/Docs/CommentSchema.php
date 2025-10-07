<?php

namespace App\Docs;

/**
 * @OA\Schema(
 *     schema="CommentSchema",
 *     title="Comment Schema",
 *     description="Represents a user comment on a post.",
 *     type="object",
 *     required={"id", "content_comments", "post_id", "user_id"},
 *
 *     @OA\Property(property="id", type="integer", example=101),
 *     @OA\Property(property="content_comments", type="string", example="This is a great post!"),
 *     @OA\Property(property="post_id", type="integer", example=42),
 *     @OA\Property(property="user_id", type="integer", example=7),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-10-06T14:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-10-06T14:15:00Z")
 * )
 */

class CommentSchema
{

}
