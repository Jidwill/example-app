<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tags are a fixed list the admin manages, so they are seeded by hand
        // rather than with a factory.
        $tags = [
            'Bulk',
            'Cut',
            'Maintain',
            'High protein',
            'Low carb',
            'Meal prep',
            'Quick',
            'Budget',
            'Vegetarian',
            'Breakfast',
            'Post workout',
        ];

        foreach ($tags as $name) {
            Tag::create(['name' => $name]);
        }
    }
}
