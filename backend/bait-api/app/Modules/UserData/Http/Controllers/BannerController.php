<?php

namespace App\Modules\UserData\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\UserData\Http\Requests\Banner\BannerUploadRequest;
use App\Modules\UserData\Http\Resources\BannerResource;
use App\Modules\UserData\Domain\Models\Banner;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use PHPOpenSourceSaver\JWTAuth\JWTGuard;
use App\Modules\UserData\Domain\Models\User;

class BannerController extends Controller {
    private $guard;

    public function __construct() {
         /** @var JWTGuard $guard */
        $this->guard = auth('api');
    }

    public function upload(BannerUploadRequest $request){

        //Validation
        if (!$request->hasFile('banner') || !$request->file('banner')->isValid()) {
            return response()->json(['message' => 'Invalid file upload'], 422);
        }

        $file = $request->file('banner');
        $path = $file->store('banner', 'public'); // storage/app/public/banner

        $banner = banner::create([
            'url_banner'=>$path,
        ]);

        /** @var User $user */
        $user = $this->guard->user();
        $user->update([
            'banner_id' => $banner->id,
        ]);

        return new BannerResource($banner);
    }

    public function show($id){
        $banner = Banner::findOrFail($id);
        return new BannerResource($banner);
    }
}