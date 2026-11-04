<?php

namespace Database\Seeders;

use App\Models\Recipe;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class RecipeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $tags = Tag::all();

        // recycle() picks a random existing user for each recipe
        // (otherwise the factory would make a new user for every single one)
        $recipes = Recipe::factory()
            ->count(40)
            ->recycle($users)
            ->create();

        // give each recipe 1-3 random tags (many to many)
        foreach ($recipes as $recipe) {
            $recipe->tags()->attach(
                $tags->random(fake()->numberBetween(1, 3))->pluck('id')
            );
        }
    }
}
