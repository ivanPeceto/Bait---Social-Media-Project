<?php

namespace App\Modules\Multimedia\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Modules\UserData\Domain\Models\User;
use Database\Factories\PostFactory; 
use Illuminate\Database\Eloquent\Relations\HasMany; 
use App\Modules\Multimedia\Domain\Models\PostReaction;
use App\Modules\Multimedia\Domain\Models\Repost;
use App\Modules\Multimedia\Domain\Models\Comment;
use App\Modules\Multimedia\Domain\Models\MultimediaContent;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'content_posts',
        'user_id'
    ];

    /**
     * Get the user that owns the post.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function comments(): HasMany 
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return PostFactory::new();
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(PostReaction::class);
    }

    public function reposts(): HasMany
    {
        return $this->hasMany(Repost::class);
    }

    /**
     * AÑADIR ESTA FUNCIÓN
     * Get the multimedia content associated with the post.
     */
    public function multimedia_contents()
    {
        return $this->hasMany(MultimediaContent::class, 'post_id');
    }
}