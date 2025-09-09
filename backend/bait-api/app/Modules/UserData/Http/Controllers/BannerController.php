<?php

namespace App\Modules\UserData\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\UserData\Domain\Models\Banner;
use App\Modules\UserData\Domain\Models\User;
use App\Modules\UserData\Http\Requests\Banner\BannerUploadRequest;
use App\Modules\UserData\Http\Resources\BannerResource;
use PHPOpenSourceSaver\JWTAuth\JWTGuard;

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
        // Eliminado: Bloque de validación manual redundante.
        // BannerUploadRequest ya se encarga de esto.

        $file = $request->file('banner');
        $path = $file->store('banners', 'public'); // 'banners' en plural para consistencia

        $banner = Banner::create([ // Corregido: 'Banner' con mayúscula
            'url_banners' => $path, // Corregido: a 'url_banners'
        ]);

        /** @var User $user */
        $user = $this->guard->user();
        $user->update([
            'banner_id' => $banner->id,
        ]);

        // La respuesta por defecto para una creación es 201, pero 200 también es común.
        // BannerResource no establece un código, por lo que Laravel usará 200 OK.
        return new BannerResource($banner);
    }

    public function show($id)
    {
        $banner = Banner::findOrFail($id);
        return new BannerResource($banner);
    }
}