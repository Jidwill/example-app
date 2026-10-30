<?php

namespace Database\Factories;

use App\Models\Like;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Like>
 */
class LikeFactory extends Factory
{
    /**
     * Define the model's default state.
     * By default a like is on a recipe; seeders use for($model, 'likeable')
     * to like a comment instead.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'likeable_id' => Recipe::factory(),
            'likeable_type' => Recipe::class,
        ];
    }
}
