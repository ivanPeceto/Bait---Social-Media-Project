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
    /**
     * @OA\Post(
     * path="/api/avatars/upload",
     * operationId="uploadAvatar",
     * tags={"Avatar"},
     * summary="Upload user avatar",
     * description="Uploads an image file to be used as the user's avatar. Replaces any existing avatar.",
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     * required=true,
     * @OA\MediaType(
     * mediaType="multipart/form-data",
     * @OA\Schema(
     * required={"avatar"},
     * @OA\Property(
     * property="avatar",
     * type="string",
     * format="binary",
     * description="Image file to upload"
     * )
     * )
     * )
     * ),
     * @OA\Response(
     * response=201,
     * description="Avatar created successfully",
     * @OA\JsonContent(ref="#/components/schemas/AvatarSchema")
     * ),
     * @OA\Response(
     * response=422,
     * description="Validation error"
     * ),
     * @OA\Response(
     * response=401,
     * description="Unauthenticated"
     * )
     * )
     */
    public function upload(AvatarUploadRequest $request): AvatarResource
    {
        $file = $request->file('avatar');
        $path = $file->store('avatars', 'public');

        /** @var User $user */
        $user = auth()->user();

        // --- INICIO DE LA MODIFICACIÓN ---
        // Se busca el avatar por defecto para evitar que sea eliminado.
        // Se usa first() en lugar de firstOrFail() para no causar un error si no existe.
        $defaultAvatar = Avatar::where('url_avatars', 'avatars/default.jpg')->first();

        // Se comprueba si el usuario tiene un avatar asignado.
        // Y MUY IMPORTANTE: se comprueba que el avatar actual NO SEA el de por defecto.
        // Solo si se cumplen ambas condiciones, se procede a eliminar el avatar anterior.
        if ($user->avatar && (!$defaultAvatar || $user->avatar->id !== $defaultAvatar->id)) {
            Storage::disk('public')->delete($user->avatar->url_avatars);
            $user->avatar->delete();
        }
        // --- FIN DE LA MODIFICACIÓN ---

        $avatar = Avatar::create([
            'url_avatars' => $path,
        ]);

        $user->update([
            'avatar_id' => $avatar->id,
        ]);

        return new AvatarResource($avatar);
    }

    
    /**
     * @OA\Get(
     * path="/api/avatars/{avatar}",
     * summary="Get a specific avatar",
     * description="Returns a single avatar resource by ID",
     * tags={"Avatar"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="avatar",
     * in="path",
     * required=true,
     * description="ID of the avatar to retrieve",
     * @OA\Schema(type="integer")
     * ),
     *
     * @OA\Response(
     * response=200,
     * description="Successful response",
     * @OA\JsonContent(ref="#/components/schemas/AvatarSchema")
     * ),
     *
     * @OA\Response(
     * response=404,
     * description="Avatar not found"
     * )
     * )
     */
    public function show(Avatar $avatar): AvatarResource
    {
        return new AvatarResource($avatar);
    }



    /**
     * @OA\Delete(
     * path="/api/avatars/self",
     * summary="Delete the authenticated user's avatar",
     * description="Deletes the avatar of the currently authenticated user and replaces it with a default avatar.",
     * tags={"Avatar"},
     * * security={{"bearerAuth":{}}},
     * * @OA\Response(
     * response=200,
     * description="Avatar destroyed and replaced with default",
     * @OA\JsonContent(
     * type="object",
     * @OA\Property(
     * property="message",
     * type="string",
     * example="Avatar destroyed. Replaced with default."
     * )
     * )
     * ),
     * @OA\Response(
     * response=401,
     * description="Unauthorized - user not authenticated"
     * )
     * )
     */
    public function destroySelf(): JsonResponse
    {
        /** @var User $user */
        $user = auth()->user();

        $this->destroyForUser($user);
        
        return response()->json(['message' => 'Avatar destroyed. Replaced with default.'], 200);
    }

    /**
     * @OA\Delete(
     * path="/api/privileged/users/{user}/avatar",
     * summary="Delete avatar for a specified user (admin/moderator only)",
     * description="Deletes the avatar for the given user and replaces it with a default avatar.",
     * tags={"User Management"},
     * * security={{"bearerAuth":{}}},
     * * @OA\Parameter(
     * name="user",
     * in="path",
     * required=true,
     * description="ID of the user whose avatar will be deleted",
     * @OA\Schema(type="integer")
     * ),
     * * @OA\Response(
     * response=200,
     * description="Avatar destroyed and replaced with default",
     * @OA\JsonContent(
     * type="object",
     * @OA\Property(
     * property="message",
     * type="string",
     * example="Avatar for user johndoe destroyed. Replaced with default."
     * )
     * )
     * ),
     * * @OA\Response(
     * response=403,
     * description="Forbidden - user does not have permission"
     * ),
     * * @OA\Response(
     * response=404,
     * description="User not found"
     * )
     * )
     */
    public function destroyUserAvatar(User $user): JsonResponse
    {
        $this->destroyForUser($user);

        return response()->json(['message' => "Avatar for user {$user->username} destroyed. Replaced with default."], 200);
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