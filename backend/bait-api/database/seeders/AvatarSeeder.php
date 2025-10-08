<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class AvatarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
<<<<<<< HEAD
        DB::table('avatars')->insert([
            'url_avatars' => 'avatars/default.jpg',
=======
        // Corregido a 'avatars' (plural) para que coincida con la tabla
        DB::table('avatars')->insert([
            // Corregido a 'url_avatars' (plural) para que coincida con la columna
            'url_avatars' => 'avatars/default.jpg',
            'created_at' => now(), 
            'updated_at' => now()
>>>>>>> feature/backend/documentation
        ]);
    }
}