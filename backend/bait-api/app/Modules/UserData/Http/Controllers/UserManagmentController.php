<?php

namespace App\Http\Controllers;

use App\Modules\UserData\Domain\Models\User;
use App\Modules\UserData\Domain\Models\UserState;
use App\Modules\UserData\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;

class UserManagementController extends Controller
{
    public function suspend(User $user): UserResource|JsonResponse
    {
        $suspendedState = UserState::where('name', 'suspended')->first();

        if (!$suspendedState) {
            return response()->json(['message' => '"suspended" state not found in database.'], 500);
        }

        $user->update(['state_id' => $suspendedState->id]);

        return new UserResource($user->fresh(['role', 'state', 'avatar', 'banner']));
    }

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