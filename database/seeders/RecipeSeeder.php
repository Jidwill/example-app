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

        // recycle() makes each recipe belong to a random existing user,
        // instead of the factory creating a brand new user for every recipe.
        $recipes = Recipe::factory()
            ->count(40)
            ->recycle($users)
            ->create();

        // Many to many: give each recipe between 1 and 3 random tags.
        foreach ($recipes as $recipe) {
            $recipe->tags()->attach(
                $tags->random(rand(1, 3))->pluck('id')
            );
        }
    }
}
