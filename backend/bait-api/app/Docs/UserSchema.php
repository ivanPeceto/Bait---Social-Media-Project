<?php

namespace App\Docs;

/**
 * @OA\Schema(
 *     schema="UserSchema",
 *     type="object",
 *     title="User Schema",
 *     required={"id", "username", "name", "email"},
 *     @OA\Property(property="id", type="integer", example=123),
 *     @OA\Property(property="username", type="string", example="johndoe"),
 *     @OA\Property(property="name", type="string", example="John Doe"),
 *     @OA\Property(property="email", type="string", format="email", example="john@example.com"),
 *     @OA\Property(
 *         property="role",
 *         type="string",
 *         example="admin",
 *         description="User's role name",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="state",
 *         type="string",
 *         example="active",
 *         description="User's state name",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="avatar",
 *         ref="#/components/schemas/AvatarSchema",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="banner",
 *         ref="#/components/schemas/BannerSchema",
 *         nullable=true
 *     ),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-10-05T12:34:56Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-10-06T12:34:56Z")
 * )
 */
class UserSchema
{

}
