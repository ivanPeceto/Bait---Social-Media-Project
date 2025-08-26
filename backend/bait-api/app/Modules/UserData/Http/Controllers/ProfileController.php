<?php

namespace App\Modules\UserData\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\UserData\Http\Requests\User\UpdateProfileRequest;
use App\Modules\UserData\Http\Requests\User\ChangePasswordRequest;
use App\Modules\UserData\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use PHPOpenSourceSaver\JWTAuth\JWTGuard;

class ProfileController extends Controller
{
    private $guard;

    public function __construct()
    {
        /** @var JWTGuard $guard */
        $this->guard = auth('api');
    }

    public function me()
    {
        return new UserResource($this->guard->user());
    }

    public function update(UpdateProfileRequest $request)
    {
        /** @var \App\Models\User $user */
        $user = $this->guard->user();
        $user->update($request->validated());

        return new UserResource($user->fresh());
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        /** @var \App\Models\User $user */
        $user = $this->guard->user();

        if (! Hash::check($request->validated()['current_password'], $user->password)) {
            return response()->json(['message' => 'Current password is incorrect'], 422);
        }

        $user->update([
            'password' => Hash::make($request->validated()['new_password']),
        ]);

        return response()->json(['message' => 'Password updated successfully']);
    }

}
