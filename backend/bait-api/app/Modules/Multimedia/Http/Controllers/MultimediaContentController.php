<?php

namespace App\Modules\Multimedia\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Multimedia\Domain\Models\MultimediaContent;
use App\Modules\Multimedia\Domain\Models\Post;
use App\Modules\Multimedia\Http\Requests\MultimediaContent\CreateMultimediaContentRequest;
use App\Modules\Multimedia\Http\Resources\MultimediaContentResource;
use Illuminate\Http\JsonResponse;

class MultimediaContentController extends Controller
{
    public function store(CreateMultimediaContentRequest $request): JsonResponse
    {
        $post = Post::find($request->validated('post_id'));

        if (auth()->id() !== $post->user_id) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $multimediaContent = MultimediaContent::create([
            'url_multimedia_contents' => $request->validated('url_multimedia_contents'),
            'type_multimedia_contents' => $request->validated('type_multimedia_contents'),
            'post_id' => $request->validated('post_id'),
        ]);

        return response()->json(new MultimediaContentResource($multimediaContent), 201);
    }

    public function destroy(MultimediaContent $multimediaContent): JsonResponse
    {
        if (auth()->id() !== $multimediaContent->post->user_id) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $multimediaContent->delete();

        return response()->json(null, 204);
    }
}