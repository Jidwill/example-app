<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Database\Seeder;

class CommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Each comment is written by a random existing user on a random existing recipe.
        Comment::factory()
            ->count(120)
            ->recycle(User::all())
            ->recycle(Recipe::all())
            ->create();
    }
}
