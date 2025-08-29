<?php

namespace App\Modules\UserInteractions\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\UserInteractions\Domain\Models\Notification;
use App\Modules\UserInteractions\Http\Requests\Notification\UpdateNotificationRequest;
use App\Modules\UserInteractions\Http\Resources\NotificationResource;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    public function index(): JsonResponse
    {
        $notifications = auth()->user()->notifications()->with('user')->get();
        return response()->json(NotificationResource::collection($notifications));
    }

    public function show(Notification $notification): JsonResponse
    {
        if (auth()->id() !== $notification->user_id) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        return response()->json(new NotificationResource($notification));
    }

    public function update(UpdateNotificationRequest $request, Notification $notification): JsonResponse
    {
        if (auth()->id() !== $notification->user_id) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $notification->update($request->validated());
        return response()->json(new NotificationResource($notification));
    }
}