<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Modules\UserData\Domain\Models\Avatar;

class AvatarSeeder extends Seeder
{
    public function run(): void
    {
        Avatar::firstOrCreate(
            ['url_avatars' => 'avatars/default.jpg'],
            ['url_avatars' => 'avatars/default.jpg']
        );
    }
}