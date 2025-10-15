<?php

namespace App\Modules\UserInteractions\Http\Controllers;

use App\Http\Controllers\Controller;
// Se elimina el 'use' del modelo viejo y personalizado
// use App\Modules\UserInteractions\Domain\Models\Notification; 
use App\Modules\UserInteractions\Http\Requests\Notification\UpdateNotificationRequest;
use App\Modules\UserInteractions\Http\Resources\NotificationResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
// --- INICIO DE CAMBIOS NECESARIOS ---
// 1. Se importa el modelo de notificación estándar de Laravel.
use Illuminate\Notifications\DatabaseNotification;
// --- FIN DE CAMBIOS NECESARIOS ---

class NotificationController extends Controller
{
    /**
     * @OA\Get(
     * path="/api/notifications",
     * operationId="getUserNotifications",
     * tags={"Notifications"},
     * summary="List all notifications for the authenticated user",
     * description="Retrieves a list of all notifications for the currently authenticated user.",
     * security={{"bearerAuth":{}}},
     * @OA\Response(
     * response=200,
     * description="Successful operation",
     * @OA\JsonContent(
     * type="array",
     * @OA\Items(ref="#/components/schemas/NotificationSchema")
     * )
     * ),
     * @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function index(): AnonymousResourceCollection|JsonResponse
    {
        // Esta línea ahora funciona porque el método 'notifications()' del modelo User es el correcto.
        $notifications = auth()->user()->notifications()->get();
        return NotificationResource::collection($notifications);
    }

    /**
     * @OA\Get(
     * path="/api/notifications/{notification}",
     * operationId="getNotificationById",
     * tags={"Notifications"},
     * summary="Get a single notification by ID",
     * description="Retrieves the details of a single notification. The user must be the owner of the notification.",
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="notification",
     * in="path",
     * required=true,
     * description="The ID of the notification.",
     * @OA\Schema(type="string", format="uuid")
     * ),
     * @OA\Response(
     * response=200,
     * description="Successful operation",
     * @OA\JsonContent(ref="#/components/schemas/NotificationSchema")
     * ),
     * @OA\Response(response=401, description="Unauthenticated"),
     * @OA\Response(response=403, description="Forbidden - User does not own this notification"),
     * @OA\Response(response=404, description="Notification not found")
     * )
     */
    // --- INICIO DE CAMBIOS NECESARIOS ---
    // 2. Se cambia el tipo del parámetro para que Laravel inyecte el modelo de notificación correcto.
    public function show(DatabaseNotification $notification): NotificationResource|JsonResponse
    {
        // 3. Se cambia 'user_id' por 'notifiable_id', que es la columna estándar de Laravel.
        if (auth()->id() !== $notification->notifiable_id) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        return new NotificationResource($notification);
    }
    // --- FIN DE CAMBIOS NECESARIOS ---

    /**
     * @OA\Put(
     * path="/api/notifications/{notification}",
     * operationId="updateNotification",
     * tags={"Notifications"},
     * summary="Mark a notification as read",
     * description="Updates the status of a notification, typically to mark it as read. The user must be the owner of the notification.",
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="notification",
     * in="path",
     * required=true,
     * description="The ID of the notification.",
     * @OA\Schema(type="string", format="uuid")
     * ),
     * @OA\RequestBody(
     * required=true,
     * description="Boolean status to mark the notification as read.",
     * @OA\JsonContent(
     * required={"is_read"},
     * @OA\Property(property="is_read", type="boolean", example=true)
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Notification updated successfully",
     * @OA\JsonContent(ref="#/components/schemas/NotificationSchema")
     * ),
     * @OA\Response(response=401, description="Unauthenticated"),
     * @OA\Response(response=403, description="Forbidden - User does not own this notification"),
     * @OA\Response(response=404, description="Notification not found"),
     * @OA\Response(response=422, description="Validation error")
     * )
     */
    // --- INICIO DE CAMBIOS NECESARIOS ---
    // 4. Se repiten los cambios del método 'show' aquí.
    public function update(UpdateNotificationRequest $request, DatabaseNotification $notification): NotificationResource|JsonResponse
    {
        if (auth()->id() !== $notification->notifiable_id) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        if ($request->validated('is_read')) {
            // El método 'markAsRead()' es del sistema estándar de notificaciones de Laravel.
            // El método del modelo viejo personalizado probablemente no funcionaba.
            $notification->markAsRead();
        }

        return new NotificationResource($notification);
    }
    // --- FIN DE CAMBIOS NECESARIOS ---
}