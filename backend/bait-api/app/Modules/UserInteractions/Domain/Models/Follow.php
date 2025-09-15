<?php

namespace App\Modules\UserInteractions\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Modules\UserData\Domain\Models\User;
use Database\Factories\FollowFactory;

class Follow extends Model
{
    use HasFactory;

    protected $fillable = [
        'follower_id',
        'following_id',
    ];

    /**
     * The user that is following.
     */
    public function follower()
    {
        return $this->belongsTo(User::class, 'follower_id');
    }

    /**
     * The user that is being followed.
     */
    public function following()
    {
        return $this->belongsTo(User::class, 'following_id');
    }

    protected static function newFactory()
    {
        return FollowFactory::new();
    }
}