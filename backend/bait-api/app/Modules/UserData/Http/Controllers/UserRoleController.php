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
    /**
     * @OA\Get(
     *     path="/api/roles",
     *     summary="Get all user roles",
     *     description="Returns a list of all user roles in the system.",
     *     tags={"User Roles"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="List of user roles",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/UserRoleSchema")
     *         )
     *     )
     * )
     */

    public function index(): JsonResource
    {
        $roles = UserRole::all();
        return UserRoleResource::collection($roles);
    }


    /**
     * @OA\Post(
     *     path="/api/roles",
     *     summary="Create a new user role",
     *     description="Creates a new role in the system.",
     *     tags={"User Roles"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="editor")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Role created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/UserRoleSchema")
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */

    public function create(CreateRoleRequest $request): UserRoleResource
    {
        $role = UserRole::create($request->validated());
        return new UserRoleResource($role);
    }


    /**
     * @OA\Put(
     *     path="/api/roles/{role}",
     *     summary="Update a user role",
     *     description="Updates an existing user role.",
     *     tags={"User Roles"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="role",
     *         in="path",
     *         description="ID of the role to update",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="manager")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Role updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/UserRoleSchema")
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Role not found"
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */

    public function update(UpdateRoleRequest $request, UserRole $role): UserRoleResource
    {
        $role->update($request->validated());
        
        return new UserRoleResource($role);
    }


    /**
     * @OA\Delete(
     *     path="/api/roles/{role}",
     *     summary="Delete a user role",
     *     description="Deletes an existing user role.",
     *     tags={"User Roles"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="role",
     *         in="path",
     *         description="ID of the role to delete",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=204,
     *         description="Role deleted successfully"
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Role not found"
     *     )
     * )
     */

    public function destroy(DeleteRoleRequest $request, UserRole $role): Response
    {
        $role->delete();
        return response()->noContent();
    }
}