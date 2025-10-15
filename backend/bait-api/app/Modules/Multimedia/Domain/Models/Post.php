<?php

namespace App\Modules\Multimedia\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Modules\UserData\Domain\Models\User;
use Database\Factories\PostFactory; 
use Illuminate\Database\Eloquent\Relations\HasMany; 
use App\Modules\Multimedia\Domain\Models\PostReaction;


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
}