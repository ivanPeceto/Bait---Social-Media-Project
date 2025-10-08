<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserStateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('user_states')->insert([
            ['id'=> '1','name' => 'active','created_at' => now(), 'updated_at' => now()], 
            ['id'=> '2','name' => 'suspended','created_at' => now(), 'updated_at' => now()],
            ['id'=> '3','name' => 'deleted','created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
