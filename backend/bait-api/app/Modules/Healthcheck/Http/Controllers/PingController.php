<?php

namespace App\Modules\Healthcheck\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class PingController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/ping",
     *     summary="Ping Endpoint",
     *     description="Simple endpoint to check if the API is responding. Returns a JSON object with service status.",
     *     tags={"Health"},
     *     @OA\Response(
     *         response=200,
     *         description="The API is reachable. Returns a JSON: {'status': 'ok'}"
     *     )
     * )
     */

    public function ping(): JsonResponse
    {
        return response()->json(['status' => 'ok']);
    }
}
