<?php

namespace App\Services\FoodLookup;

/**
 * One food with its macros per 100g. Every FoodLookup returns these,
 * so the page doesn't need to know which API (or list) the data came from.
 */
final readonly class FoodItem
{
    public function __construct(
        public string $name,
        public ?string $brand,
        public float $kcal,
        public float $protein,
        public float $carbs,
        public float $fat,
    ) {}

    /**
     * Builds a FoodItem from one product in an Open Food Facts search result.
     * Returns null if the product is missing its name or macros, so those get skipped.
     */
    public static function fromOpenFoodFacts(array $product): ?self
    {
        $name = trim((string) ($product['product_name'] ?? ''));
        $n = $product['nutriments'] ?? [];

        if ($name === '' || ! isset($n['proteins_100g'], $n['carbohydrates_100g'], $n['fat_100g'])) {
            return null;
        }

        // some products only have energy in kJ, so convert (1 kcal = 4.184 kJ)
        $kcal = $n['energy-kcal_100g'] ?? (isset($n['energy-kj_100g']) ? $n['energy-kj_100g'] / 4.184 : null);

        // if there's no energy at all, work it out from the macros like the Recipe model does
        $kcal ??= ($n['proteins_100g'] * 4) + ($n['carbohydrates_100g'] * 4) + ($n['fat_100g'] * 9);

        // brands can be "Tesco, Tesco Finest" - just show the first one
        $brand = trim(explode(',', (string) ($product['brands'] ?? ''))[0]) ?: null;

        return new self(
            name: $name,
            brand: $brand,
            kcal: round((float) $kcal, 1),
            protein: round((float) $n['proteins_100g'], 1),
            carbs: round((float) $n['carbohydrates_100g'], 1),
            fat: round((float) $n['fat_100g'], 1),
        );
    }

    /**
     * Livewire can only keep simple values between requests, so the page stores arrays.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
