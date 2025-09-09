<?php

namespace App\Modules\Multimedia\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Modules\UserData\Domain\Models\User;
use App\Modules\Multimedia\Domain\Models\Post;
use App\Modules\Multimedia\Domain\Models\ReactionType;
use Database\Factories\PostReactionFactory;

class PostReaction extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_id',
        'post_id',
        'reaction_type_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function reactionType()
    {
        return $this->belongsTo(ReactionType::class);
    }

    protected static function newFactory()
    {
        return PostReactionFactory::new();
    }
}