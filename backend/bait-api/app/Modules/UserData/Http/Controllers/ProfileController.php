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
    public function show(): UserResource
    {
        /** @var User $user */
        $user = auth()->user();

        $user->load(['role', 'state', 'avatar', 'banner']);

        return new UserResource($user);
    }

    public function updateSelf(UpdateProfileRequest $request): UserResource
    {
        /** @var User $user */
        $user = auth()->user();
        $user->update($request->validated());

        return new UserResource($user->fresh(['role', 'state', 'avatar', 'banner']));
    }

    public function changeSelfPassword(ChangePasswordRequest $request)
    {
        /** @var User $user */
        $user = auth()->user();

        $user->update([
            'password' => Hash::make($request->validated('new_password')),
        ]);

        return response()->json(['message' => 'Password updated successfully']);
    }

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

    public function changeUserPassword(Request $request, User $user)
    {
        $request->validate(['new_password'=>'required|string|min:8']);

        $user->update(['password'=> Hash::make($request->input('new_password'))]);

        return response()->json(['message'=>"Password for user {$user->username} updated successfully."]);
    }
}