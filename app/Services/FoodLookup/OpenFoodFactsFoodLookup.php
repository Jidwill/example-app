<?php

namespace App\Services\FoodLookup;

use App\Contracts\FoodLookup;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Factory as Http;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Str;

/**
 * Looks foods up in Open Food Facts (https://world.openfoodfacts.org), a free open database.
 *
 * Everything this class needs is passed into the constructor (dependency injection) by
 * FoodLookupServiceProvider, rather than it creating things itself. That means tests can
 * give it a fake HTTP client, and the URL, timeout etc. come from config/services.php.
 */
class OpenFoodFactsFoodLookup implements FoodLookup
{
    public function __construct(
        private Http $http,
        private Cache $cache,
        private string $baseUrl,
        private string $userAgent,
        private int $timeoutSeconds = 8,
        private int $cacheMinutes = 1440,
    ) {}

    public function search(string $query, int $limit = 6): array
    {
        $query = Str::squish($query);

        if ($query === '') {
            return [];
        }

        // Open Food Facts only allows 10 searches a minute, so remember results for a day.
        // If the API call fails it throws before anything is cached, so errors aren't remembered
        $results = $this->cache->remember(
            'food-lookup:'.md5(Str::lower($query)),
            now()->addMinutes($this->cacheMinutes),
            fn () => $this->fetch($query),
        );

        return array_slice(array_map(fn (array $item) => new FoodItem(...$item), $results), 0, $limit);
    }

    public function sourceName(): string
    {
        return 'Open Food Facts';
    }

    /**
     * Calls the API and returns the usable products as arrays (so they can be cached).
     *
     * @return list<array<string, mixed>>
     */
    private function fetch(string $query): array
    {
        try {
            $response = $this->http
                ->withUserAgent($this->userAgent) // Open Food Facts asks apps to say who they are
                ->acceptJson()
                ->timeout($this->timeoutSeconds)
                // try again once if their server has a hiccup, but not for things like 429 (too many requests)
                ->retry([500], when: fn ($e) => $e instanceof RequestException && $e->response->serverError(), throw: false)
                ->get(rtrim($this->baseUrl, '/').'/cgi/search.pl', [
                    'search_terms' => $query,
                    'search_simple' => 1,
                    'action' => 'process',
                    'json' => 1,
                    'page_size' => 20,
                    'fields' => 'product_name,brands,nutriments', // only what we need, so it's quicker
                ]);
        } catch (ConnectionException $e) {
            throw new FoodLookupException("Couldn't reach Open Food Facts (it took too long or the connection failed). Try again in a moment.", previous: $e);
        }

        if ($response->status() === 429) {
            throw new FoodLookupException('Open Food Facts is getting too many searches right now. Wait a minute and try again.');
        }

        if ($response->failed() || ! is_array($response->json('products'))) {
            throw new FoodLookupException("Open Food Facts didn't send back a proper answer (error {$response->status()}). Try again in a moment.");
        }

        return collect($response->json('products'))
            ->map(fn ($product) => is_array($product) ? FoodItem::fromOpenFoodFacts($product) : null)
            ->filter()
            ->map(fn (FoodItem $item) => $item->toArray())
            ->values()
            ->all();
    }
}
