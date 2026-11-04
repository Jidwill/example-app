<?php

namespace Database\Factories;

use App\Models\Recipe;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Recipe>
 */
class RecipeFactory extends Factory
{
    /**
     * Real meals with rough macros per serving (protein, carbs, fat in grams).
     * The factory picks one at random and changes the macros a bit so
     * the seeded recipes look realistic.
     */
    protected static array $meals = [
        [
            'title' => 'Chicken rice bowl',
            'description' => 'Grilled chicken thighs over rice with peppers and a soy glaze.',
            'ingredients' => [
                '200g chicken thigh',
                '150g cooked rice',
                '1 red pepper',
                '1 tbsp soy sauce',
                '1 tsp honey',
            ],
            'method' => [
                'Slice the chicken and pepper.',
                'Fry the chicken for 8 minutes, then add the pepper.',
                'Stir in the soy sauce and honey and serve over the rice.',
            ],
            'macros' => [45, 60, 12],
        ],
        [
            'title' => 'Protein overnight oats',
            'description' => 'Oats soaked overnight with whey and Greek yoghurt. No cooking needed.',
            'ingredients' => [
                '60g oats',
                '1 scoop whey protein',
                '150g Greek yoghurt',
                '100ml milk',
                'Handful of berries',
            ],
            'method' => [
                'Mix the oats, whey, yoghurt and milk in a jar.',
                'Leave in the fridge overnight.',
                'Top with the berries in the morning.',
            ],
            'macros' => [42, 55, 9],
        ],
        [
            'title' => 'Beef chilli',
            'description' => 'A big batch chilli that freezes well for meal prep.',
            'ingredients' => [
                '500g 5% beef mince',
                '1 tin kidney beans',
                '1 tin chopped tomatoes',
                '1 onion',
                '2 tsp chilli powder',
            ],
            'method' => [
                'Fry the onion until soft.',
                'Brown the mince, then add the chilli powder.',
                'Add the tomatoes and beans and simmer for 30 minutes.',
            ],
            'macros' => [38, 30, 11],
        ],
        [
            'title' => 'Tuna pasta bake',
            'description' => 'Cheap and filling pasta bake with tinned tuna and light cheese.',
            'ingredients' => [
                '2 tins tuna',
                '200g pasta',
                '1 tin sweetcorn',
                '50g light cheddar',
                '200ml passata',
            ],
            'method' => [
                'Cook the pasta and drain it.',
                'Mix in the tuna, sweetcorn and passata.',
                'Top with the cheese and bake at 200C for 20 minutes.',
            ],
            'macros' => [40, 65, 10],
        ],
        [
            'title' => 'Turkey mince wraps',
            'description' => 'Quick wraps with seasoned turkey mince and salsa.',
            'ingredients' => [
                '250g turkey mince',
                '2 wholemeal wraps',
                '1 tsp fajita seasoning',
                'Salsa',
                'Lettuce',
            ],
            'method' => [
                'Brown the turkey mince with the seasoning.',
                'Warm the wraps.',
                'Fill with the mince, lettuce and salsa.',
            ],
            'macros' => [44, 42, 10],
        ],
        [
            'title' => 'Greek yoghurt bowl',
            'description' => 'High protein breakfast bowl with granola and fruit.',
            'ingredients' => [
                '250g 0% Greek yoghurt',
                '30g granola',
                '1 banana',
                '1 tsp honey',
            ],
            'method' => [
                'Spoon the yoghurt into a bowl.',
                'Slice the banana on top.',
                'Add the granola and drizzle with honey.',
            ],
            'macros' => [30, 50, 5],
        ],
        [
            'title' => 'Salmon and sweet potato',
            'description' => 'Oven baked salmon with roasted sweet potato and broccoli.',
            'ingredients' => [
                '1 salmon fillet',
                '1 large sweet potato',
                '100g broccoli',
                '1 tsp olive oil',
            ],
            'method' => [
                'Cube the sweet potato and roast at 200C for 15 minutes.',
                'Add the salmon and broccoli to the tray.',
                'Roast for another 12 minutes.',
            ],
            'macros' => [34, 45, 18],
        ],
        [
            'title' => 'Egg white omelette',
            'description' => 'Fluffy omelette packed with vegetables and a little feta.',
            'ingredients' => [
                '200ml egg whites',
                '2 whole eggs',
                'Spinach',
                'Cherry tomatoes',
                '20g feta',
            ],
            'method' => [
                'Whisk the egg whites and eggs together.',
                'Pour into a hot pan and add the spinach and tomatoes.',
                'Crumble the feta on top and fold over.',
            ],
            'macros' => [38, 6, 14],
        ],
        [
            'title' => 'Chicken fajita traybake',
            'description' => 'Everything on one tray, ready in under half an hour.',
            'ingredients' => [
                '300g chicken breast',
                '2 peppers',
                '1 red onion',
                'Fajita seasoning',
                '2 tortillas',
            ],
            'method' => [
                'Slice the chicken, peppers and onion.',
                'Toss with the seasoning on a tray.',
                'Roast at 200C for 25 minutes and serve in the tortillas.',
            ],
            'macros' => [48, 40, 9],
        ],
        [
            'title' => 'Lentil and chickpea curry',
            'description' => 'Vegetarian curry with lentils, chickpeas and spinach.',
            'ingredients' => [
                '100g red lentils',
                '1 tin chickpeas',
                '1 tin light coconut milk',
                'Spinach',
                '2 tbsp curry paste',
            ],
            'method' => [
                'Fry the curry paste for a minute.',
                'Add the lentils, chickpeas and coconut milk.',
                'Simmer for 20 minutes, then stir in the spinach.',
            ],
            'macros' => [26, 60, 14],
        ],
        [
            'title' => 'Prawn stir fry',
            'description' => 'Fast stir fry with prawns and egg noodles.',
            'ingredients' => [
                '200g king prawns',
                '1 nest egg noodles',
                'Stir fry vegetables',
                '1 tbsp soy sauce',
                '1 tsp sesame oil',
            ],
            'method' => [
                'Cook the noodles and drain them.',
                'Stir fry the prawns and vegetables in the sesame oil.',
                'Add the noodles and soy sauce and toss together.',
            ],
            'macros' => [36, 50, 8],
        ],
        [
            'title' => 'Cottage cheese pancakes',
            'description' => 'Protein pancakes made with cottage cheese and oats.',
            'ingredients' => [
                '150g cottage cheese',
                '50g oats',
                '2 eggs',
                '1 tsp baking powder',
                'Berries to serve',
            ],
            'method' => [
                'Blend everything except the berries into a batter.',
                'Cook small pancakes for 2 minutes each side.',
                'Serve with the berries.',
            ],
            'macros' => [35, 38, 12],
        ],
        [
            'title' => 'Steak and potato bowl',
            'description' => 'Lean steak with crispy potatoes and green beans.',
            'ingredients' => [
                '200g sirloin steak',
                '250g baby potatoes',
                '100g green beans',
                '1 tsp olive oil',
            ],
            'method' => [
                'Halve the potatoes and roast at 200C for 30 minutes.',
                'Fry the steak for 3 minutes each side and let it rest.',
                'Steam the green beans and serve everything together.',
            ],
            'macros' => [46, 45, 16],
        ],
        [
            'title' => 'Tofu peanut noodles',
            'description' => 'Crispy tofu with a peanut sauce over rice noodles.',
            'ingredients' => [
                '200g firm tofu',
                '80g rice noodles',
                '1 tbsp peanut butter',
                '1 tbsp soy sauce',
                'Spring onions',
            ],
            'method' => [
                'Press and cube the tofu, then fry until crispy.',
                'Mix the peanut butter and soy sauce with a splash of water.',
                'Toss the noodles, tofu and sauce and top with spring onions.',
            ],
            'macros' => [28, 62, 18],
        ],
        [
            'title' => 'Chicken pesto pasta',
            'description' => 'Simple pasta with chicken, pesto and cherry tomatoes.',
            'ingredients' => [
                '200g chicken breast',
                '100g pasta',
                '1 tbsp pesto',
                'Cherry tomatoes',
                'Rocket',
            ],
            'method' => [
                'Cook the pasta.',
                'Fry the chicken for 10 minutes until cooked through.',
                'Mix with the pesto, tomatoes and rocket.',
            ],
            'macros' => [50, 70, 15],
        ],
        [
            'title' => 'Cod and chips',
            'description' => 'A lighter fish and chips made in the air fryer.',
            'ingredients' => [
                '1 cod fillet',
                '250g potatoes',
                '30g breadcrumbs',
                '1 egg',
                'Peas',
            ],
            'method' => [
                'Cut the potatoes into chips and air fry for 20 minutes.',
                'Dip the cod in egg and breadcrumbs.',
                'Air fry the cod for 12 minutes and serve with peas.',
            ],
            'macros' => [38, 55, 7],
        ],
        [
            'title' => 'Protein smoothie',
            'description' => 'Post workout shake with banana, oats and peanut butter.',
            'ingredients' => [
                '1 scoop whey protein',
                '1 banana',
                '30g oats',
                '1 tbsp peanut butter',
                '300ml milk',
            ],
            'method' => [
                'Add everything to a blender.',
                'Blend for 30 seconds until smooth.',
            ],
            'macros' => [40, 60, 14],
        ],
        [
            'title' => 'Turkey meatballs',
            'description' => 'Lean turkey meatballs in tomato sauce with spaghetti.',
            'ingredients' => [
                '300g turkey mince',
                '1 egg',
                '80g spaghetti',
                '200ml passata',
                'Basil',
            ],
            'method' => [
                'Mix the mince and egg and roll into meatballs.',
                'Brown them, then simmer in the passata for 15 minutes.',
                'Serve over the spaghetti with basil.',
            ],
            'macros' => [45, 55, 10],
        ],
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $meal = fake()->randomElement(static::$meals);
        [$protein, $carbs, $fat] = $meal['macros'];

        return [
            'user_id' => User::factory(),
            'title' => $meal['title'],
            'description' => $meal['description'],
            'ingredients' => implode("\n", $meal['ingredients']),
            'method' => implode("\n", $meal['method']),
            'prep_minutes' => fake()->randomElement([10, 15, 20, 25, 30, 40, 45, 60]),
            'servings' => fake()->numberBetween(1, 4),
            'protein' => $this->vary($protein),
            'carbs' => $this->vary($carbs),
            'fat' => $this->vary($fat),
            'image_path' => null,
            // posted some time in the last few months (comments always come after this)
            'created_at' => fake()->dateTimeBetween('-3 months', '-1 month'),
        ];
    }

    /**
     * Nudges a number up or down by up to 15% so the same meal
     * doesn't always have identical macros.
     */
    private function vary(int $grams): int
    {
        return max(1, (int) round($grams * fake()->randomFloat(2, 0.85, 1.15)));
    }
}
