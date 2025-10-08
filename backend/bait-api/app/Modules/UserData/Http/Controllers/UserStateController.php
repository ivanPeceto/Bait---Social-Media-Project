<?php

namespace App\Modules\UserData\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\UserData\Domain\Models\UserState;
use App\Modules\UserData\Http\Requests\UserState\CreateStateRequest;
use App\Modules\UserData\Http\Requests\UserState\UpdateStateRequest;
use App\Modules\UserData\Http\Requests\UserState\DeleteStateRequest;
use App\Modules\UserData\Http\Resources\UserStateResource;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;

class UserStateController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/states",
     *     summary="Get all user states",
     *     description="Returns a list of all user states (e.g. active, suspended).",
     *     operationId="getAllStates",
     *     tags={"User States"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="List of user states",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/UserStateSchema")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - user not authenticated"
     *     )
     * )
     */

    public function index(): JsonResource
    {
        $states = UserState::all();
        return UserStateResource::collection($states);
    }


    /**
     * @OA\Post(
     *     path="/api/states",
     *     summary="Create a new user state",
     *     description="Creates a new user state in the system.",
     *     tags={"User States"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="muted")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="State created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/UserStateSchema")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - user not authenticated"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */

    public function create(CreateStateRequest $request): UserStateResource
    {
        $state = UserState::create($request->validated());

        return new UserStateResource($state);
    }
    

    /**
     * @OA\Put(
     *     path="/api/states/{state}",
     *     summary="Update a user state",
     *     description="Updates an existing user state.",
     *     tags={"User States"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="state",
     *         in="path",
     *         required=true,
     *         description="ID of the state to update",
     *         @OA\Schema(type="integer", example=4)
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="banned")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="State updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/UserStateSchema")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - user not authenticated"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="State not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */

    public function update(UpdateStateRequest $request, UserState $state): UserStateResource
    {
        $state->update($request->validated());

        return new UserStateResource($state);
    }


    /**
     * @OA\Delete(
     *     path="/api/states/{state}",
     *     summary="Delete a user state",
     *     description="Deletes an existing user state.",
     *     tags={"User States"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="state",
     *         in="path",
     *         required=true,
     *         description="ID of the state to delete",
     *         @OA\Schema(type="integer", example=4)
     *     ),
     *
     *     @OA\Response(
     *         response=204,
     *         description="State deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - user not authenticated"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="State not found"
     *     )
     * )
     */

    public function destroy(DeleteStateRequest $request, UserState $state): Response
    {
        $state->delete();

        return response()->noContent();
    }
}
