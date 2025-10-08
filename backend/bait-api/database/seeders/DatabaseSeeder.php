<?php

namespace Database\Seeders;

<<<<<<< HEAD
use App\Modules\UserData\Domain\Models\User;
use App\Modules\UserData\Domain\Models\UserRole;
use App\Modules\UserData\Domain\Models\UserState;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
=======
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
>>>>>>> feature/backend/documentation

class DatabaseSeeder extends Seeder
{
    /**
<<<<<<< HEAD
     * Seed the application's database.
=======
     * Run the database seeds.
>>>>>>> feature/backend/documentation
     */
    public function run(): void
    {
        $this->call([
<<<<<<< HEAD
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
            'password' => Hash::make('adminadmin'),
            'role_id' => $adminRole->id,
            'state_id' => $activeState->id,
        ]);

        User::factory()->create([
            'name' => 'Moderator',
            'username' => 'moderator',
            'email' => 'moderator@moderator.com',
            'password' => Hash::make('moderator'),
            'role_id' => $modRole->id,
            'state_id' => $activeState->id,
        ]);
    }
}
=======
            AvatarSeeder::class,
            BannerSeeder::class,
            UserRolesSeeder::class,
            UserStateSeeder::class,
            ReactionTypesSeeder::class,
            AdminUserSeeder::class
        ]);
    }
}
>>>>>>> feature/backend/documentation
