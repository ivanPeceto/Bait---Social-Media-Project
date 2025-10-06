<?php

namespace App\Docs;

/**
 * @OA\Schema(
 *     schema="AvatarSchema",
 *     type="object",
 *     title="Avatar Schema",
 *     required={"id", "url_avatars"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(
 *         property="url_avatars",
 *         type="string",
 *         format="url",
 *         example="https://example.com/storage/avatars/avatar1.jpg"
 *     )
 * )
 */
class AvatarSchema
{

}
