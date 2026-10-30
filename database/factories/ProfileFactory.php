<?php

namespace Database\Factories;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Profile>
 */
class ProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $goal = fake()->randomElement(['bulk', 'cut', 'maintain']);

        // Realistic calorie targets depend on the goal.
        $calories = match ($goal) {
            'bulk' => fake()->numberBetween(28, 38) * 100,
            'cut' => fake()->numberBetween(16, 24) * 100,
            'maintain' => fake()->numberBetween(22, 30) * 100,
        };

        return [
            'user_id' => User::factory(),
            'goal' => $goal,
            'daily_calories' => $calories,
            'daily_protein' => fake()->numberBetween(12, 22) * 10,
            'bio' => fake()->optional(0.8)->randomElement([
                'Training for my first powerlifting meet.',
                'Busy student, so I meal prep on Sundays.',
                'Trying to hit 180g of protein without living on chicken.',
                'Push pull legs, six days a week.',
                'Cutting for summer, then back to bulking.',
                'Vegetarian lifter looking for high protein ideas.',
                'Coming back to the gym after a long break.',
                'Big fan of cheap, simple, high protein meals.',
            ]),
        ];
    }
}
