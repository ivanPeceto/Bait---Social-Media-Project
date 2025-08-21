<?php

namespace App\Modules\UserData\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\UserData\Http\Requests\RegisterRequest;
use App\Modules\UserData\Services\AuthService;
use App\Modules\UserData\Http\Requests\LoginRequest;
use App\Modules\UserData\Http\Resources\UserResource;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTFactory;

class AuthController extends Controller
{
    public function __construct(private AuthService $auth) {}

    public function register(RegisterRequest $request) {
        $user = $this->auth->register($request->validated());
        $token = JWTAuth::fromUser($user);
        return response()->json([
            'user'  => new UserResource($user),
            'token' => $token,
            'type'  => 'bearer',
            'ttl'   => auth('api')->factory()->getTTL(),
        ], 201);
    }

    public function login(LoginRequest $request) {
        if (!$token = auth('api')->attempt($request->validated())) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }
        return response()->json([
            'user'  => new UserResource(auth('api')->user()),
            'token' => $token,
            'type'  => 'bearer',
            'ttl'   => auth('api')->factory()->getTTL(),
        ]);
    }

    public function me() {
        return new UserResource(auth('api')->user());
    }

    public function refresh() {
        return response()->json([
            'token' => auth('api')->refresh(),
            'type'  => 'bearer',
            'ttl'   => auth('api')->factory()->getTTL(),
        ]);
    }

    public function logout() {
        auth('api')->logout();
        return response()->json(['message' => 'Logged out']);
    }
}
