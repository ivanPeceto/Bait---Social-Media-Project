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

    public function upload(BannerUploadRequest $request)
    {
        $file = $request->file('banner');
        $path = $file->store('banners', 'public'); 

        /** @var User $user */
        $user = $this->guard->user();

        if ($user->banner) {
            Storage::disk('public')->delete($user->banner->url_banners);
            $user->banner->delete();
        }

        $banner = Banner::create([ 
            'url_banners' => $path, 
        ]);

        $user->update([
            'banner_id' => $banner->id,
        ]);

        return new BannerResource($banner);
    }

    public function show(Banner $banner): BannerResource
    {
        return  new BannerResource($banner);
    }

    private function destroyForUser(User $user): void
    {
        $defaultBanner = Banner::where('url_banners', 'banners/default.jpg')->firstOrFail();

        if($user->banner && $user->banner_id !== $defaultBanner->id){
            $temp = $user->banner;
            $user->update(['banner_id' => $defaultBanner->id]);

            Storage::disk('public')->delete($temp->url_banners);
            $temp->delete();
        } elseif (!$user->avatar){
            $user->update(['banner_id'=>$defaultBanner->id]);
        }
    }

    public function destroySelf(): JsonResponse
    {
        /** @var User $user */
        $user = $this->guard->user();

        $this->destroyForUser($user);

        return response()->json(['message' => 'Banner destroyed. Replaced with default.'], 200);
    }

    public function destroyUserBanner(User $user): JsonResponse 
    {
        $this->destroyForUser($user);

        return response()->json(['message'=>'Banner for user {$user->username} destroyed. Replaced with default.'],200);
    }
}