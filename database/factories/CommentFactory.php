<?php

namespace Database\Factories;

use App\Models\Comment;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Comment>
 */
class CommentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'recipe_id' => Recipe::factory(),
            'body' => fake()->randomElement([
                'Made this for meal prep this week, really good.',
                'Swapped the rice for cauliflower rice to save some calories.',
                'Added an extra 50g of rice since I am bulking.',
                'Great macros for how filling it is.',
                'Tried this with turkey instead and it worked well.',
                'Does this freeze well?',
                'Doubled the recipe and it lasted me four days.',
                'A bit bland for me, so I added extra chilli.',
                'Perfect post workout meal.',
                'How long does it keep in the fridge?',
                'My go to lunch now, thanks for sharing.',
                'Used Greek yoghurt instead of the sauce and it was great.',
                'Good recipe, but my macros came out slightly higher.',
                'Easy enough to make after a late gym session.',
            ]),
        ];
    }
}
