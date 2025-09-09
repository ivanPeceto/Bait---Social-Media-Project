<?php

namespace App\Modules\UserData\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Database\Factories\AvatarFactory;

class Avatar extends Model
{
    use HasFactory;

    protected $fillable = [
        'url_avatars',
    ];

    // Buena práctica: Si es una relación hasOne, el método es singular.
    public function user()
    {
        return $this->hasOne(User::class, 'avatar_id');
    }

    protected static function newFactory()
    {
        return AvatarFactory::new();
    }
}