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
        ['id'=> '1','name' => 'user'], ['id'=> '2', 'name' => 'moderator'], ['id'=> '3', 'name' => 'admin'],
        ]);
    }
}
