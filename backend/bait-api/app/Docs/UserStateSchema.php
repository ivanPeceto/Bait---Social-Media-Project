<?php

namespace App\Docs;

/**
 * @OA\Schema(
 *     schema="UserStateSchema",
 *     type="object",
 *     title="User State Schema",
 *     required={"id", "name"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="active")
 * )
 */
class UserStateSchema
{
    
}
