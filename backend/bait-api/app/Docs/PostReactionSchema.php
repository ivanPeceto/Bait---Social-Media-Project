<?php

namespace App\Docs;

/**
 * @OA\Schema(
 *     schema="PostReactionSchema",
 *     title="Post Reaction Schema",
 *     description="Represents a user's reaction to a post",
 *     type="object",
 *     required={"id", "user_id", "post_id", "reaction_type_id"},
 *
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         example=1,
 *         description="Unique identifier for the post reaction"
 *     ),
 *
 *     @OA\Property(
 *         property="user_id",
 *         type="integer",
 *         example=12,
 *         description="ID of the user who reacted"
 *     ),
 *
 *     @OA\Property(
 *         property="post_id",
 *         type="integer",
 *         example=34,
 *         description="ID of the post being reacted to"
 *     ),
 *
 *     @OA\Property(
 *         property="reaction_type_id",
 *         type="integer",
 *         example=2,
 *         description="ID representing the type of reaction (e.g., like, love, laugh)"
 *     ),
 *
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         example="2025-10-06T14:48:00Z"
 *     ),
 *
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         example="2025-10-06T14:50:00Z"
 *     ),
 *
 *     @OA\Property(
 *         property="reaction_type",
 *         type="object",
 *         nullable=true,
 *         description="Reaction type details",
 *         @OA\Property(property="id", type="integer", example=2),
 *         @OA\Property(property="name", type="string", example="like"),
 *         @OA\Property(property="icon", type="string", example="👍")
 *     ),
 *
 *     @OA\Property(
 *         property="user",
 *         type="object",
 *         nullable=true,
 *         description="User who reacted",
 *         @OA\Property(property="id", type="integer", example=12),
 *         @OA\Property(property="username", type="string", example="johndoe")
 *     )
 * )
 */

class PostReactionSchema 
{

}
