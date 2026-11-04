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
     * Likes a recipe by default - in the seeder I use for($comment, 'likeable')
     * to like comments as well.
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
