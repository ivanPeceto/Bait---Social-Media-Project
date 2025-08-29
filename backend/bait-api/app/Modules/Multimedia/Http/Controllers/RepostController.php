<?php

namespace App\Modules\Multimedia\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Multimedia\Domain\Models\Repost;
use App\Modules\Multimedia\Http\Requests\Repost\CreateRepostRequest;
use Illuminate\Http\JsonResponse;

class RepostController extends Controller
{
    public function store(CreateRepostRequest $request): JsonResponse
    {
        $repost = Repost::create([
            'user_id' => auth()->id(),
            'post_id' => $request->validated('post_id'),
        ]);

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