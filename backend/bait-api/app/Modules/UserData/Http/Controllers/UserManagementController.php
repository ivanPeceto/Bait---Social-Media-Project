<?php

namespace App\Modules\UserData\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\UserData\Domain\Models\User;
use App\Modules\UserData\Domain\Models\UserState;
use App\Modules\UserData\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;

class UserManagementController extends Controller
{



    /**
     * @OA\Get(
     * path="/api/privileged/users",
     * summary="List all users (admin/moderator only)",
     * description="Returns a paginated list of all users in the system for the administration panel.",
     * tags={"User Management"},
     * security={{"bearerAuth":{}}},
     * @OA\Response(
     * response=200,
     * description="A paginated list of users.",
     * @OA\JsonContent(
     * type="array",
     * @OA\Items(ref="#/components/schemas/UserResource")
     * )
     * ),
     * @OA\Response(
     * response=401,
     * description="Unauthenticated"
     * ),
     * @OA\Response(
     * response=403,
     * description="Forbidden - Insufficient permissions"
     * )
     * )
     */
    public function index()
    {
        
        $users = User::with(['role', 'state', 'avatar', 'banner'])->latest()->paginate(20);

        // Devolvemos la colección de usuarios formateada con UserResource.
        return UserResource::collection($users);
    }


    /**
     * @OA\Post(
     *     path="/api/privileged/users/{user}/suspend",
     *     summary="Suspend a user (admin/moderator only)",
     *     description="Sets the state of the specified user to 'suspended'. Fails if the 'suspended' state is not defined in the database.",
     *     tags={"User Management"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         description="ID of the user to suspend",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="User successfully suspended",
     *         @OA\JsonContent(ref="#/components/schemas/UserSchema")
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - insufficient permissions"
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="The 'suspended' state is not defined in the database",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="'suspended' state not found in database."
     *             )
     *         )
     *     )
     * )
     */

    public function suspend(User $user): UserResource|JsonResponse
    {
        $suspendedState = UserState::where('name', 'suspended')->firstOrFail();

        if (!$suspendedState) {
            return response()->json(['message' => '"suspended" state not found in database.'], 500);
        }

        $user->update(['state_id' => $suspendedState->id]);

        return new UserResource($user->fresh(['role', 'state', 'avatar', 'banner']));
    }

    /**
     * @OA\Post(
     *     path="/api/privileged/users/{user}/activate",
     *     summary="Activate a user (admin/moderator only)",
     *     description="Sets the state of the specified user to 'active'. Fails if the 'active' state is not defined in the database.",
     *     tags={"User Management"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         description="ID of the user to activate",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="User successfully activated",
     *         @OA\JsonContent(ref="#/components/schemas/UserSchema")
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - insufficient permissions"
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="The 'active' state is not defined in the database",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="'active' state not found in database."
     *             )
     *         )
     *     )
     * )
     */


    public function activate(User $user): UserResource|JsonResponse
    {
        $activeState = UserState::where('name', 'active')->first();

        if (!$activeState) {
            return response()->json(['message' => '"Active" state not found in database.'], 500);
        }

        $user->update(['state_id' => $activeState->id]);

        return new UserResource($user->fresh(['role', 'state', 'avatar', 'banner']));
    }
}