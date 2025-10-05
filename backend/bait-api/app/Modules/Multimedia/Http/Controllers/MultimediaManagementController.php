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
    public function destroyPost(Post $post): JsonResponse
    {
        //Limpia multimedia del almacenamiento interno
        foreach ($post->multimediaContents as $content) {
            Storage::disk('public')->delete($content->url_multimedia_contents);
        }

        $post->delete();

        return response()->json(['message' => 'Post and all associated content deleted successfully.'], 200);
    }


    public function destroyComment(Comment $comment): JsonResponse
    {
        $comment->delete();
        return response()->json(['message' => 'Comment deleted successfully.'], 200);
    }

    public function destroyRepost(Repost $repost): JsonResponse
    {
        $repost->delete();
        return response()->json(['message' => 'Repost deleted successfully.'], 200);
    }

    public function destroyReaction(PostReaction $reaction): JsonResponse
    {
        $reaction->delete();
        return response()->json(['message' => 'Reaction deleted successfully.'], 200);
    }
}