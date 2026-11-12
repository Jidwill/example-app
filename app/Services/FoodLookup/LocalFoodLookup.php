<?php

namespace App\Services\FoodLookup;

use App\Contracts\FoodLookup;
use Illuminate\Support\Str;

/**
 * A second FoodLookup that uses a small built-in list instead of the internet.
 * Used when FOOD_LOOKUP_DRIVER=local (e.g. no wifi, or the API is down on demo day) and in tests.
 * Because it implements the same interface, nothing else in the app has to change to use it.
 * Values are rough per-100g figures for common gym-food ingredients.
 */
class LocalFoodLookup implements FoodLookup
{
    /** name => [kcal, protein, carbs, fat] per 100g */
    private const FOODS = [
        'Chicken breast (raw)' => [120, 22.5, 0, 2.6],
        'Chicken thigh (raw)' => [177, 18.6, 0, 11.4],
        'Turkey mince 5% fat (raw)' => [125, 20.5, 0, 4.6],
        'Beef mince 5% fat (raw)' => [137, 21.4, 0, 5.4],
        'Salmon fillet (raw)' => [208, 20.4, 0, 13.4],
        'Cod fillet (raw)' => [82, 17.8, 0, 0.7],
        'Tinned tuna in water' => [109, 24.9, 0, 1],
        'Eggs (whole)' => [143, 12.6, 0.7, 9.5],
        'Egg whites' => [52, 10.9, 0.7, 0.2],
        'Greek yoghurt 0% fat' => [57, 10.3, 3.6, 0.2],
        'Cottage cheese' => [98, 11.1, 3.4, 4.3],
        'Whey protein powder' => [400, 78, 8, 6],
        'Tofu (firm)' => [144, 15.8, 2.8, 8.7],
        'Rolled oats' => [379, 13.2, 67.7, 6.5],
        'White rice (cooked)' => [130, 2.7, 28.2, 0.3],
        'Pasta (dry)' => [371, 13, 74.7, 1.5],
        'Sweet potato (raw)' => [86, 1.6, 20.1, 0.1],
        'Potatoes (raw)' => [77, 2, 17.5, 0.1],
        'Red lentils (dry)' => [358, 24, 60, 1.5],
        'Chickpeas (tinned, drained)' => [139, 7, 22.5, 2.6],
        'Kidney beans (tinned, drained)' => [110, 7.6, 17.6, 0.6],
        'Banana' => [89, 1.1, 22.8, 0.3],
        'Broccoli' => [34, 2.8, 6.6, 0.4],
        'Peanut butter' => [588, 25, 20, 50],
        'Olive oil' => [884, 0, 0, 100],
        'Semi-skimmed milk' => [46, 3.5, 4.8, 1.7],
    ];

    public function search(string $query, int $limit = 6): array
    {
        $words = array_filter(explode(' ', Str::lower(Str::squish($query))));

        if ($words === []) {
            return [];
        }

        $items = [];

        foreach (self::FOODS as $name => [$kcal, $protein, $carbs, $fat]) {
            // every word has to appear in the name, so "chicken breast" doesn't match thighs
            if (collect($words)->every(fn ($word) => Str::contains(Str::lower($name), $word))) {
                $items[] = new FoodItem($name, null, $kcal, $protein, $carbs, $fat);
            }
        }

        return array_slice($items, 0, $limit);
    }

    public function sourceName(): string
    {
        return 'MacroMeals built-in list';
    }
}
