<?php

namespace App\Modules\UserData\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\UserData\Http\Requests\UpdateProfileRequest;
use App\Modules\UserData\Http\Requests\ChangePasswordRequest;
use App\Modules\UserData\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function show() { return new UserResource(auth('api')->user()); }

    public function update(UpdateProfileRequest $request) {

    }

    public function changePassword(ChangePasswordRequest $request) {

    }

    public function updateAvatar(Request $request) {

    }

    public function updateBanner(Request $request) {

    }
}
