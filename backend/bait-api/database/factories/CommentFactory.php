<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Modules\Multimedia\Domain\Models\Comment;
use App\Modules\UserData\Domain\Models\User;
use App\Modules\Multimedia\Domain\Models\Post;

class CommentFactory extends Factory
{
    protected $model = Comment::class;

    public function definition()
    {
        return [
            'content_comments' => $this->faker->sentence(),
            'user_id' => User::factory(),
            'post_id' => Post::factory(),
        ];
    }
}