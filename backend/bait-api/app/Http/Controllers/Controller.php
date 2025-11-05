<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="API in Laravel",
 *      description="API documentation with Swagger in Laravel"
 * )
 *
 * @OA\Server(
 *      url="http://127.0.0.1:8001",
 *      description="Local server"
 * )
 *
 * @OA\SecurityScheme(
 *      securityScheme="bearerAuth",
 *      type="http",
 *      scheme="bearer",
 *      bearerFormat="JWT",
 *      description="Use a JWT token to authenticate"
 * )
 *
 * @OA\Tag(name="Health")
 * @OA\Tag(name="Auth")
 * @OA\Tag(name="Avatar")
 * @OA\Tag(name="Banner")
 * @OA\Tag(name="Profile")
 * @OA\Tag(name="Users")
 * @OA\Tag(name="User Roles")
 * @OA\Tag(name="User States")
 * @OA\Tag(name="Posts")
 * @OA\Tag(name="Reposts")
 * @OA\Tag(name="Post Reactions")
 * @OA\Tag(name="Comments")
 * @OA\Tag(name="Chats")
 * @OA\Tag(name="Follows")
 * @OA\Tag(name="Multimedia Content")
 * @OA\Tag(name="Messages")
 * @OA\Tag(name="Notifications")
 * @OA\Tag(name="Feed")
 * @OA\Tag(name="Multimedia Management", description="Privileged")
 * @OA\Tag(name="User Management", description="Privileged")
 */


class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
