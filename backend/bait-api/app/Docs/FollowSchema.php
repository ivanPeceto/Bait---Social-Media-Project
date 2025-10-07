<?php

namespace App\Docs;

/**
 * @OA\Schema(
 * schema="FollowSchema",
 * type="object",
 * title="Follow Schema",
 * description="Represents a follow relationship between two users.",
 * @OA\Property(property="id", type="integer", description="The unique identifier for the follow relationship.", example=1),
 * @OA\Property(property="follower", ref="#/components/schemas/UserSchema", description="The user who is following."),
 * @OA\Property(property="following", ref="#/components/schemas/UserSchema", description="The user who is being followed."),
 * @OA\Property(
 * property="created_at",
 * type="string",
 * format="date-time",
 * description="Timestamp when the follow action occurred.",
 * example="2025-10-04T16:28:00.000000Z"
 * )
 * )
 */
class FollowSchema
{
}