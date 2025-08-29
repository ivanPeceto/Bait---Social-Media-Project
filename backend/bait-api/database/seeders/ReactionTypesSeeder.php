<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReactionTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $reactionTypes = [
            ['name_reaction_types' => 'Like'],
            ['name_reaction_types' => 'Love'],
            ['name_reaction_types' => 'Haha'],
            ['name_reaction_types' => 'Wow'],
            ['name_reaction_types' => 'Sad'],
            ['name_reaction_types' => 'Angry'],
        ];

        DB::table('reaction_types')->insert($reactionTypes);
    }
}