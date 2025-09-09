<?php

namespace Database\Factories;

use App\Modules\UserData\Domain\Models\Banner;
use Illuminate\Database\Eloquent\Factories\Factory;

class BannerFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Banner::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        // Genera una ruta de imagen de marcador de posición.
        // En las pruebas de subida, usaremos un archivo falso real.
        return [
            'url_banners' => 'banners/' . $this->faker->unique()->word . '.jpg',
        ];
    }
}