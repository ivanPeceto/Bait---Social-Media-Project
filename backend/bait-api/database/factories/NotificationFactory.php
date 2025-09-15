<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Modules\UserInteractions\Domain\Models\Notification;
use App\Modules\UserData\Domain\Models\User;

class NotificationFactory extends Factory
{
    protected $model = Notification::class;

    public function definition()
    {
        return [
            'type_notifications' => $this->faker->randomElement(['new_follow', 'new_comment', 'new_message']),
            'content_notifications' => $this->faker->sentence(),
            'is_read_notifications' => false,
            'user_id' => User::factory(),
        ];
    }
}