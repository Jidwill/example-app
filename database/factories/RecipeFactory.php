<?php

namespace Database\Factories;

use App\Models\Recipe;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Recipe>
 */
class RecipeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->randomElement([
                'Chicken rice bowl',
                'Protein oats',
                'Tuna pasta',
                'Beef chilli',
                'Turkey wraps',
                'Greek yoghurt bowl'  
            ]),
                    'protein' => fake()->numberBetween(20, 60),

        ];
    }
}
