<?php

namespace App\Modules\Multimedia\Domain\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Modules\UserData\Domain\Models\User;
use Database\Factories\CommentFactory;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'content_comments',
        'user_id',
        'post_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    protected static function newFactory()
    {
        return CommentFactory::new();
    }
}