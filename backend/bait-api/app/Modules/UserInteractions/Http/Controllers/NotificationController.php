<?php

namespace App\Modules\UserInteractions\Http\Controllers;

use App\Events\UserFollowed;
use App\Http\Controllers\Controller;
use App\Modules\UserData\Domain\Models\User;
use App\Modules\UserInteractions\Domain\Models\Follow;
use App\Modules\UserInteractions\Domain\Models\Notification;
use App\Modules\UserInteractions\Http\Requests\Follow\CreateFollowRequest;
use App\Modules\UserInteractions\Http\Requests\Notification\UpdateNotificationRequest;
use App\Modules\UserInteractions\Http\Resources\FollowResource;
use App\Modules\UserInteractions\Http\Resources\NotificationResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class NotificationController extends Controller
{
    public function index(): AnonymousResourceCollection|JsonResponse
    {
        $notifications = auth()->user()->notifications()->with('user')->get();
        return NotificationResource::collection($notifications);
    }

    public function show(Notification $notification): NotificationResource|JsonResponse
    {
        if (auth()->id() !== $notification->user_id) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        return new NotificationResource($notification);
    }

    public function update(UpdateNotificationRequest $request, Notification $notification): NotificationResource|JsonResponse
    {
        if (auth()->id() !== $notification->user_id) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $notification->update($request->validated());
        return new NotificationResource($notification);
    }

    public function store(CreateFollowRequest $request)
    {
        $validated = $request->validated();
        $follower = auth()->user();
        $followingId = $validated['following_id'];

        $follow = Follow::create([
            'follower_id' => $follower->id,
            'following_id' => $followingId,
        ]);

        $followedUser = User::find($followingId);
        UserFollowed::dispatch($follower, $followedUser);

        return new FollowResource($follow);
    }
}