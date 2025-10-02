<?php

use App\Http\Controllers\MultimediaManagementController;
use App\Http\Controllers\UserManagementController;
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

Route::prefix('privileged/users/{user}')->middleware(['auth:api'])->group(function () {
    Route::put('/update',    [ProfileController::class, 'updateUser'])
        ->middleware('role:admin,moderator')
        ->name('privileged.users.update');

    Route::put('/password',  [ProfileController::class, 'changeUserPassword'])
        ->middleware('role:admin')
        ->name('privileged.users.changePasswor');

    Route::delete('/avatar', [AvatarController::class, 'destroyUserAvatar'])
        ->middleware('role:admin,moderator')
        ->name('privileged.users.destroyBanner');

    Route::delete('/banner', [BannerController::class, 'destroyUserBanner'])
        ->middleware('role:admin,moderator')
        ->name('privileged.users.destroyBanner');

    Route::post('/suspend', [UserManagementController::class, 'suspend'])
        ->middleware('role:admin,moderator')
        ->name('privileged.users.suspend');

    Route::post('/activate', [UserManagementController::class, 'activate'])
        ->middleware('role:admin,moderator')
        ->name('privileged.users.activate');
});

Route::middleware('auth:api')->prefix('profile')->group(function () {
    Route::get('/show',        [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/update',      [ProfileController::class, 'updateSelf'])->name('profile.update');
    Route::put('/password',    [ProfileController::class, 'changeSelfPassword'])->name('profile.password');
});

Route::prefix('roles')->middleware(['auth:api', 'role:admin'])->group(function () {
    Route::get('/',          [UserRoleController::class, 'index'])->name('roles.index');
    Route::post('/',         [UserRoleController::class, 'create'])->name('roles.create');
    Route::put('/{role}',    [UserRoleController::class, 'update'])->name('roles.update');
    Route::delete('/{role}', [UserRoleController::class, 'destroy'])->name('roles.destroy');
});

Route::prefix('states')->middleware(['auth:api', 'role:admin'])->group(function () {
    Route::get('/',           [UserStateController::class, 'index'])->name('states.index');
    Route::post('/',          [UserStateController::class, 'create'])->name('states.create');
    Route::put('/{state}',    [UserStateController::class, 'update'])->name('states.update');
    Route::delete('/{state}', [UserStateController::class, 'destroy'])->name('states.destroy');
});

Route::prefix('avatars')->middleware('auth:api')->group(function () {
    Route::post('/upload',  [AvatarController::class, 'upload'])->name('avatars.upload');
    Route::get('/{avatar}', [AvatarController::class, 'show'])->name('avatars.show');
    Route::delete('/self',  [AvatarController::class, 'destroySelf'])->name('avatars.destroySelf');
});

Route::prefix('banners')->middleware('auth:api')->group(function () {
    Route::post('/upload',  [BannerController::class, 'upload'])->name('banner.upload');
    Route::get('/{banner}', [BannerController::class, 'show'])->name('banner.show');
    Route::delete('/self',  [BannerController::class, 'destroySelf'])->name('banner.destroySelf');
});

/*----End UserData routes--------------*/

/*----------------------------------------------------------------------------------------------*/

/*MultiMedia routes*/

Route::middleware('auth:api')->prefix('posts')->group(function () {
    Route::get('/',         [PostController::class, 'index'])->name('posts.');
    Route::post('/',        [PostController::class, 'store']);
    Route::get('/{post}',   [PostController::class, 'show']);
    Route::patch('/{post}', [PostController::class, 'update']);
    Route::delete('/{post}',[PostController::class, 'destroy']);
});

Route::middleware('auth:api')->prefix('comments')->group(function () {
    Route::get('/',             [CommentController::class, 'index']);
    Route::post('/',            [CommentController::class, 'store']);
    Route::get('/{comment}',    [CommentController::class, 'show']);
    Route::put('/{comment}',    [CommentController::class, 'update']);
    Route::delete('/{comment}', [CommentController::class, 'destroy']);
});

Route::middleware('auth:api')->prefix('reposts')->group(function () {
    Route::post('/',           [RepostController::class, 'store']);
    Route::delete('/{repost}', [RepostController::class, 'destroy']);
});

Route::middleware('auth:api')->prefix('multimedia-contents')->group(function () {
    Route::post('/',                      [MultimediaContentController::class, 'store']);
    Route::delete('/{multimediaContent}', [MultimediaContentController::class, 'destroy']);
});

Route::middleware('auth:api')->prefix('post-reactions')->group(function () {
    Route::post('/', [PostReactionController::class, 'store']);
});

Route::middleware(['auth:api', 'role:admin,moderator'])->prefix('privileged/multimedia')->group(function () {
    Route::delete('/post/{post}',        [MultimediaManagementController::class, 'destroyPost'])->name('privileged.post.destroy');
    Route::delete('/comment/{comment}',  [MultimediaManagementController::class, 'destroyComment'])->name('privileged.comment.destroy');
    Route::delete('/repost/{repost}',    [MultimediaManagementController::class, 'destroyRepost'])->name('privileged.repost.destroy');
    Route::delete('/reaction/{reaction}',[MultimediaManagementController::class, 'destroyReaction'])->name('privileged.reaction.destroy');
});

/*end MultiMedia routes*/

/*----------------------------------------------------------------------------------------------*/

/*UserInteractions routes*/

Route::middleware('auth:api')->prefix('notifications')->group(function () {
    Route::get('/',               [NotificationController::class, 'index']);
    Route::get('/{notification}', [NotificationController::class, 'show']);
    Route::put('/{notification}', [NotificationController::class, 'update']);
});

Route::middleware('auth:api')->prefix('follows')->group(function () {
    Route::post('/',    [FollowController::class, 'store']);
    Route::delete('/',  [FollowController::class, 'destroy']);
});

Route::middleware('auth:api')->prefix('chats')->group(function () {
    Route::get('/',         [ChatController::class, 'index']);
    Route::post('/',        [ChatController::class, 'store']);
    Route::get('/{chat}',   [ChatController::class, 'show']);
});

Route::middleware('auth:api')->prefix('chats/{chat}')->group(function () {
    Route::get('messages',  [MessageController::class, 'index']); 
    Route::post('messages', [MessageController::class, 'store']); 
});

/*end UserInteractions routes*/
