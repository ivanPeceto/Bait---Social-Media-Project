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
        DB::table('banners')->insert([
            'url_banners' => 'banners/default.jpg',
            'created_at' => now(), 
            'updated_at' => now()
        ]);
    }
}