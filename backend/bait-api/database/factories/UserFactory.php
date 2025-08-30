<?php

namespace Database\Factories;

use App\Modules\UserData\Domain\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition()
    {
        return [
            'username' => $this->faker->unique()->userName(),
            'name'     => $this->faker->name(),
            'email'    => $this->faker->unique()->safeEmail(),
            'password' => Hash::make('password'), 
            'role_id'  => 1, 
            'state_id' => 1,
            'avatar_id' => 1,
            'banner_id' => 1,
        ];
    }
}
