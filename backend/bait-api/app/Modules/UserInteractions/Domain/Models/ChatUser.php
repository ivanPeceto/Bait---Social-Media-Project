<?php

namespace App\Modules\UserInteractions\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ChatUser extends Pivot
{
    use HasFactory;

    protected $table = 'chat_users';
    public $timestamps = false;
}