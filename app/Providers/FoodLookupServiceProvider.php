<?php

namespace App\Providers;

use App\Contracts\FoodLookup;
use App\Services\FoodLookup\LocalFoodLookup;
use App\Services\FoodLookup\OpenFoodFactsFoodLookup;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Client\Factory as Http;
use Illuminate\Support\ServiceProvider;

/**
 * Tells Laravel's service container what to give back when something asks for a FoodLookup.
 *
 * Anything that type-hints FoodLookup (like the food-lookup component's search method)
 * gets whichever implementation is chosen here, built with everything it needs.
 * Changing FOOD_LOOKUP_DRIVER in .env swaps it without touching any other code.
 */
class FoodLookupServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // singleton = build it once per request and reuse it
        $this->app->singleton(FoodLookup::class, function (Application $app) {
            $config = $app['config']['services.food_lookup'];

            return match ($config['driver']) {
                'local' => new LocalFoodLookup,
                default => new OpenFoodFactsFoodLookup(
                    http: $app->make(Http::class),
                    cache: $app->make(Cache::class),
                    baseUrl: $config['open_food_facts_url'],
                    userAgent: $config['user_agent'],
                    timeoutSeconds: (int) $config['timeout'],
                    cacheMinutes: (int) $config['cache_minutes'],
                ),
            };
        });
    }
}
