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
    /**
     * @OA\Post(
     *     path="/api/multimedia-contents",
     *     summary="Attach multimedia to a post",
     *     description="Creates a multimedia content item (image/video) and links it to a post created by the authenticated user.",
     *     tags={"Multimedia Content"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"url_multimedia_contents", "type_multimedia_contents", "post_id"},
     *             @OA\Property(property="url_multimedia_contents", type="string", example="uploads/posts/media123.jpg"),
     *             @OA\Property(property="type_multimedia_contents", type="string", example="image"),
     *             @OA\Property(property="post_id", type="integer", example=10)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Multimedia content created and linked to post",
     *         @OA\JsonContent(ref="#/components/schemas/MultimediaContentSchema")
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized - only post creator can attach multimedia"
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */

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


    /**
     * @OA\Delete(
     *     path="/api/multimedia-contents/{multimediaContent}",
     *     summary="Delete a multimedia content from a post",
     *     description="Deletes a multimedia content item, if it belongs to a post created by the authenticated user.",
     *     tags={"Multimedia Content"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="multimediaContent",
     *         in="path",
     *         required=true,
     *         description="ID of the multimedia content to delete",
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=204,
     *         description="Multimedia content deleted"
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized - only post creator can delete the content"
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Multimedia content not found"
     *     )
     * )
     */

    public function destroy(MultimediaContent $multimediaContent): JsonResponse
    {
        if (auth()->id() !== $multimediaContent->post->user_id) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $multimediaContent->delete();

        return response()->json(null, 204);
    }
}
