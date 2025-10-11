<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Modules\UserData\Domain\Models\User;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run()
    {
        $admin = User::where('email', 'admin@example.com')->first();

        if (!$admin) {
            DB::table('users')->insert([
                'id' => 1,
                'username' => 'admin',
                'name' => 'admin',
                'email' => 'admin@example.com',
                'email_verified_at' => now(),
                'password' => bcrypt('admin123'),
                'remember_token' => Str::random(10),
                'role_id' => 3,
                'state_id' => 1,
                'avatar_id' => 1,
                'banner_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('users')->insert([
                'id' => 2,
                'username' => 'moderator',
                'name' => 'moderator',
                'email' => 'mod@example.com',
                'email_verified_at' => now(),
                'password' => bcrypt('mod12345'),
                'remember_token' => Str::random(10),
                'role_id' => 2,
                'state_id' => 1,
                'avatar_id' => 1,
                'banner_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('users')->insert([
                'id' => 3,
                'username' => 'user',
                'name' => 'user',
                'email' => 'user@example.com',
                'email_verified_at' => now(),
                'password' => bcrypt('user1234'),
                'remember_token' => Str::random(10),
                'role_id' => 1,
                'state_id' => 1,
                'avatar_id' => 1,
                'banner_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $this->command->info('Users already exist.');
        }
    }
}
