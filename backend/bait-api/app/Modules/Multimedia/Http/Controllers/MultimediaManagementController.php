<?php

namespace App\Modules\Multimedia\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Modules\Multimedia\Domain\Models\Post;
use App\Modules\Multimedia\Domain\Models\Comment;
use App\Modules\Multimedia\Domain\Models\Repost;
use App\Modules\Multimedia\Domain\Models\PostReaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class MultimediaManagementController extends Controller
{
    /**
     * @OA\Delete(
     *     path="/api/privileged/multimedia/post/{post}",
     *     summary="Delete a post and its associated multimedia",
     *     tags={"Multimedia Management"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="post",
     *         in="path",
     *         required=true,
     *         description="ID of the post to delete",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Post and associated content deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Post and all associated content deleted successfully.")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=404, description="Post not found")
     * )
     */
    public function destroyPost(Post $post): JsonResponse
    {
        foreach ($post->multimediaContents as $content) {
            Storage::disk('public')->delete($content->url_multimedia_contents);
        }

        $post->delete();

        return response()->json(['message' => 'Post and all associated content deleted successfully.'], 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/privileged/multimedia/comment/{comment}",
     *     summary="Delete a comment",
     *     tags={"Multimedia Management"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="comment",
     *         in="path",
     *         required=true,
     *         description="ID of the comment to delete",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Comment deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Comment deleted successfully.")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=404, description="Comment not found")
     * )
     */
    public function destroyComment(Comment $comment): JsonResponse
    {
        $comment->delete();
        return response()->json(['message' => 'Comment deleted successfully.'], 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/privileged/multimedia/repost/{repost}",
     *     summary="Delete a repost",
     *     tags={"Multimedia Management"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="repost",
     *         in="path",
     *         required=true,
     *         description="ID of the repost to delete",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Repost deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Repost deleted successfully.")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=404, description="Repost not found")
     * )
     */
    public function destroyRepost(Repost $repost): JsonResponse
    {
        $repost->delete();
        return response()->json(['message' => 'Repost deleted successfully.'], 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/privileged/multimedia/reaction/{reaction}",
     *     summary="Delete a post reaction",
     *     tags={"Multimedia Management"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="reaction",
     *         in="path",
     *         required=true,
     *         description="ID of the reaction to delete",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Reaction deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Reaction deleted successfully.")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=404, description="Reaction not found")
     * )
     */
    public function destroyReaction(PostReaction $reaction): JsonResponse
    {
        $reaction->delete();
        return response()->json(['message' => 'Reaction deleted successfully.'], 200);
    }
}
