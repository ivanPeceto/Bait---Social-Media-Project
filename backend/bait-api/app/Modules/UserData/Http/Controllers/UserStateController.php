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
    public function index(): JsonResource
    {
        $states = UserState::all();
        return UserStateResource::collection($states);
    }

    public function create(CreateStateRequest $request): UserStateResource
    {
        $state = UserState::create($request->validated());

        return new UserStateResource($state);
    }

    public function update(UpdateStateRequest $request, UserState $state): UserStateResource
    {
        $state->update($request->validated());

        return new UserStateResource($state);
    }

    public function destroy(DeleteStateRequest $request, UserState $state): Response
    {
        $state->delete();

        return response()->noContent();
    }
}