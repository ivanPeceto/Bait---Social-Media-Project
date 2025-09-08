<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Modules\Multimedia\Domain\Models\PostReaction;
use App\Modules\UserData\Domain\Models\User;
use App\Modules\Multimedia\Domain\Models\Post;
use App\Modules\Multimedia\Domain\Models\ReactionType;

class PostReactionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PostReaction::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'post_id' => Post::factory(),
            'reaction_type_id' => ReactionType::factory(),
        ];
    }
}