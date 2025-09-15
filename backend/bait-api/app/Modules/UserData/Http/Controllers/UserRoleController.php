<?php

namespace App\Modules\UserData\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\UserData\Domain\Models\UserRole;
use App\Modules\UserData\Http\Requests\UserRole\CreateRoleRequest;
use App\Modules\UserData\Http\Requests\UserRole\UpdateRoleRequest;
use App\Modules\UserData\Http\Requests\UserRole\DeleteRoleRequest;
use App\Modules\UserData\Http\Resources\UserRoleResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;

class UserRoleController extends Controller
{
    public function index(): JsonResource
    {
        $roles = UserRole::all();
        return UserRoleResource::collection($roles);
    }

    public function create(CreateRoleRequest $request): UserRoleResource
    {
        $role = UserRole::create($request->validated());
        return new UserRoleResource($role);
    }

    public function update(UpdateRoleRequest $request, UserRole $role): UserRoleResource
    {
        $role->update($request->validated());
        
        return new UserRoleResource($role);
    }

    public function destroy(DeleteRoleRequest $request, UserRole $role): Response
    {
        $role->delete();
        return response()->noContent();
    }
}