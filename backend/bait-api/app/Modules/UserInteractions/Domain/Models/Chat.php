<?php

namespace App\Modules\UserInteractions\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Modules\UserData\Domain\Models\User;
use App\Modules\UserInteractions\Domain\Models\Message;
use Database\Factories\ChatFactory;

class Chat extends Model
{
    use HasFactory;

    protected $fillable = [];

    public function users()
    {
        return $this->belongsToMany(User::class, 'chat_users');
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    protected static function newFactory()
    {
        return ChatFactory::new();
    }
}