<?php

namespace App\Docs;

/**
 * @OA\Schema(
 *     schema="PostSchema",
 *     title="Post Schema",
 *     description="Schema representing a user's post.",
 *     type="object",
 *     required={"id", "content_posts", "user_id"},
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="content_posts", type="string", example="This is my post."),
 *     @OA\Property(property="user_id", type="integer", example=5),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-10-06T12:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-10-06T12:30:00Z")
 * )
 */

class PostSchema
{

}
