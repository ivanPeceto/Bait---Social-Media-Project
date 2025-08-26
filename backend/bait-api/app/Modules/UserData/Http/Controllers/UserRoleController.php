<?php

namespace App\Modules\UserData\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\UserData\Domain\Models\UserRole;
use App\Modules\UserData\Http\Requests\UserRole\CreateRoleRequest;
use App\Modules\UserData\Http\Requests\UserRole\UpdateRoleRequest;
use App\Modules\UserData\Http\Resources\UserRoleResource;
use Illuminate\Http\JsonResponse;

class UserRoleController extends Controller
{
    public function index(): JsonResponse{
        $roles = UserRole::all();
        return response()->json(UserRoleResource::collection($roles), 200);
    }

    public function create(CreateRoleRequest $request): JsonResponse
    {
        $role = UserRole::create($request->validated());

        return response()->json([
            'message'=>'User role created successfully.',
            'data'=>$role,
        ], 201);
    }

    public function update(UpdateRoleRequest $request, UserRole $role): JsonResponse
    {
        $role->update($request->validated());

        return response()->json([
            'message' => 'User role updated successfully.',
            'data'=>$role,
        ], 200);
    }

    public function destroy(UserRole $role): JsonResponse{

        return response()->json([
            'message'=>'User role eliminated successfully',
        ], 200);
    }
}
