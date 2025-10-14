<?php

namespace App\Docs;

/**
 * @OA\Schema(
 *     schema="UserOnCommentSchema",
 *     type="object",
 *     title="User On Comment Schema",
 *     required={"id", "username", "name", "email"},
 *     @OA\Property(property="id", type="integer", example=123),
 *     @OA\Property(property="username", type="string", example="johndoe"),
 *     @OA\Property(property="name", type="string", example="John Doe"),
 *     @OA\Property(property="email", type="string", format="email", example="john@example.com"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-10-05T12:34:56Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-10-06T12:34:56Z")
 * )
 */
class UserOnCommentSchema
{

}
