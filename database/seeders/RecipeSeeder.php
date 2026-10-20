<?php

namespace Database\Seeders;

use App\Models\Recipe;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RecipeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       Recipe::create(['title' => 'Chicken rice bowl', 'protein' => 45]);
        Recipe::create(['title' => 'Protein oats', 'protein' => 35]);
        Recipe::create(['title' => 'Tuna pasta', 'protein' => 40]);
    }
}
