<?php

namespace App\Docs;

/**
 * @OA\Schema(
 * schema="CommentSchema",
 * type="object",
 * title="Comment Schema",
 * description="Represents a user comment on a post.",
 * @OA\Property(
 * property="id",
 * type="integer",
 * example=101
 * ),
 * @OA\Property(
 * property="content",
 * type="string",
 * example="This is a great post!"
 * ),
 * @OA\Property(
 * property="user",
 * ref="#/components/schemas/UserOnCommentSchema"
 * ),
 * @OA\Property(
 * property="created_at",
 * type="string",
 * format="date-time",
 * example="2025-10-06T14:00:00Z"
 * ),
 * @OA\Property(
 * property="updated_at",
 * type="string",
 * format="date-time",
 * example="2025-10-06T14:15:00Z"
 * )
 * )
 */
class CommentSchema
{
}