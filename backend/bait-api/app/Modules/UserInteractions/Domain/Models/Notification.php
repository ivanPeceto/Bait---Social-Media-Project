<?php

namespace App\Modules\UserInteractions\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Modules\UserData\Domain\Models\User;
use Database\Factories\NotificationFactory;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'type_notifications',
        'content_notifications',
        'is_read_notifications',
        'user_id',
    ];

    protected $casts = [
        'is_read_notifications' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    protected static function newFactory()
    {
        return NotificationFactory::new();
    }
}