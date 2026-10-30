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
            // Users only like other people's content, and each item at most once.
            $otherRecipes = $recipes->where('user_id', '!=', $user->id);
            $otherComments = $comments->where('user_id', '!=', $user->id);

            // Polymorphic: the same Like factory is used for recipes and comments.
            foreach ($otherRecipes->random(rand(0, 6)) as $recipe) {
                Like::factory()->for($user)->for($recipe, 'likeable')->create();
            }

            foreach ($otherComments->random(rand(0, 6)) as $comment) {
                Like::factory()->for($user)->for($comment, 'likeable')->create();
            }
        }
    }
}
