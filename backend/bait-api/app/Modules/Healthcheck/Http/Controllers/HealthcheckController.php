<?php

namespace App\Modules\Healthcheck\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class HealthcheckController
{
    /**
     * @OA\Get(
     *     path="/api/healthcheck",
     *     summary="Healthcheck Endpoint",
     *     description="Returns a simple status message to confirm that the backend is operational.",
     *     tags={"Health"},
     *     @OA\Response(
     *         response=200,
     *         description="OK status"
     *     )
     * )
     */

    public function status(): JsonResponse
    {
        return response()->json(['status' => 'ok']);
    }
}

