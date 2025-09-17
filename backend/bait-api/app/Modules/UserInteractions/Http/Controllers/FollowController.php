<?php

namespace App\Modules\UserInteractions\Http\Controllers;

use App\Events\UserFollowed;
use App\Http\Controllers\Controller;
use App\Modules\UserData\Domain\Models\User;
use App\Modules\UserInteractions\Domain\Models\Follow;
use App\Modules\UserInteractions\Http\Requests\Follow\CreateFollowRequest;
use Illuminate\Http\JsonResponse;
use App\Modules\UserInteractions\Http\Requests\Follow\DestroyFollowRequest;

class FollowController extends Controller
{
    public function store(CreateFollowRequest $request): JsonResponse
    {
        $follower = auth()->user();
        $followingId = $request->validated('following_id');

        // Prevents user from followin itself
        if ($follower->id == $followingId) {
            return response()->json(['message' => 'You cannot follow yourself.'], 422);
        }

        // Checks if it's already followed
        $existingFollow = Follow::where('follower_id', $follower->id)
            ->where('following_id', $followingId)
            ->first();

        if ($existingFollow) {
            return response()->json(['message' => 'Already following this user.'], 409);
        }

        Follow::create([
            'follower_id' => $follower->id,
            'following_id' => $followingId,
        ]);

        $followedUser = User::find($followingId);
        event(new UserFollowed($follower, $followedUser));

        return response()->json(['message' => 'User followed successfully.'], 201);
    }

    public function destroy(DestroyFollowRequest $request): JsonResponse
    {
        $follow = Follow::where('follower_id', auth()->id())
            ->where('following_id', $request->validated('following_id'))
            ->firstOrFail();

        $follow->delete();
        return response()->json(null, 204);
    }
}