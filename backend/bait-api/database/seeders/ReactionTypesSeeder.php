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
            ['name_reaction_types' => 'like'], 
            ['name_reaction_types' => 'love'], 
            ['name_reaction_types' => 'haha'], 
            ['name_reaction_types' => 'wow'],  
            ['name_reaction_types' => 'sad'],  
            ['name_reaction_types' => 'angry'],
        ];

        DB::table('reaction_types')->insert($reactionTypes);
    }
}