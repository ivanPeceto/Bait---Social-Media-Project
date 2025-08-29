<?php

namespace App\Modules\Multimedia\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Multimedia\Domain\Models\PostReaction;
use App\Modules\Multimedia\Http\Requests\Reaction\CreatePostReactionRequest;
use App\Modules\Multimedia\Http\Resources\PostReactionResource;
use Illuminate\Http\JsonResponse;

class PostReactionController extends Controller
{
    /**
     * Store or update a reaction to a post.
     */
    public function store(CreatePostReactionRequest $request): JsonResponse
    {
        $user_id = auth()->id();
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

        return response()->json(new PostReactionResource($reaction->load('user', 'reactionType')), 201);
    }
}