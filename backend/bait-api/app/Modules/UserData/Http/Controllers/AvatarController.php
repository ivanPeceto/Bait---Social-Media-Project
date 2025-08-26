<?php

namespace App\Modules\UserData\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\UserData\Http\Requests\Avatar\AvatarUploadRequest;
use App\Modules\UserData\Http\Resources\AvatarResource;
use App\Modules\UserData\Domain\Models\Avatar;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use PHPOpenSourceSaver\JWTAuth\JWTGuard;
use App\Modules\UserData\Domain\Models\User;

class AvatarController extends Controller {
    private $guard;

    public function __construct() {
         /** @var JWTGuard $guard */
        $this->guard = auth('api');
    }

    public function upload(AvatarUploadRequest $request){

        //Validation
        if (!$request->hasFile('avatar') || !$request->file('avatar')->isValid()) {
            return response()->json(['message' => 'Invalid file upload'], 422);
        }

        $file = $request->file('avatar');
        $path = $file->store('avatar', 'public'); // storage/app/public/avatar

        $avatar = Avatar::create([
            'url_avatar'=>$path,
        ]);

        /** @var User $user */
        $user = $this->guard->user();
        $user->update([
            'avatar_id' => $avatar->id,
        ]);

        return new AvatarResource($avatar);
    }

    public function show($id){
        $avatar = Avatar::findOrFail($id);
        return new AvatarResource($avatar);
    }
}