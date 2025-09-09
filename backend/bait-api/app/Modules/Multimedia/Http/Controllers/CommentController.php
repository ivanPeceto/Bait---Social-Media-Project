<?php

namespace App\Modules\Multimedia\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Multimedia\Domain\Models\Comment;
use App\Modules\Multimedia\Http\Requests\Comment\CreateCommentRequest;
use App\Modules\Multimedia\Http\Requests\Comment\UpdateCommentRequest;
use App\Modules\Multimedia\Http\Resources\CommentResource;
use Illuminate\Http\JsonResponse;

class CommentController extends Controller
{
    public function index(): JsonResponse
    {
        $comments = Comment::with('user')->get();
        return response()->json(CommentResource::collection($comments));
    }

    public function store(CreateCommentRequest $request): JsonResponse
    {
        $comment = Comment::create([
            'content_comments' => $request->validated('content_comments'),
            'user_id' => auth()->id(),
            'post_id' => $request->validated('post_id'),
        ]);

        return response()->json(new CommentResource($comment), 201);
    }

    public function show(Comment $comment): JsonResponse
    {
        return response()->json(new CommentResource($comment));
    }

    public function update(UpdateCommentRequest $request, Comment $comment): JsonResponse
    {
        if ($request->user()->id !== $comment->user_id) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $comment->update($request->validated());
        return response()->json(new CommentResource($comment));
    }

    public function destroy(Comment $comment): JsonResponse
    {
        if (auth()->id() !== $comment->user_id) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $comment->delete();
        return response()->json(null, 204);
    }
}