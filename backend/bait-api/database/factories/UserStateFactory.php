<?php

namespace Database\Factories;

use App\Modules\UserData\Domain\Models\UserState;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserStateFactory extends Factory
{
    protected $model = UserState::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->word(),
        ];
    }
}