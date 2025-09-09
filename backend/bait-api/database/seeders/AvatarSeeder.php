<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AvatarSeeder extends Seeder
{
    public function run(): void
    {
        // Corregido: todo a plural y ruta consistente.
        DB::table('avatars')->insert([
            'url_avatars' => 'avatars/default.jpg',
        ]);
    }
}