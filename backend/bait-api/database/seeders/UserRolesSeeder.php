<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class UserRolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('user_roles')->insert([
            ['id'=> '1','name' => 'user','created_at' => now(), 'updated_at' => now()], 
            ['id'=> '2', 'name' => 'moderator', 'created_at' => now(), 'updated_at' => now()], 
            ['id'=> '3', 'name' => 'admin', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
