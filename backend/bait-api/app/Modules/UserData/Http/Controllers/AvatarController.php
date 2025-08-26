<?php

namespace App\Modules\UserData\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\UserData\Http\Requests\Avatar\AvatarUploadRequest;
use App\Modules\UserData\Http\Resources\AvatarResource;
use App\Modules\UserData\Domain\Models\Avatar;

class AvatarController extends Controller {

    public function upload(AvatarUploadRequest $request){
        $file = $request->file('avatar');
        $path = $file->store('avatar', 'public'); // storage/app/public/avatar

        $avatar = Avatar::create([
            'url_avatar'=>$path,
        ]);

        return new AvatarResource($avatar);
    }
}