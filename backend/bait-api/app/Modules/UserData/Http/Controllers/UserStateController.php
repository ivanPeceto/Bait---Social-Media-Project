<?php

namespace App\Modules\UserData\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\UserData\Domain\Models\UserState;
use App\Modules\UserData\Http\Requests\CreateStateRequest;
use App\Modules\UserData\Http\Requests\UpdateStateRequest;
use App\Modules\UserData\Http\Resources\UserStateResource;
use Illuminate\Http\JsonResponse;

class UserStateController extends Controller
{
    public function index(): JsonResponse{
        $states = UserState::all();
        return response()->json(UserStateResource::collection($states), 200);
    }

    public function create(CreateStateRequest $request): JsonResponse
    {
        $state = UserState::create($request->validated());

        return response()->json([
            'message'=>'User role created successfully.',
            'data'=>$state,
        ], 201);
    }

    public function update(UpdateStateRequest $request, UserState $state): JsonResponse
    {
        $state->update($request->validated());

        return response()->json([
            'message' => 'User role updated successfully.',
            'data'=>$state,
        ], 200);
    }

    public function destroy(UserState $state): JsonResponse{

        return response()->json([
            'message'=>'User role eliminated successfully',
        ], 200);
    }
}
