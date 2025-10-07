<?php

namespace Database\Seeders;

use App\Modules\UserData\Domain\Models\User;
use App\Modules\UserData\Domain\Models\UserRole;
use App\Modules\UserData\Domain\Models\UserState;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserRolesSeeder::class,
            UserStateSeeder::class,
            ReactionTypesSeeder::class,
            AvatarSeeder::class,
            BannerSeeder::class,
        ]);
        
        $adminRole = UserRole::where('name', 'admin')->firstOrFail();
        $modRole = UserRole::where('name', 'moderator')->firstOrFail();
        $activeState = UserState::where('name', 'active')->firstOrFail();

        User::factory()->create([
            'name' => 'Admin',
            'username' => 'admin',
            'email' => 'admin@admin.com',
            'password' => Hash::make('admin'),
            'role_id' => $adminRole->id,
            'state_id' => $activeState->id,
        ]);

        User::factory()->create([
            'name' => 'Moderator',
            'username' => 'mod',
            'email' => 'mod@mod.com',
            'password' => Hash::make('mod'),
            'role_id' => $modRole->id,
            'state_id' => $activeState->id,
        ]);
    }
}