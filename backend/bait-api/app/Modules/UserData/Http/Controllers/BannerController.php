<?php

namespace App\Modules\UserData\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\UserData\Domain\Models\Banner;
use App\Modules\UserData\Domain\Models\User;
use App\Modules\UserData\Http\Requests\Banner\BannerUploadRequest;
use App\Modules\UserData\Http\Resources\BannerResource;
use PHPOpenSourceSaver\JWTAuth\JWTGuard;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    /** @var JWTGuard $guard */
    private $guard;

    public function __construct()
    {
        $this->guard = auth('api');
    }

    /**
     * @OA\Post(
     *     path="/api/banners/upload",
     *     summary="Upload or replace the authenticated user's banner",
     *     description="Uploads a new banner image for the authenticated user. If a banner already exists, it will be deleted and replaced with the new one.",
     *     tags={"Banner"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"banner"},
     *                 @OA\Property(
     *                     property="banner",
     *                     type="string",
     *                     format="binary",
     *                     description="The banner image file to upload"
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Successfully uploaded banner",
     *         @OA\JsonContent(ref="#/components/schemas/BannerSchema")
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - user not authenticated"
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation error - invalid or missing file"
     *     )
     * )
     */


    public function upload(BannerUploadRequest $request)
    {
        $file = $request->file('banner');
        $path = $file->store('banners', 'public'); 

        /** @var User $user */
        $user = $this->guard->user();

        $banner = Banner::create([ 
            'url_banners' => $path, 
        ]);

        $user->update([
            'banner_id' => $banner->id,
        ]);

        return new BannerResource($banner);
    }


    /**
     * @OA\Get(
     *     path="/api/banners/{banner}",
     *     summary="Get a specific banner",
     *     description="Returns a single banner resource by ID",
     *     tags={"Banner"},
     *
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="banner",
     *         in="path",
     *         required=true,
     *         description="ID of the banner to retrieve",
     *         @OA\Schema(type="integer", example=2)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Banner retrieved successfully",
     *         @OA\JsonContent(ref="#/components/schemas/BannerSchema")
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Missing or invalid token"
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Banner not found"
     *     )
     * )
     */
    public function show(Banner $banner): BannerResource
    {
        return new BannerResource($banner);
    }



    /**
     * @OA\Delete(
     *     path="/api/banners/self",
     *     summary="Delete the authenticated user's banner",
     *     description="Deletes the authenticated user's banner and replaces it with the default banner.",
     *     tags={"Banner"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Banner destroyed and replaced with default",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Banner destroyed. Replaced with default."
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - user not authenticated"
     *     )
     * )
     */

    public function destroySelf(): JsonResponse
    {
        $user = auth()->user();

        // Luego destruimos el banner anterior (que estaba asociado al usuario)
        $this->destroyForUser($user);

        return response()->json(['message' => 'Banner destroyed. Replaced with default.'], 200);
    }

    

    /**
     * @OA\Delete(
     *     path="/api/privileged/users/{user}/banner",
     *     summary="Delete a specific user's banner",
     *     description="Deletes the specified user's banner and replaces it with the default banner. Only accessible to admins or moderators.",
     *     tags={"User Management"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         required=true,
     *         description="ID of the user whose banner will be deleted",
     *         @OA\Schema(type="integer", example=2)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Banner deleted and replaced with default",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Banner for user johndoe destroyed. Replaced with default."
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - not authorized"
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="User not found or banner not found"
     *     )
     * )
     */

    public function destroyUserBanner(User $user): JsonResponse 
    {
        $this->destroyForUser($user);
        $user->refresh();

        return response()->json([
            'message' => "Banner for user {$user->username} destroyed. Replaced with default."
        ], 200);
    }
    
    private function destroyForUser(User $user): void
    {
        $defaultBanner = Banner::firstOrCreate(
            ['url_banners' => 'banners/default.jpg']
        );

<<<<<<< HEAD
        if ($user->banner && $user->banner_id !== $defaultBanner->id) {
            $currentBanner = $user->banner;
            
=======
        if($user->banner && $user->banner_id !== $defaultBanner->id){
            $temp = $user->banner;
>>>>>>> feature/backend/documentation
            $user->update(['banner_id' => $defaultBanner->id]);

            Storage::disk('public')->delete($currentBanner->url_banners);
            
            $currentBanner->delete();
        } 
        elseif (!$user->banner) {
            $user->update(['banner_id' => $defaultBanner->id]);
        }
    }

}