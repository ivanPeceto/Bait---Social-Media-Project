<?php

use Illuminate\Support\Facades\Route;
use App\Modules\UserData\Http\Controllers\AuthController;
use App\Modules\UserData\Http\Controllers\ProfileController;
use App\Modules\UserData\Http\Controllers\UserRoleController;
use App\Modules\UserData\Http\Controllers\AvatarController;

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login',    [AuthController::class, 'login']);
    Route::post('refresh',  [AuthController::class, 'refresh'])->middleware('auth:api');
    Route::post('logout',   [AuthController::class, 'logout'])->middleware('auth:api');
    Route::get('me',        [AuthController::class, 'me'])->middleware('auth:api');
});

Route::middleware('auth:api')->prefix('profile')->group(function () {
    Route::get('/',            [ProfileController::class, 'show']);
    Route::put('/',            [ProfileController::class, 'update']);
    Route::put('/password',    [ProfileController::class, 'changePassword']);
    Route::post('/avatar',     [ProfileController::class, 'updateAvatar']);
    Route::post('/banner',     [ProfileController::class, 'updateBanner']);
});

Route::prefix('roles')->group(function () {
    Route::get('/', [UserRoleController::class, 'index']);
    Route::get('/', [UserRoleController::class, 'create']);
    Route::get('/{role}', [UserRoleController::class, 'update']);
    Route::get('/{role}', [UserRoleController::class, 'destroy']);

});

Route::prefix('avatars')->middleware('auth:api')->group(function () {
    Route::post('/upload', [AvatarController::class, 'upload'])->name('avatars.upload');

    Route::get('/{id}', [AvatarController::class, 'show'])->name('avatars.show');
});

