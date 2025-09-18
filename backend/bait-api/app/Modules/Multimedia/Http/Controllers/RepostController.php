<?php

namespace App\Modules\Multimedia\Http\Controllers;

use App\Events\NewRepost;
use App\Http\Controllers\Controller;
use App\Modules\Multimedia\Domain\Models\Post;
use App\Modules\Multimedia\Domain\Models\Repost;
use App\Modules\Multimedia\Http\Requests\Repost\CreateRepostRequest;
use Illuminate\Http\JsonResponse;
use App\Modules\Multimedia\Http\Resources\RepostResource;

class RepostController extends Controller
{
    public function store(CreateRepostRequest $request): JsonResponse
    {
        $post_id = $request->validated('post_id');
        $repost = Repost::create([
            'user_id' => auth()->id(),
            'post_id' => $post_id,
        ]);

        $post = Post::findOrFail($post_id);
        event(new NewRepost(auth()->user(), $post));

        return response()->json(new RepostResource($repost), 201);
    }

    public function destroy(Repost $repost): JsonResponse
    {
        if (auth()->id() !== $repost->user_id) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $repost->delete();
        return response()->json(null, 204);
    }
}