<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Like;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Database\Seeder;

class LikeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $recipes = Recipe::all();
        $comments = Comment::all();

        foreach (User::all() as $user) {
            // people can't like their own recipes or comments
            $otherRecipes = $recipes->where('user_id', '!=', $user->id);
            $otherComments = $comments->where('user_id', '!=', $user->id);

            // random() never picks the same item twice, so there are no duplicate likes,
            // and the same Like factory works for both because likes are polymorphic
            foreach ($otherRecipes->random(fake()->numberBetween(0, 6)) as $recipe) {
                Like::factory()->for($user)->for($recipe, 'likeable')->create();
            }

            foreach ($otherComments->random(fake()->numberBetween(0, 6)) as $comment) {
                Like::factory()->for($user)->for($comment, 'likeable')->create();
            }
        }
    }
}
