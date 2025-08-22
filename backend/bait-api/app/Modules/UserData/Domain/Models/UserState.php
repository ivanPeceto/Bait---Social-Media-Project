<?php

namespace App\Modules\UserData\Domain\Models;

use Illuminate\Database\Eloquent\Model;

class UserState extends Model
{
    protected $fillable = [
        'name',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'state_id');
    }
}