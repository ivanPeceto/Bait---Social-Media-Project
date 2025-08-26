<?php

namespace App\Modules\UserData\Domain\Models;

use Illuminate\Database\Eloquent\Model;

class Avatar extends Model
{
    protected $fillable = [
        'url_avatar',
    ];

    public function users()
    {
        return $this->hasOne(User::class, 'avatar_id');
    }
}
