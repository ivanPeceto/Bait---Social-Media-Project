<?php

namespace App\Modules\Multimedia\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Multimedia\Domain\Models\Post;
use App\Modules\Multimedia\Http\Requests\Post\CreatePostRequest;
use App\Modules\Multimedia\Http\Requests\Post\UpdatePostRequest;
use App\Modules\Multimedia\Http\Resources\PostResource;
use Illuminate\Http\JsonResponse;
use App\Events\NewPost;

class PostController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/posts",
     *     summary="List all posts",
     *     description="Returns a list of all posts with associated user info.",
     *     tags={"Posts"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="List of posts returned successfully",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/PostSchema")
     *         )
     *     )
     * )
     */

    public function index(): JsonResponse
    {
        $posts = Post::with('user.avatar', 'multimedia_contents')
                    ->withCount(['reactions', 'comments', 'reposts'])
                    ->latest()
                    ->get();
        return response()->json(PostResource::collection($posts));
    }


    /**
     * @OA\Post(
     *     path="/api/posts",
     *     summary="Create a new post",
     *     description="Authenticated users can create a new post.",
     *     tags={"Posts"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"content_posts"},
     *             @OA\Property(property="content_posts", type="string", example="This is a new post.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Post created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/PostSchema")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    
    public function store(CreatePostRequest $request): JsonResponse
    {
        $post = Post::create([
            'content_posts' => $request->validated('content_posts'),
            'user_id' => auth()->id(),
        ]);

        event(new NewPost($post));
        return response()->json(new PostResource($post), 201);
    }


    /**
     * @OA\Get(
     *     path="/api/posts/{post}",
     *     summary="Retrieve a specific post",
     *     description="Returns the details of a single post.",
     *     tags={"Posts"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="post",
     *         in="path",
     *         required=true,
     *         description="ID of the post",
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Post retrieved successfully",
     *         @OA\JsonContent(ref="#/components/schemas/PostSchema")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Post not found"
     *     )
     * )
     */

    public function show(Post $post): JsonResponse
    {
        return response()->json(new PostResource($post));
    }

    
    /**
     * @OA\Put(
     *     path="/api/posts/{post}",
     *     summary="Update a post",
     *     description="Only the author of the post can update it.",
     *     tags={"Posts"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="post",
     *         in="path",
     *         required=true,
     *         description="ID of the post",
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"content_posts"},
     *             @OA\Property(property="content_posts", type="string", example="Updated post content.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Post updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/PostSchema")
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

    public function update(UpdatePostRequest $request, Post $post): JsonResponse
    {
        if ($request->user()->id !== $post->user_id) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $post->update($request->validated());
        return response()->json(new PostResource($post));
    }


    /**
     * @OA\Delete(
     *     path="/api/posts/{post}",
     *     summary="Delete a post",
     *     description="Only the author of the post can delete it.",
     *     tags={"Posts"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="post",
     *         in="path",
     *         required=true,
     *         description="ID of the post",
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=204,
     *         description="Post deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized action"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Post not found"
     *     )
     * )
     */

    public function destroy(Post $post): JsonResponse
    {
        if (auth()->id() !== $post->user_id){
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }
        
        $post->delete();
        return response()->json(null, 204);
    }


    /**
     * @OA\Get(
     * path="/api/posts/{post}/user-reaction",
     * summary="Check user's reaction to a post",
     * description="Checks if the currently authenticated user has reacted to a specific post, and if so, which reaction they used.",
     * tags={"Posts"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="post",
     * in="path",
     * required=true,
     * description="ID of the post to check",
     * @OA\Schema(type="integer")
     * ),
     * @OA\Response(
     * response=200,
     * description="Returns the user's reaction status.",
     * @OA\JsonContent(
     * type="object",
     * @OA\Property(property="has_reacted", type="boolean", example=true),
     * @OA\Property(property="reaction_type_id", type="integer", nullable=true, example=1)
     * )
     * ),
     * @OA\Response(
     * response=401,
     * description="Unauthenticated"
     * ),
     * @OA\Response(
     * response=404,
     * description="Post not found"
     * )
     * )
     */
    public function checkUserReaction(Post $post): JsonResponse
    {
        $user = auth()->user();

        // Busca una reacción que coincida con el post y el usuario actual
        $reaction = $post->reactions()->where('user_id', $user->id)->first();

        if ($reaction) {
            return response()->json([
                'has_reacted' => true,
                'reaction_type_id' => $reaction->reaction_type_id
            ]);
        }

        return response()->json([
            'has_reacted' => false,
            'reaction_type_id' => null
        ]);
    }

}
