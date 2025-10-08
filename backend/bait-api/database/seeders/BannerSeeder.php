<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class BannerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Corregido a 'banners' (plural) para que coincida con la tabla
        DB::table('banners')->insert([
            // Corregido a 'url_banners' (plural) para que coincida con la columna
            'url_banners' => 'banners/default.jpg',
            'created_at' => now(), 
            'updated_at' => now()
        ]);
    }
}