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
     * Order matters here because of the foreign keys: users and tags first,
     * then recipes (need users), then comments (need users and recipes),
     * then likes (need everything else).
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
