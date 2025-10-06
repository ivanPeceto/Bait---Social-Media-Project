<?php

namespace App\Docs;

/**
 * @OA\Schema(
 *     schema="UserRoleSchema",
 *     type="object",
 *     title="User Role Schema",
 *     required={"id", "name"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="admin")
 * )
 */
class UserRoleSchema
{
  
}
