<?php

namespace App\Modules\UserData\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\UserData\Http\Requests\Avatar\AvatarUploadRequest;
use App\Modules\UserData\Http\Resources\AvatarResource;
use App\Modules\UserData\Domain\Models\Avatar;
use App\Modules\UserData\Domain\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AvatarController extends Controller
{
    public function upload(AvatarUploadRequest $request): AvatarResource
    {
        $file = $request->file('avatar');
        $path = $file->store('avatars', 'public');

        /** @var User $user */
        $user = auth()->user();

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar->url_avatars);
            $user->avatar->delete();
        }

        $avatar = Avatar::create([
            'url_avatars' => $path,
        ]);

        $user->update([
            'avatar_id' => $avatar->id,
        ]);

        return new AvatarResource($avatar);
    }

    public function show(Avatar $avatar): AvatarResource
    {
        return new AvatarResource($avatar);
    }

    public function destroySelf(): JsonResponse
    {
        /** @var User $user */
        $user = auth()->user();

        $this->destroyForUser($user);
        
        return response()->json(['message' => 'Avatar destroyed. Replaced with default.'], 200);
    }

    public function destroyUserAvatar(User $user): JsonResponse
    {
        $this->destroyForUser($user);

        return response()->json(['message' => 'Avatar for user {$user->username} destroyed. Replaced with default.'], 200);
    }

    private function destroyForUser(User $user): void
    {
        $defaultAvatar = Avatar::where('url_avatars', 'avatars/default.jpg')->firstOrFail();

        if($user->avatar && $user->avatar_id !== $defaultAvatar->id){
            $temp = $user->avatar;
            $user->update(['avatar_id' => $defaultAvatar->id]);

            Storage::disk('public')->delete($temp->url_avatars);
            $temp->delete();
        } elseif (!$user->avatar) {
            $user->update(['avatar_id' => $defaultAvatar->id]);
        }
    }
}