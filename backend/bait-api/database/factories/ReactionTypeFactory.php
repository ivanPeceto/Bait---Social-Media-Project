<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Modules\Multimedia\Domain\Models\ReactionType;

class ReactionTypeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ReactionType::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name_reaction_types' => $this->faker->unique()->randomElement(['like', 'love', 'haha', 'wow', 'sad', 'angry']),
        ];
    }
}