<?php

namespace App\Modules\UserInteractions\Http\Controllers;

use App\Events\UserFollowed;
use App\Http\Controllers\Controller;
use App\Modules\UserData\Domain\Models\User;
use App\Modules\UserData\Http\Resources\UserResource;
use App\Modules\UserInteractions\Domain\Models\Follow;
use App\Notifications\NewFollowNotification;
use App\Modules\UserInteractions\Http\Requests\Follow\CreateFollowRequest;
use Illuminate\Http\JsonResponse;
use App\Modules\UserInteractions\Http\Requests\Follow\DestroyFollowRequest;
use App\Modules\UserInteractions\Http\Resources\FollowResource;

class FollowController extends Controller
{
    /**
     * @OA\Post(
     * path="/api/follows",
     * operationId="followUser",
     * tags={"Follows"},
     * summary="Follow another user",
     * description="Creates a new follow relationship from the authenticated user to another user.",
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     * required=true,
     * description="Pass the ID of the user to follow.",
     * @OA\JsonContent(
     * required={"following_id"},
     * @OA\Property(property="following_id", type="integer", example=2, description="The ID of the user to be followed.")
     * )
     * ),
     * @OA\Response(
     * response=201,
     * description="Follow relationship created successfully",
     * @OA\JsonContent(ref="#/components/schemas/FollowSchema") 
     * ),
     * @OA\Response(response=401, description="Unauthenticated"),
     * @OA\Response(response=409, description="Conflict - Already following this user"),
     * @OA\Response(response=422, description="Unprocessable Entity (e.g., trying to follow oneself, validation error)")
     * )
     */
    public function store(CreateFollowRequest $request): JsonResponse
    {
        $follower = auth()->user();
        $followingId = $request->validated('following_id');

        // Prevents user from following itself
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
        
        $followedUser->notify(new NewFollowNotification($follower, $followedUser));
        
        //event(new UserFollowed($follower, $followedUser));

        return response()->json(['message' => 'User followed successfully.'], 201);
    }

    /**
     * @OA\Delete(
     * path="/api/follows",
     * operationId="unfollowUser",
     * tags={"Follows"},
     * summary="Unfollow a user",
     * description="Deletes an existing follow relationship.",
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     * required=true,
     * description="Pass the ID of the user to unfollow.",
     * @OA\JsonContent(
     * required={"following_id"},
     * @OA\Property(property="following_id", type="integer", example=2, description="The ID of the user to be unfollowed.")
     * )
     * ),
     * @OA\Response(
     * response=204,
     * description="Successful operation. No content."
     * ),
     * @OA\Response(response=401, description="Unauthenticated"),
     * @OA\Response(response=404, description="Not Found - The follow relationship does not exist"),
     * @OA\Response(response=422, description="Validation error")
     * )
     */
    public function destroy(DestroyFollowRequest $request): JsonResponse
    {
        $follow = Follow::where('follower_id', auth()->id())
            ->where('following_id', $request->validated('following_id'))
            ->firstOrFail();

        $follow->delete();
        return response()->json(null, 204);
    }


    /**
     * @OA\Get(
     * path="/api/users/{user}/followers",
     * summary="Get a user's followers",
     * description="Returns a paginated list of users who are following the specified user.",
     * tags={"Follows"},
     * security={{"bearerAuth":{}}},
     *
     * @OA\Parameter(
     * name="user",
     * in="path",
     * required=true,
     * description="ID of the user",
     * @OA\Schema(type="integer")
     * ),
     *
     * @OA\Response(
     * response=200,
     * description="A paginated list of followers.",
     * @OA\JsonContent(
     * type="array",
     * @OA\Items(ref="#/components/schemas/UserResource")
     * )
     * ),
     * @OA\Response(
     * response=404,
     * description="User not found"
     * )
     * )
     */
    public function getFollowers(User $user)
    {
        $followers = $user->followers()->paginate(20);
        return UserResource::collection($followers);
    }

    /**
     * @OA\Get(
     * path="/api/users/{user}/following",
     * summary="Get users a user is following",
     * description="Returns a paginated list of users that the specified user is following.",
     * tags={"Follows"},
     * security={{"bearerAuth":{}}},
     *
     * @OA\Parameter(
     * name="user",
     * in="path",
     * required=true,
     * description="ID of the user",
     * @OA\Schema(type="integer")
     * ),
     *
     * @OA\Response(
     * response=200,
     * description="A paginated list of users being followed.",
     * @OA\JsonContent(
     * type="array",
     * @OA\Items(ref="#/components/schemas/UserResource")
     * )
     * ),
     * @OA\Response(
     * response=404,
     * description="User not found"
     * )
     * )
     */
    public function getFollowing(User $user)
    {
        $following = $user->following()->paginate(20);
        return UserResource::collection($following);
    }
}