<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Modules\Multimedia\Domain\Models\MultimediaContent;
use App\Modules\Multimedia\Domain\Models\Post;

class MultimediaContentFactory extends Factory
{
    protected $model = MultimediaContent::class;

    public function definition()
    {
        return [
            'url_multimedia_contents' => $this->faker->imageUrl(),
            'type_multimedia_contents' => $this->faker->randomElement(['image', 'video']),
            'post_id' => Post::factory(),
        ];
    }
}