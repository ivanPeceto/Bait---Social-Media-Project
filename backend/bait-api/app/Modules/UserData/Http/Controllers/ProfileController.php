<?php

namespace App\Modules\UserData\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\UserData\Http\Requests\User\UpdateProfileRequest;
use App\Modules\UserData\Http\Requests\User\ChangePasswordRequest;
use App\Modules\UserData\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use App\Modules\UserData\Domain\Models\User;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/profile/show",
     *     summary="Get the authenticated user's profile",
     *     description="Returns the authenticated user's profile with related role, state, avatar, and banner information.",
     *     tags={"Profile"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="User profile retrieved successfully",
     *         @OA\JsonContent(ref="#/components/schemas/UserSchema")
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - user not authenticated"
     *     )
     * )
     */

    public function show(): UserResource
    {
        /** @var User $user */
        $user = auth()->user();

        $user->load(['role', 'state', 'avatar', 'banner']);

        return new UserResource($user);
    }


    /**
     * @OA\Put(
     *     path="/api/profile/update",
     *     summary="Update the authenticated user's profile",
     *     description="Updates the authenticated user's profile fields. Only fields allowed by validation will be updated.",
     *     tags={"Profile"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="username", type="string", example="johndoe"),
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(
     *                 property="state_id",
     *                 type="integer",
     *                 example=2,
     *                 description="ID of the new state to assign to the user"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="User profile updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/UserSchema")
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - user not authenticated"
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */

    public function updateSelf(UpdateProfileRequest $request): UserResource
    {
        /** @var User $user */
        $user = auth()->user();
        $user->update($request->validated());

        return new UserResource($user->fresh(['role', 'state', 'avatar', 'banner']));
    }


    /**
     * @OA\Put(
     *     path="/api/profile/password",
     *     summary="Change the authenticated user's password",
     *     description="Allows the authenticated user to change their password by providing the current and new passwords.",
     *     tags={"Profile"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"current_password", "new_password", "new_password_confirmation"},
     *             @OA\Property(
     *                 property="current_password",
     *                 type="string",
     *                 format="password",
     *                 example="secret123",
     *                 description="The user's current password"
     *             ),
     *             @OA\Property(
     *                 property="new_password",
     *                 type="string",
     *                 format="password",
     *                 example="secret456",
     *                 description="The new password the user wants to set"
     *             ),
     *             @OA\Property(
     *                 property="new_password_confirmation",
     *                 type="string",
     *                 format="password",
     *                 example="secret456",
     *                 description="Confirmation of the new password"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Password updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Password updated successfully"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - user not authenticated"
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed (e.g., current password incorrect, password too weak)"
     *     )
     * )
     */

    public function changeSelfPassword(ChangePasswordRequest $request)
    {
        /** @var User $user */
        $user = auth()->user();

        $user->update([
            'password' => Hash::make($request->validated('new_password')),
        ]);

        return response()->json(['message' => 'Password updated successfully']);
    }


    /**
     * @OA\Put(
     *     path="/api/privileged/users/{user}/update",
     *     summary="Update a user's profile (admin/moderator only)",
     *     description="Allows admins and moderators to update a user's profile. Moderators can only update limited fields (e.g., name).",
     *     tags={"User Management"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         description="ID of the user to update",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="username", type="string", example="johndoe"),
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(
     *                 property="state_id",
     *                 type="integer",
     *                 example=2,
     *                 description="ID of the new state for the user"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="User updated successfully",
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
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */

    public function updateUser(UpdateProfileRequest $request, User $user): UserResource
    {
        /** @var User $actingUser */
        $actingUser = auth()->user();
        
        $data = $request->validated();

        //Limita el acceso a los campos que puede editar un moderador
        if($actingUser->role && $actingUser->role->name === 'moderator'){
            $allowedFields = ['name'];
            $data = array_intersect_key($data, array_flip($allowedFields));
        }

        $user->update($data);

        return new UserResource($user->fresh(['role', 'state', 'avatar', 'banner']));
    }


    /**
     * @OA\Put(
     *     path="/api/privileged/users/{user}/password",
     *     summary="Change password for a specified user (admin/moderator only)",
     *     description="Allows admins or moderators to update the password of a specified user.",
     *     tags={"User Management"},
     *     security={{"bearerAuth":{}}},
     * 
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         description="ID of the user whose password is to be changed",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     * 
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"new_password"},
     *             @OA\Property(
     *                 property="new_password",
     *                 type="string",
     *                 format="password",
     *                 example="newStrongPassword123",
     *                 description="The new password for the user (min 8 characters)"
     *             )
     *         )
     *     ),
     * 
     *     @OA\Response(
     *         response=200,
     *         description="Password updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Password for user johndoe updated successfully."
     *             )
     *         )
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
     *         response=422,
     *         description="Validation error - e.g., password too short"
     *     )
     * )
     */

    public function changeUserPassword(Request $request, User $user)
    {
        $request->validate(['new_password'=>'required|string|min:8']);

        $user->update(['password'=> Hash::make($request->input('new_password'))]);

        return response()->json(['message'=>"Password for user {$user->username} updated successfully."]);
    }
}