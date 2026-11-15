<?php

namespace App\Contracts;

use App\Services\FoodLookup\FoodItem;
use App\Services\FoodLookup\FoodLookupException;

/**
 * Anything that can look up a food and give back its macros per 100g.
 *
 * The rest of the app only asks the container for a FoodLookup, never a specific
 * class. That way I can swap between the real Open Food Facts API and the
 * built-in list (for offline use and tests) just by changing the binding in
 * FoodLookupServiceProvider.
 */
interface FoodLookup
{
    /**
     * Search for foods by name.
     *
     * @return list<FoodItem>
     *
     * @throws FoodLookupException when the lookup couldn't be done (e.g. the API is down)
     */
    public function search(string $query, int $limit = 6): array;

    /**
     * Name of where the results come from (shown under the results).
     */
    public function sourceName(): string;
}
