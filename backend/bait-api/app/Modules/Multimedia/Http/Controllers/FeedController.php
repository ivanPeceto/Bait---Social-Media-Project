<?php

namespace App\Modules\Multimedia\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Multimedia\Http\Resources\PostResource;
use App\Modules\UserData\Domain\Models\User;
use Illuminate\Http\Request;
use App\Modules\Multimedia\Domain\Models\Post;

class FeedController extends Controller
{
    /**
     * @OA\Get(
     * path="/api/feed",
     * summary="Get the user's personalized feed",
     * description="Returns a paginated list of posts from users that the authenticated user follows, including their own posts, sorted by most recent.",
     * tags={"Feed"},
     * security={{"bearerAuth":{}}},
     * @OA\Response(
     * response=200,
     * description="The user's feed retrieved successfully.",
     * @OA\JsonContent(
     * type="array",
     * @OA\Items(ref="#/components/schemas/PostSchema")
     * )
     * ),
     * @OA\Response(
     * response=401,
     * description="Unauthenticated"
     * )
     * )
     */
    public function __invoke(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        $followingIds = $user->following()->pluck('users.id');

        $feedUserIds = $followingIds->push($user->id);

        $feed = Post::whereIn('user_id', $feedUserIds)
                    ->with('user.avatar')
                    ->latest()
                    ->paginate(20);

        return PostResource::collection($feed);
    }
}