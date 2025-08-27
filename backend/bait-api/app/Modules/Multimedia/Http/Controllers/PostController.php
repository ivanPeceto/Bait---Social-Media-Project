<?php

namespace App\Modules\Multimedia\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Multimedia\Domain\Models\Post;
use App\Modules\Multimedia\Http\Requests\Post\CreatePostRequest;
use App\Modules\Multimedia\Http\Requests\Post\UpdatePostRequest;
use App\Modules\Multimedia\Http\Resources\PostResource;
use Illuminate\Http\JsonResponse;

class PostController extends Controller
{
    public function index(): JsonResponse
    {
        $posts = Post::with('user')->get();
        return response()->json(PostResource::collection($posts));
    }

    public function store(CreatePostRequest $request): JsonResponse
    {
        $post = Post::create([
            'content_posts' => $request->validated('content'),
            'user_id' => auth()->id(),
        ]);

        return response()->json(new PostResource($post), 201);
    }

    public function show(Post $post): JsonResponse
    {
        return response()->json(new PostResource($post));
    }

    public function update(UpdatePostRequest $request, Post $post): JsonResponse
    {
        if ($request->user()->id !== $post->user_id) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $post->update($request->validated());
        return response()->json(new PostResource($post));
    }

    public function destroy(Post $post): JsonResponse
    {
        if ($request->user()->id !== $post->user_id) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }
        
        $post->delete();
        return response()->json(null, 204);
    }
}