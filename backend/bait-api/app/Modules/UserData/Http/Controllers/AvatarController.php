<?php

namespace App\Modules\UserData\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\UserData\Http\Requests\Avatar\AvatarUploadRequest;
use App\Modules\UserData\Http\Resources\AvatarResource;
use App\Modules\UserData\Domain\Models\Avatar;
use App\Modules\UserData\Domain\Models\User;
use Illuminate\Support\Facades\Storage;

class AvatarController extends Controller
{
    public function upload(AvatarUploadRequest $request): AvatarResource
    {
        $file = $request->file('avatar');
        // Corregido: Guarda en la carpeta 'avatars' (plural).
        $path = $file->store('avatars', 'public');

        /** @var User $user */
        $user = auth()->user();

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar->url_avatars);
            $user->avatar->delete();
        }

        $avatar = Avatar::create([
            // Corregido: Nombre de la columna a plural.
            'url_avatars' => $path,
        ]);

        $user->update([
            'avatar_id' => $avatar->id,
        ]);

        // Laravel por defecto devuelve 201 en la creación a través de un Resource.
        return new AvatarResource($avatar);
    }

    public function show(Avatar $avatar): AvatarResource
    {
        return new AvatarResource($avatar);
    }
}