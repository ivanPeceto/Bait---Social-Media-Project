<?php

namespace App\Modules\UserData\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\UserData\Http\Requests\User\RegisterRequest;
use App\Modules\UserData\Services\AuthService;
use App\Modules\UserData\Http\Requests\User\LoginRequest;
use App\Modules\UserData\Http\Resources\UserResource;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use PHPOpenSourceSaver\JWTAuth\JWTGuard;

class AuthController extends Controller
{
    private $guard;

    public function __construct(private AuthService $auth) {
         /** @var JWTGuard $guard */ //Eliminates ugly intelephense problem >:(
        $this->guard = auth('api');
    }

    public function register(RegisterRequest $request) {
        $user = $this->auth->register($request->validated());
        $token = JWTAuth::fromUser($user);
        
        return response()->json([
            'user'  => new UserResource($user),
            'token' => $token,
            'type'  => 'bearer',
            'ttl'   => $this->guard->factory()->getTTL(),
        ], 201);
    }

    public function login(LoginRequest $request) {
        if (!$token = $this->guard->attempt($request->validated())) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }
        return response()->json([
            'user'  => new UserResource($this->guard->user()),
            'token' => $token,
            'type'  => 'bearer',
            'ttl'   => $this->guard->factory()->getTTL(),
        ]);
    }

    public function me() {
        return new UserResource($this->guard->user());
    }

    public function refresh() {
        return response()->json([
            'token' => $this->guard->refresh(),
            'type'  => 'bearer',
            'ttl'   => $this->guard->factory()->getTTL(),
        ]);
    }

    public function logout() {
        $this->guard->logout();
        return response()->json(['message' => 'Logged out']);
    }
}
