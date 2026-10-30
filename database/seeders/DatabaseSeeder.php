<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * The order matters: each seeder needs the rows the previous ones created.
     * Users and tags first, then recipes (need users), then comments (need
     * users and recipes), then likes (need users, recipes and comments).
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            TagSeeder::class,
            RecipeSeeder::class,
            CommentSeeder::class,
            LikeSeeder::class,
        ]);
    }
}
