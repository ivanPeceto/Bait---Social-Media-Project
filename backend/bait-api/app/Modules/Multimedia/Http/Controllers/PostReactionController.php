<?php

namespace App\Modules\Multimedia\Http\Controllers;

use App\Events\NewReactionEvent;
use App\Http\Controllers\Controller;
use App\Modules\Multimedia\Domain\Models\Post;
use App\Modules\Multimedia\Domain\Models\PostReaction;
use App\Modules\Multimedia\Http\Requests\Reaction\CreatePostReactionRequest;
use App\Modules\Multimedia\Http\Resources\PostReactionResource;
use Illuminate\Http\JsonResponse;

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
        $user_id = auth()->user()->id;
        $post_id = $request->validated('post_id');
        $reaction_type_id = $request->validated('reaction_type_id');
        $action = $request->action;

        if ($action === 'delete') {
            PostReaction::where('user_id', $user_id)
                        ->where('post_id', $post_id)
                        ->where('reaction_type_id', $reaction_type_id)
                        ->delete();
            return response()->json(['message' => 'Reaction removed successfully.'], 200);
        }

        if ($action === 'update') {
            $reaction = PostReaction::where('user_id', $user_id)
                                  ->where('post_id', $post_id)
                                  ->first();
            $reaction->update(['reaction_type_id' => $reaction_type_id]);
            return response()->json(new PostReactionResource($reaction->load('reactionType')), 200);
        }

        $reaction = PostReaction::create([
            'user_id' => $user_id,
            'post_id' => $post_id,
            'reaction_type_id' => $reaction_type_id,
        ]);

        $post = Post::findOrFail($post_id);

        event(new NewReactionEvent(auth()->user(), $post));

        return response()->json(new PostReactionResource($reaction->load('user', 'reactionType')), 201);
    }
}