<?php

namespace Database\Seeders;

use App\Models\Recipe;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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

        // clear out photos from the last time the database was seeded
        Storage::disk('public')->deleteDirectory('recipes/seeded');

        foreach ($recipes as $recipe) {
            // attach 1-3 random tags to each recipe through the pivot table
            $recipe->tags()->attach(
                $tags->random(fake()->numberBetween(1, 3))->pluck('id')
            );

            $this->attachPhoto($recipe);
        }
    }

    /**
     * Gives the recipe a photo if there's one for that meal in database/seeders/photos
     * (downloaded with "sail artisan recipes:fetch-photos").
     * Each recipe gets its own copy, so deleting one recipe can't delete another's photo.
     */
    private function attachPhoto(Recipe $recipe): void
    {
        $photo = database_path('seeders/photos/'.Str::slug($recipe->title).'.jpg');

        if (! file_exists($photo)) {
            return;
        }

        $path = "recipes/seeded/{$recipe->id}.jpg";
        Storage::disk('public')->put($path, file_get_contents($photo));

        $recipe->update(['image_path' => $path]);
    }
}
