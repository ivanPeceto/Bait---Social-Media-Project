<?php

namespace App\Modules\UserData\Domain\Models;

use Database\Factories\UserStateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserState extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'state_id');
    }

    protected static function newFactory()
    {
        return UserStateFactory::new();
    }
}