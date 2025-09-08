<?php

namespace Database\Factories;

use App\Modules\UserData\Domain\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'username' => $this->faker->unique()->userName(),
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9pMyxS.q2Rj.jP1wL3pWlq', // password
            'remember_token' => Str::random(10),
            'role_id' => 1,
            'state_id' => 1,
            'avatar_id' => 1,
            'banner_id' => 1,
        ];
    }
}