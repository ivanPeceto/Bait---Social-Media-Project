<?php

use Illuminate\Support\Facades\Route;

/*UserData*/
use App\Modules\UserData\Http\Controllers\AuthController;
use App\Modules\UserData\Http\Controllers\ProfileController;
use App\Modules\UserData\Http\Controllers\UserRoleController;
use App\Modules\UserData\Http\Controllers\AvatarController;
use App\Modules\UserData\Http\Controllers\BannerController;
use App\Modules\UserData\Http\Controllers\UserStateController;

/*MultiMedia*/
use App\Modules\Multimedia\Http\Controllers\PostController;
use App\Modules\Multimedia\Http\Controllers\CommentController;
use App\Modules\Multimedia\Http\Controllers\RepostController;
use App\Modules\Multimedia\Http\Controllers\MultimediaContentController;
use App\Modules\Multimedia\Http\Controllers\PostReactionController;

/*UserInteractions*/
use App\Modules\UserInteractions\Http\Controllers\NotificationController;
use App\Modules\UserInteractions\Http\Controllers\FollowController;
use App\Modules\UserInteractions\Http\Controllers\ChatController;
use App\Modules\UserInteractions\Http\Controllers\MessageController;


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
    Route::post('register', [AuthController::class, 'register'])->name('auth.register');
    Route::post('login',    [AuthController::class, 'login'])->name('auth.login');
    Route::post('refresh',  [AuthController::class, 'refresh'])->middleware('auth:api')->name('auth.refresh');
    Route::post('logout',   [AuthController::class, 'logout'])->middleware('auth:api')->name('auth.logout');
    Route::get('me',        [AuthController::class, 'me'])->middleware('auth:api')->name('auth.me');
});

Route::middleware('auth:api')->prefix('profile')->group(function () {
    Route::get('/',            [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/',            [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/password',    [ProfileController::class, 'changePassword'])->name('profile.password');
});

Route::prefix('roles')->middleware('auth:api')->group(function () {
    Route::get('/', [UserRoleController::class, 'index'])->name('roles.index');
    Route::post('/', [UserRoleController::class, 'create'])->name('roles.create');
    Route::put('/{role}', [UserRoleController::class, 'update'])->name('roles.update');
    Route::delete('/{role}', [UserRoleController::class, 'destroy'])->name('roles.destroy');
});

Route::prefix('states')->middleware('auth:api')->group(function () {
    Route::get('/', [UserStateController::class, 'index'])->name('states.index');
    Route::post('/', [UserStateController::class, 'create'])->name('states.create');
    Route::put('/{state}', [UserStateController::class, 'update'])->name('states.update');
    Route::delete('/{state}', [UserStateController::class, 'destroy'])->name('states.destroy');
});

Route::prefix('avatars')->middleware('auth:api')->group(function () {
    Route::post('/upload', [AvatarController::class, 'upload'])->name('avatars.upload');
    Route::get('/{avatar}', [AvatarController::class, 'show'])->name('avatars.show');
});

Route::prefix('banners')->middleware('auth:api')->group(function () {
    Route::post('/upload', [BannerController::class, 'upload'])->name('banner.upload');
    Route::get('/{banner}', [BannerController::class, 'show'])->name('banner.show');
});

/*----End UserData routes--------------*/

/*----------------------------------------------------------------------------------------------*/

/*MultiMedia routes*/

Route::middleware('auth:api')->prefix('posts')->group(function () {
    Route::get('/', [PostController::class, 'index']);
    Route::post('/', [PostController::class, 'store']);
    Route::get('/{post}', [PostController::class, 'show']);
    Route::patch('/{post}', [PostController::class, 'update']);
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
    Route::delete('/', [FollowController::class, 'destroy']);
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
