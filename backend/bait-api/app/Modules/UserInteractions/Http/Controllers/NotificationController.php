<?php

namespace App\Modules\UserInteractions\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\UserInteractions\Domain\Models\Notification;
use App\Modules\UserInteractions\Http\Requests\Notification\UpdateNotificationRequest;
use App\Modules\UserInteractions\Http\Resources\NotificationResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class NotificationController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/notifications",
     *     operationId="getUserNotifications",
     *     tags={"Notifications"},
     *     summary="List all notifications for the authenticated user",
     *     description="Retrieves a list of all notifications for the currently authenticated user.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/NotificationSchema")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */

    public function index(): AnonymousResourceCollection|JsonResponse
    {
        $notifications = auth()->user()->notifications()->get();

        return NotificationResource::collection($notifications);
    }


    /**
     * @OA\Get(
     *     path="/api/notifications/{notification}",
     *     operationId="getNotificationById",
     *     tags={"Notifications"},
     *     summary="Get a single notification by ID",
     *     description="Retrieves the details of a single notification. The user must be the owner of the notification.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="notification",
     *         in="path",
     *         required=true,
     *         description="The ID of the notification.",
     *         @OA\Schema(type="string", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/NotificationSchema")
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden - User does not own this notification"),
     *     @OA\Response(response=404, description="Notification not found")
     * )
     */

    public function show(Notification $notification): NotificationResource|JsonResponse
    {
        if (auth()->id() !== $notification->user_id) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        return new NotificationResource($notification);
    }


    /**
     * @OA\Put(
     *     path="/api/notifications/{notification}",
     *     operationId="updateNotification",
     *     tags={"Notifications"},
     *     summary="Mark a notification as read",
     *     description="Updates the status of a notification, typically to mark it as read. The user must be the owner of the notification.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="notification",
     *         in="path",
     *         required=true,
     *         description="The ID of the notification.",
     *         @OA\Schema(type="string", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Boolean status to mark the notification as read.",
     *         @OA\JsonContent(
     *             required={"is_read"},
     *             @OA\Property(
     *                 property="is_read_notifications",
     *                 type="boolean",
     *                 example=true
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Notification updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/NotificationSchema")
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden - User does not own this notification"),
     *     @OA\Response(response=404, description="Notification not found"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */

    public function update(UpdateNotificationRequest $request, Notification $notification): NotificationResource|JsonResponse
    {
        if (auth()->id() !== $notification->user_id) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        if ($request->validated()['is_read_notifications']) {
            $notification->update(['is_read_notifications' => true]);
        }

        return new NotificationResource($notification);
    }

}
