<?php

namespace App\Modules\UserData\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\UserData\Http\Requests\User\UpdateProfileRequest;
use App\Modules\UserData\Http\Requests\User\ChangePasswordRequest;
use App\Modules\UserData\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use App\Modules\UserData\Domain\Models\User;

class ProfileController extends Controller
{
    public function show(): UserResource
    {
        /** @var User $user */
        $user = auth()->user();

        $user->load(['role', 'state', 'avatar', 'banner']);

        return new UserResource($user);
    }

    public function update(UpdateProfileRequest $request): UserResource
    {
        /** @var User $user */
        $user = auth()->user();
        $user->update($request->validated());

        return new UserResource($user->fresh(['role', 'state', 'avatar', 'banner']));
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        /** @var User $user */
        $user = auth()->user();

        $user->update([
            'password' => Hash::make($request->validated('new_password')),
        ]);

        return response()->json(['message' => 'Password updated successfully']);
    }
}