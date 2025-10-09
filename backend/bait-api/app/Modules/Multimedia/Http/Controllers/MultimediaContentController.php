<?php

namespace App\Modules\Multimedia\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Multimedia\Domain\Models\MultimediaContent;
use App\Modules\Multimedia\Domain\Models\Post;
use App\Modules\Multimedia\Http\Requests\MultimediaContent\CreateMultimediaContentRequest;
use App\Modules\Multimedia\Http\Resources\MultimediaContentResource;
use Illuminate\Http\JsonResponse;
use App\Modules\Multimedia\Http\Requests\MultimediaContent\UploadMultimediaContentRequest;

class MultimediaContentController extends Controller
{
    /**
     * @OA\Post(
     * path="/api/multimedia-contents",
     * summary="Upload and attach multimedia to a post",
     * description="Uploads a file (image/video) and links it to a post.",
     * tags={"Multimedia Content"},
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     * required=true,
     * @OA\MediaType(
     * mediaType="multipart/form-data",
     * @OA\Schema(
     * required={"file", "post_id"},
     * @OA\Property(
     * property="file",
     * type="string",
     * format="binary",
     * description="The image or video file to upload."
     * ),
     * @OA\Property(
     * property="post_id",
     * type="integer",
     * description="The ID of the post to attach the media to.",
     * example=1
     * )
     * )
     * )
     * ),
     * @OA\Response(
     * response=201,
     * description="Multimedia uploaded and linked successfully.",
     * @OA\JsonContent(ref="#/components/schemas/MultimediaContentSchema")
     * ),
     * @OA\Response(response=422, description="Validation Error"),
     * @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function store(UploadMultimediaContentRequest $request): JsonResponse
    {
        $file = $request->file('file');
        $postId = $request->validated('post_id');
        
        $fileType = str_starts_with($file->getMimeType(), 'video') ? 'video' : 'image';
        
        $path = $file->store('multimedia', 'public');

        $multimediaContent = MultimediaContent::create([
            'url_multimedia_contents' => $path,
            'type_multimedia_contents' => $fileType,
            'post_id' => $postId,
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
