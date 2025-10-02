<?php

namespace App\Modules\UserData\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\UserData\Http\Requests\User\RegisterRequest;
use App\Modules\UserData\Services\AuthService;
use App\Modules\UserData\Http\Requests\User\LoginRequest;
use App\Modules\UserData\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    public function __construct(private AuthService $authService)
    {
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $this->authService->register($request->validated());

        $credentials = $request->only('email', 'password');
        if (!$token = auth('api')->attempt($credentials)) {
            return response()->json(['message' => 'Registration succeeded but login failed.'], 500);
        }

        return $this->respondWithToken($token);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        if (!$token = auth('api')->attempt($request->validated())) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        return $this->respondWithToken($token);
    }

    public function me(): UserResource
    {
        $user = auth('api')->user();
        $user->load(['role', 'state', 'avatar', 'banner']);
        return new UserResource($user);
    }

    public function refresh(): JsonResponse
    {
        return $this->respondWithToken(auth('api')->refresh());
    }

    public function logout(): JsonResponse
    {
        auth('api')->logout();
        return response()->json(['message' => 'Successfully logged out']);
    }
    
    protected function respondWithToken(string $token): JsonResponse
    {
        $user = auth('api')->user();
        $user->load(['role', 'state', 'avatar', 'banner']);

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
            'user' => new UserResource($user)
        ]);
    }
}
