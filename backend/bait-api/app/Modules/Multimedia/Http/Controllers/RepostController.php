<?php

namespace App\Modules\Multimedia\Http\Controllers;

use App\Events\NewRepost;
use App\Http\Controllers\Controller;
use App\Modules\Multimedia\Domain\Models\Post;
use App\Modules\Multimedia\Domain\Models\Repost;
use App\Modules\Multimedia\Http\Requests\Repost\CreateRepostRequest;
use App\Notifications\NewRepostNotification;
use Illuminate\Http\JsonResponse;
use App\Modules\Multimedia\Http\Resources\RepostResource;

class RepostController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/reposts",
     *     summary="Repost a post",
     *     description="Allows an authenticated user to repost an existing post.",
     *     tags={"Reposts"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"post_id"},
     *             @OA\Property(property="post_id", type="integer", example=12)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Repost created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/RepostSchema")
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Original post not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */

    public function store(CreateRepostRequest $request): JsonResponse
    {
        $post_id = $request->validated('post_id');
        $repost = Repost::create([
            'user_id' => auth()->id(),
            'post_id' => $post_id,
        ]);

        $post = Post::findOrFail($post_id);

        //event(new NewRepost(auth()->user(), $post));

        $post->user->notify(new NewRepostNotification(auth()->user(), $post));

        return response()->json(new RepostResource($repost), 201);
    }


    /**
     * @OA\Delete(
     *     path="/api/reposts/{repost}",
     *     summary="Delete a repost",
     *     description="Deletes a repost made by the authenticated user.",
     *     tags={"Reposts"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="repost",
     *         in="path",
     *         required=true,
     *         description="ID of the repost to delete",
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=204,
     *         description="Repost deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized - not the owner of the repost"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Repost not found"
     *     )
     * )
     */

    public function destroy(Repost $repost): JsonResponse
    {
        if (auth()->id() !== $repost->user_id) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $repost->delete();
        return response()->json(null, 204);
    }
}
