<?php

namespace App\Modules\Multimedia\Http\Controllers;

use App\Events\NewReactionEvent;
use App\Http\Controllers\Controller;
use App\Modules\Multimedia\Domain\Models\Post;
use App\Modules\Multimedia\Domain\Models\PostReaction;
use App\Modules\Multimedia\Http\Requests\Reaction\CreatePostReactionRequest;
use App\Modules\Multimedia\Http\Resources\PostReactionResource;
use App\Notifications\NewReactionNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use App\Modules\Multimedia\Domain\Models\ReactionType;
use App\Modules\Multimedia\Http\Resources\ReactionTypeResource;
use Illuminate\Support\Facades\DB;

class PostReactionController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/post-reactions",
     *     summary="Add, update or delete a reaction to a post",
     *     description="Creates a new reaction, updates an existing one, or deletes it based on the 'action' parameter.",
     *     tags={"Post Reactions"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"post_id", "reaction_type_id"},
     *             @OA\Property(property="post_id", type="integer", example=12),
     *             @OA\Property(property="reaction_type_id", type="integer", example=3),
     *             @OA\Property(property="action", type="string", enum={"create", "update", "delete"}, example="create", description="Action to perform on the reaction")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Reaction created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/PostReactionSchema")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Reaction updated or deleted successfully",
     *         @OA\JsonContent(
     *             oneOf={
     *                 @OA\Schema(ref="#/components/schemas/PostReactionSchema"),
     *                 @OA\Schema(
     *                     @OA\Property(property="message", type="string", example="Reaction removed successfully.")
     *                 )
     *             }
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Post not found"
     *     )
     * )
     */
    
    public function store(CreatePostReactionRequest $request): JsonResponse
    {
        $user_id = auth()->id();
        $post_id = $request->validated('post_id');
        $reaction_type_id = $request->validated('reaction_type_id');
        $action = $request->action;

        if ($action === 'delete') {
            $reaction = PostReaction::where('user_id', $user_id)
                        ->where('post_id', $post_id)
                        ->where('reaction_type_id', $reaction_type_id)
                        ->first();
            if ($reaction) {
                $reaction_copy = $reaction;
                $reaction->delete();
                event(new NewReactionEvent($reaction_copy));
            }
            return response()->json(['message' => 'Reaction removed successfully.'], 200);
        }

        if ($action === 'update') {
            $reaction = PostReaction::where('user_id', $user_id)
                                  ->where('post_id', $post_id)
                                  ->first();
            $reaction->update(['reaction_type_id' => $reaction_type_id]);
            //event(new NewReactionEvent($reaction));
            return response()->json(new PostReactionResource($reaction->load('reactionType')), 200);
        }

        $reaction = PostReaction::create([
            'user_id' => $user_id,
            'post_id' => $post_id,
            'reaction_type_id' => $reaction_type_id,
        ]);

        $post = Post::findOrFail($post_id);

        event(new NewReactionEvent($reaction));

        return response()->json(new PostReactionResource($reaction->load('user', 'reactionType')), 201);
    }

    /**
     * @OA\Get(
     * path="/api/reaction-types",
     * summary="Get all available reaction types",
     * description="Returns a list of all reaction types (like, love, etc.) that can be used on posts.",
     * tags={"Reactions"},
     * security={{"bearerAuth":{}}},
     *
     * @OA\Response(
     * response=200,
     * description="List of reaction types retrieved successfully",
     * @OA\JsonContent(
     * type="array",
     * @OA\Items(ref="#/components/schemas/ReactionTypeResource")
     * )
     * )
     * )
     */
    public function index()
    {
        $types = Cache::remember('reaction_types', 3600, function () {
            return ReactionType::all();
        });
        return ReactionTypeResource::collection($types);
    }

    /**
     * @OA\Get(
     * path="/api/posts/{post}/reaction-summary",
     * summary="Get a summary of reactions for a post, grouped by type",
     * description="Returns a list of reaction types and the count for each on a specific post.",
     * tags={"Post Reactions"},
     * security={{"bearerAuth":{}}},
     *
     * @OA\Parameter(
     * name="post",
     * in="path",
     * required=true,
     * description="The ID of the post to get the reaction summary for.",
     * @OA\Schema(type="integer", example=12)
     * ),
     *
     * @OA\Response(
     * response=200,
     * description="Reaction summary retrieved successfully",
     * @OA\JsonContent(
     * type="array",
     * @OA\Items(
     * type="object",
     * @OA\Property(property="reaction_type_id", type="integer", example=1),
     * @OA\Property(property="name", type="string", example="like"),
     * @OA\Property(property="count", type="integer", example=10)
     * ),
     * example={
     * {"reaction_type_id": 1, "name": "like", "count": 10},
     * {"reaction_type_id": 2, "name": "love", "count": 5},
     * {"reaction_type_id": 3, "name": "haha", "count": 3}
     * }
     * )
     * ),
     *
     * @OA\Response(
     * response=404,
     * description="Post not found"
     * )
     * )
     */
    public function getReactionCountsByPost(Post $post): JsonResponse
    {
        $reactionCounts = PostReaction::where('post_id', $post->id)
            ->join('reaction_types', 'post_reactions.reaction_type_id', '=', 'reaction_types.id')
            ->select(
                'reaction_type_id',
                'reaction_types.name_reaction_types as name',
                DB::raw('COUNT(*) as count') 
            )
            ->groupBy('reaction_type_id', 'reaction_types.name_reaction_types')
            ->orderBy('count', 'desc') // Ordenamos por las más populares
            ->get();

        return response()->json($reactionCounts);
    }
}