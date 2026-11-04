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
        // tags are a fixed list so I just seed them by hand instead of using a factory
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
