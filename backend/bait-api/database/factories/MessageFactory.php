<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Modules\UserInteractions\Domain\Models\Message;
use App\Modules\UserInteractions\Domain\Models\Chat;
use App\Modules\UserData\Domain\Models\User;

class MessageFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Message::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'content_messages' => $this->faker->sentence(),
            'chat_id' => Chat::factory(),
            'user_id' => User::factory(),
        ];
    }
}