<?php

namespace Database\Factories;

use App\Modules\UserData\Domain\Models\Avatar;
use Illuminate\Database\Eloquent\Factories\Factory;

class AvatarFactory extends Factory
{
    protected $model = Avatar::class;

    public function definition(): array
    {
        return [
            // Corregido: Genera una ruta falsa local en lugar de una URL externa.
            // Es más rápido y fiable para los tests.
            'url_avatars' => 'avatars/' . $this->faker->unique()->word . '.jpg',
        ];
    }
}