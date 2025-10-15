<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Modules\UserData\Domain\Models\Avatar;

class AvatarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('avatars')->insert([
            'url_avatars' => 'avatars/default.jpg',
        ]);
    }
}