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
        ['Chicken rice bowl', 'Grilled chicken thighs over rice with peppers and a soy glaze.', ['200g chicken thigh', '150g cooked rice', '1 red pepper', '1 tbsp soy sauce', '1 tsp honey'], 45, 60, 12],
        ['Protein overnight oats', 'Oats soaked overnight with whey and Greek yoghurt. No cooking needed.', ['60g oats', '1 scoop whey protein', '150g Greek yoghurt', '100ml milk', 'Handful of berries'], 42, 55, 9],
        ['Beef chilli', 'A big batch chilli that freezes well for meal prep.', ['500g 5% beef mince', '1 tin kidney beans', '1 tin chopped tomatoes', '1 onion', '2 tsp chilli powder'], 38, 30, 11],
        ['Tuna pasta bake', 'Cheap and filling pasta bake with tinned tuna and light cheese.', ['2 tins tuna', '200g pasta', '1 tin sweetcorn', '50g light cheddar', '200ml passata'], 40, 65, 10],
        ['Turkey mince wraps', 'Quick wraps with seasoned turkey mince and salsa.', ['250g turkey mince', '2 wholemeal wraps', '1 tsp fajita seasoning', 'Salsa', 'Lettuce'], 44, 42, 10],
        ['Greek yoghurt bowl', 'High protein breakfast bowl with granola and fruit.', ['250g 0% Greek yoghurt', '30g granola', '1 banana', '1 tsp honey'], 30, 50, 5],
        ['Salmon and sweet potato', 'Oven baked salmon with roasted sweet potato and broccoli.', ['1 salmon fillet', '1 large sweet potato', '100g broccoli', '1 tsp olive oil'], 34, 45, 18],
        ['Egg white omelette', 'Fluffy omelette packed with vegetables and a little feta.', ['200ml egg whites', '2 whole eggs', 'Spinach', 'Cherry tomatoes', '20g feta'], 38, 6, 14],
        ['Chicken fajita traybake', 'Everything on one tray, ready in under half an hour.', ['300g chicken breast', '2 peppers', '1 red onion', 'Fajita seasoning', '2 tortillas'], 48, 40, 9],
        ['Lentil and chickpea curry', 'Vegetarian curry with lentils, chickpeas and spinach.', ['100g red lentils', '1 tin chickpeas', '1 tin light coconut milk', 'Spinach', '2 tbsp curry paste'], 26, 60, 14],
        ['Prawn stir fry', 'Fast stir fry with prawns and egg noodles.', ['200g king prawns', '1 nest egg noodles', 'Stir fry vegetables', '1 tbsp soy sauce', '1 tsp sesame oil'], 36, 50, 8],
        ['Cottage cheese pancakes', 'Protein pancakes made with cottage cheese and oats.', ['150g cottage cheese', '50g oats', '2 eggs', '1 tsp baking powder', 'Berries to serve'], 35, 38, 12],
        ['Steak and potato bowl', 'Lean steak with crispy potatoes and green beans.', ['200g sirloin steak', '250g baby potatoes', '100g green beans', '1 tsp olive oil'], 46, 45, 16],
        ['Tofu peanut noodles', 'Crispy tofu with a peanut sauce over rice noodles.', ['200g firm tofu', '80g rice noodles', '1 tbsp peanut butter', '1 tbsp soy sauce', 'Spring onions'], 28, 62, 18],
        ['Chicken pesto pasta', 'Simple pasta with chicken, pesto and cherry tomatoes.', ['200g chicken breast', '100g pasta', '1 tbsp pesto', 'Cherry tomatoes', 'Rocket'], 50, 70, 15],
        ['Cod and chips', 'A lighter fish and chips made in the air fryer.', ['1 cod fillet', '250g potatoes', '30g breadcrumbs', '1 egg', 'Peas'], 38, 55, 7],
        ['Protein smoothie', 'Post workout shake with banana, oats and peanut butter.', ['1 scoop whey protein', '1 banana', '30g oats', '1 tbsp peanut butter', '300ml milk'], 40, 60, 14],
        ['Turkey meatballs', 'Lean turkey meatballs in tomato sauce with spaghetti.', ['300g turkey mince', '1 egg', '80g spaghetti', '200ml passata', 'Basil'], 45, 55, 10],
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        [$title, $description, $ingredients, $protein, $carbs, $fat] = fake()->randomElement(static::$meals);

        return [
            'user_id' => User::factory(),
            'title' => $title,
            'description' => $description,
            'ingredients' => implode("\n", $ingredients),
            'method' => implode("\n", [
                '1. Prepare and weigh all of the ingredients.',
                '2. Cook the protein until it is cooked through.',
                '3. Add the remaining ingredients and cook for '.fake()->numberBetween(5, 20).' minutes.',
                '4. Split into portions and serve, or store for meal prep.',
            ]),
            'prep_minutes' => fake()->randomElement([10, 15, 20, 25, 30, 40, 45, 60]),
            'servings' => fake()->numberBetween(1, 4),
            'protein' => $this->vary($protein),
            'carbs' => $this->vary($carbs),
            'fat' => $this->vary($fat),
            'image_path' => null,
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
