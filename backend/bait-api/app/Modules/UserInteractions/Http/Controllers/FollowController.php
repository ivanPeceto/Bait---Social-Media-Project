<?php

namespace App\Modules\UserInteractions\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\UserInteractions\Domain\Models\Follow;
use App\Modules\UserInteractions\Http\Requests\Follow\CreateFollowRequest;
use Illuminate\Http\JsonResponse;
use App\Modules\UserInteractions\Http\Requests\Follow\DestroyFollowRequest; // **Se agregó el import**

class FollowController extends Controller
{
    public function store(CreateFollowRequest $request): JsonResponse
    {
        $existingFollow = Follow::where('follower_id', auth()->id())
            ->where('following_id', $request->validated('following_id'))
            ->first();

        if ($existingFollow) {
            return response()->json(['message' => 'Already following this user.'], 409);
        }

        $follow = Follow::create([
            'follower_id' => auth()->id(),
            'following_id' => $request->validated('following_id'),
        ]);

        return response()->json(['message' => 'User followed successfully.'], 201);
    }

    // **Corregido: Usa DestroyFollowRequest y el método firstOrFail**
    public function destroy(DestroyFollowRequest $request): JsonResponse
    {
        $follow = Follow::where('follower_id', auth()->id())
            ->where('following_id', $request->validated('following_id'))
            ->firstOrFail();

        $follow->delete();
        return response()->json(null, 204);
    }
}