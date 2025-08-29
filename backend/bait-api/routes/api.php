<?php

use Illuminate\Support\Facades\Route;
use App\Modules\UserData\Http\Controllers\AuthController;
use App\Modules\UserData\Http\Controllers\ProfileController;
use App\Modules\UserData\Http\Controllers\UserRoleController;
use App\Modules\UserData\Http\Controllers\AvatarController;

use App\Modules\Content\Http\Controllers\PostController;
use App\Modules\Content\Http\Controllers\CommentController;
use App\Modules\Content\Http\Controllers\NotificationController;
use App\Modules\Social\Http\Controllers\FollowController;
use App\Modules\Social\Http\Controllers\RepostController;
use App\Modules\Content\Http\Controllers\MultimediaContentController;
use App\Modules\Chat\Http\Controllers\ChatController;
use App\Modules\Chat\Http\Controllers\MessageController;
use App\Modules\Multimedia\Http\Controllers\PostReactionController;

/*Healthcheck routes*/
Route::get('/', function () {
    return response()->json(['status' => 'ok']);
});

Route::get('/ping', function () {
    return response()->json(['status' => 'ok']);
});
/*----End Healthceck routes--------------*/

/*UserData routes*/

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

/*----End UserData routes--------------*/

/*----------------------------------------------------------------------------------------------*/

/*MultiMedia routes*/

Route::middleware('auth:api')->prefix('posts')->group(function () {
    Route::get('/', [PostController::class, 'index']);
    Route::post('/', [PostController::class, 'store']);
    Route::get('/{post}', [PostController::class, 'show']);
    Route::put('/{post}', [PostController::class, 'update']);
    Route::delete('/{post}', [PostController::class, 'destroy']);
});

Route::middleware('auth:api')->prefix('comments')->group(function () {
    Route::get('/', [CommentController::class, 'index']);
    Route::post('/', [CommentController::class, 'store']);
    Route::get('/{comment}', [CommentController::class, 'show']);
    Route::put('/{comment}', [CommentController::class, 'update']);
    Route::delete('/{comment}', [CommentController::class, 'destroy']);
});

Route::middleware('auth:api')->prefix('reposts')->group(function () {
    Route::post('/', [RepostController::class, 'store']);
    Route::delete('/{repost}', [RepostController::class, 'destroy']);
});

Route::middleware('auth:api')->prefix('multimedia-contents')->group(function () {
    Route::post('/', [MultimediaContentController::class, 'store']);
    Route::delete('/{multimediaContent}', [MultimediaContentController::class, 'destroy']);
});

Route::middleware('auth:api')->prefix('post-reactions')->group(function () {
    Route::post('/', [PostReactionController::class, 'store']);
});

/*end MultiMedia routes*/

/*----------------------------------------------------------------------------------------------*/

/*UserInteractions routes*/

Route::middleware('auth:api')->prefix('notifications')->group(function () {
    Route::get('/', [NotificationController::class, 'index']);
    Route::get('/{notification}', [NotificationController::class, 'show']);
    Route::put('/{notification}', [NotificationController::class, 'update']);
});

Route::middleware('auth:api')->prefix('follows')->group(function () {
    Route::post('/', [FollowController::class, 'store']);
    Route::delete('/{follow}', [FollowController::class, 'destroy']);
});


Route::middleware('auth:api')->prefix('chats')->group(function () {
    Route::get('/', [ChatController::class, 'index']);
    Route::post('/', [ChatController::class, 'store']);
    Route::get('/{chat}', [ChatController::class, 'show']);
});

Route::middleware('auth:api')->prefix('chats/{chat}')->group(function () {
    Route::get('messages', [MessageController::class, 'index']); 
    Route::post('messages', [MessageController::class, 'store']); 
});

/*end UserInteractions routes*/
