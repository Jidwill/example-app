<?php

namespace App\Contracts;

use App\Services\FoodLookup\FoodItem;
use App\Services\FoodLookup\FoodLookupException;

/**
 * Anything that can look up a food and give back its macros per 100g.
 *
 * The rest of the app only ever asks the service container for a FoodLookup,
 * never for a specific class, so the implementation can be swapped (the real
 * Open Food Facts API, or the built-in list for offline use and tests) by changing
 * one binding in FoodLookupServiceProvider.
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
     * Where the results come from, shown under the search results.
     */
    public function sourceName(): string;
}
