<?php

namespace App\Modules\UserData\Domain\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Database\Factories\UserFactory;
use App\Modules\UserInteractions\Domain\Models\Chat;
use App\Modules\UserInteractions\Domain\Models\Follow;
use App\Modules\UserInteractions\Domain\Models\Notification;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;
   
    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }


    protected $fillable = [
        'username',
        'name',
        'email',
        'password',
        'created_at',
        'updated_at',
        'role_id',
        'state_id',
        'avatar_id',
        'banner_id'
    ];

    protected $hidden = ['password', 'remember_token'];

    //JWT
    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }
    public function getJWTCustomClaims():array
    {
        return[];
    }

    //Entity relations
    public function role() {
        return $this->belongsTo(UserRole::class, 'role_id');
    }

    public function state() {
        return $this->belongsTo(UserState::class, 'state_id');
    }

    public function chats()
    {
        return $this->belongsToMany(Chat::class, 'chat_users');
    }

    public function follows()
    {
        return $this->hasMany(Follow::class, 'follower_id');
    }

    public function following()
    {
        return $this->belongsToMany(User::class, 'follows', 'follower_id', 'following_id');
    }

    public function followers()
    {
        return $this->belongsToMany(User::class, 'follows', 'following_id', 'follower_id');
    }

    public function avatar()
    {
        return $this->belongsTo(Avatar::class, 'avatar_id');
    }

    public function banner()
    {
        return $this->belongsTo(Banner::class, 'banner_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

}
