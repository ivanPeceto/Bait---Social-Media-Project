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
    /**
     * @OA\Get(
     *     path="/api/comments",
     *     summary="List all comments",
     *     description="Returns a list of all comments with associated user info.",
     *     tags={"Comments"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/CommentSchema")
     *         )
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        $comments = Comment::with('user')->get();
        return response()->json(CommentResource::collection($comments));
    }


    /**
     * @OA\Post(
     *     path="/api/comments",
     *     summary="Create a new comment",
     *     description="Creates a new comment on a post. Authenticated users only.",
     *     tags={"Comments"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"content_comments", "post_id"},
     *             @OA\Property(property="content_comments", type="string", example="Nice post!"),
     *             @OA\Property(property="post_id", type="integer", example=42)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Comment created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/CommentSchema")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed"
     *     )
     * )
     */
    public function store(CreateCommentRequest $request): JsonResponse
    {
        $comment = Comment::create([
            'content_comments' => $request->validated('content_comments'),
            'user_id' => auth()->id(),
            'post_id' => $request->validated('post_id'),
        ]);

        return response()->json(new CommentResource($comment), 201);
    }


    /**
     * @OA\Get(
     *     path="/api/comments/{comment}",
     *     summary="Get a specific comment",
     *     description="Returns the details of a single comment.",
     *     tags={"Comments"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="comment",
     *         in="path",
     *         required=true,
     *         description="ID of the comment to retrieve",
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Comment found",
     *         @OA\JsonContent(ref="#/components/schemas/CommentSchema")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Comment not found"
     *     )
     * )
     */
    public function show(Comment $comment): JsonResponse
    {
        return response()->json(new CommentResource($comment));
    }


    /**
     * @OA\Put(
     *     path="/api/comments/{comment}",
     *     summary="Update a comment",
     *     description="Allows the comment author to update their comment.",
     *     tags={"Comments"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="comment",
     *         in="path",
     *         required=true,
     *         description="ID of the comment to update",
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"content_comments"},
     *             @OA\Property(property="content_comments", type="string", example="Updated comment content")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Comment updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/CommentSchema")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized action"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function update(UpdateCommentRequest $request, Comment $comment): JsonResponse
    {
        if ($request->user()->id !== $comment->user_id) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $comment->update($request->validated());
        return response()->json(new CommentResource($comment));
    }


    /**
     * @OA\Delete(
     *     path="/api/comments/{comment}",
     *     summary="Delete a comment",
     *     description="Allows the comment author to delete their comment.",
     *     tags={"Comments"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="comment",
     *         in="path",
     *         required=true,
     *         description="ID of the comment to delete",
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=204,
     *         description="Comment deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized action"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Comment not found"
     *     )
     * )
     */
    public function destroy(Comment $comment): JsonResponse
    {
        if (auth()->id() !== $comment->user_id) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $comment->delete();
        return response()->json(null, 204);
    }
}
