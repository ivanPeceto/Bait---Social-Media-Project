<?php

namespace App\Modules\UserInteractions\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Modules\UserData\Domain\Models\User;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'type_notifications',
        'content_notifications',
        'is_read_notifications',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}