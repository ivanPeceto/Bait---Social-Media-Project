<?php

namespace App\Modules\UserData\Domain\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Database\Factories\UserFactory;

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

}
