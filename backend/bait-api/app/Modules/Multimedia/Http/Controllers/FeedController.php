<?php

namespace App\Modules\Multimedia\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Multimedia\Http\Resources\PostResource;
use App\Modules\Multimedia\Http\Resources\RepostResource;
use App\Modules\UserData\Domain\Models\User;
use Illuminate\Http\Request;
use App\Modules\Multimedia\Domain\Models\Post;
use App\Modules\Multimedia\Domain\Models\Repost;
use Illuminate\Http\JsonResponse; 
use Illuminate\Pagination\LengthAwarePaginator; 
use Illuminate\Pagination\Paginator; 
use Illuminate\Support\Facades\Auth;
use OpenApi\Attributes as OA;

/**
 * @OA\Schema(
 * schema="FeedItem",
 * oneOf={
 * @OA\Schema(ref="#/components/schemas/PostResource"),
 * @OA\Schema(ref="#/components/schemas/RepostResource")
 * }
 * )
 *
 * @OA\Schema(
 * schema="PaginatedFeedResponse",
 * properties={
 * @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/FeedItem")),
 * @OA\Property(property="links", ref="#/components/schemas/PaginatedLinksSchema"),
 * @OA\Property(property="meta", ref="#/components/schemas/PaginatedMetaSchema")
 * }
 * )
 */
class FeedController extends Controller
{
    /**
     * @OA\Get(
     * path="/api/feed",
     * summary="Get the user's personalized feed",
     * description="Returns a paginated list of posts AND reposts from users that the authenticated user follows, including their own posts, sorted by most recent.",
     * tags={"Feed"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="page",
     * in="query",
     * description="Número de página para la paginación.",
     * required=false,
     * @OA\Schema(type="integer", default=1)
     * ),
     * @OA\Parameter(
     * name="per_page",
     * in="query",
     * description="Resultados por página.",
     * required=false,
     * @OA\Schema(type="integer", default=15)
     * ),
     * @OA\Response(
     * response=200,
     * description="The user's feed retrieved successfully.",
     * @OA\JsonContent(ref="#/components/schemas/PaginatedFeedResponse")
     * ),
     * @OA\Response(
     * response=401,
     * description="Unauthenticated"
     * )
     * )
     */
    public function __invoke(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $followingIds = $user->following()->pluck('users.id');
        
        $reposts = Repost::whereIn('user_id', $followingIds)
            ->with([
                'user.avatar',
                'user.banner',
                'post.user.avatar', 
                'post.user.banner',
                'post.multimedia_contents',
                'post.reactions',
                'post.userReaction',
                'post' => function ($query) {
                    $query->withCount(['reactions', 'comments', 'reposts']);
                }
            ])
            ->get()
            ->map(function ($repost) {
                $repost->type = 'repost';
                return $repost;
            });

        $posts = Post::whereIn('user_id', $followingIds->push($user->id))
                    ->with([
                        'user.avatar',
                        'user.banner',
                        'multimedia_contents',
                        'reactions',
                        'userReaction'
                    ])
                    ->withCount(['reactions', 'comments', 'reposts'])
                    ->get()
                    ->map(function ($post) {
                        $post->type = 'post'; 
                        return $post;
                    });
        
        $feedItems = $posts->toBase()
            ->merge($reposts)
            ->sortByDesc('created_at');

        $page = Paginator::resolveCurrentPage('page', 1);
        $perPage = (int) $request->input('per_page', 15);
        $pagedItems = $feedItems->slice(($page - 1) * $perPage, $perPage)->values();

        $paginatedCollection = new LengthAwarePaginator(
            $pagedItems,
            $feedItems->count(),
            $perPage,
            $page,
            ['path' => Paginator::resolveCurrentPath()]
        );

        // Transformar la colección a sus Resources correspondientes
        $paginatedCollection->getCollection()->transform(function ($item) {
            if ($item instanceof Post) {
                return new PostResource($item);
            }
            if ($item instanceof Repost) {
                return new RepostResource($item);
            }
            return $item; 
        });

        return response()->json($paginatedCollection);
    }
}