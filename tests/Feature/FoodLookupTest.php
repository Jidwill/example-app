<?php

use App\Contracts\FoodLookup;
use App\Models\User;
use App\Services\FoodLookup\FoodItem;
use App\Services\FoodLookup\FoodLookupException;
use App\Services\FoodLookup\LocalFoodLookup;
use App\Services\FoodLookup\OpenFoodFactsFoodLookup;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Livewire\Volt\Volt;

// a cut-down version of what Open Food Facts sends back
function openFoodFactsResponse(): array
{
    return ['count' => 3, 'products' => [
        ['product_name' => 'Chicken Breast Fillets', 'brands' => 'Tesco, Tesco Finest', 'nutriments' => [
            'energy-kcal_100g' => 106, 'proteins_100g' => 24, 'carbohydrates_100g' => 0, 'fat_100g' => 1.1,
        ]],
        ['product_name' => 'Chicken nuggets', 'nutriments' => ['energy-kcal_100g' => 250]], // no macros, should be skipped
        ['product_name' => 'Chicken soup', 'brands' => '', 'nutriments' => [
            'energy-kj_100g' => 209.2, 'proteins_100g' => 2, 'carbohydrates_100g' => 5, 'fat_100g' => 1,
        ]],
    ]];
}

test('the container gives back the Open Food Facts lookup by default', function () {
    expect(app(FoodLookup::class))->toBeInstanceOf(OpenFoodFactsFoodLookup::class);
});

test('changing the driver config swaps in the built-in list', function () {
    config(['services.food_lookup.driver' => 'local']);
    app()->forgetInstance(FoodLookup::class);

    expect(app(FoodLookup::class))->toBeInstanceOf(LocalFoodLookup::class);
});

test('it searches Open Food Facts and turns the products into FoodItems', function () {
    Http::fake(['world.openfoodfacts.org/*' => Http::response(openFoodFactsResponse())]);

    $results = app(FoodLookup::class)->search('chicken');

    expect($results)->toHaveCount(2)
        ->and($results[0])->toBeInstanceOf(FoodItem::class)
        ->and($results[0]->name)->toBe('Chicken Breast Fillets')
        ->and($results[0]->brand)->toBe('Tesco') // only the first brand
        ->and($results[0]->protein)->toBe(24.0)
        ->and($results[1]->kcal)->toBe(50.0); // worked out from kJ (209.2 / 4.184)

    Http::assertSent(fn (Request $request) => str_contains($request->url(), '/cgi/search.pl')
        && $request['search_terms'] === 'chicken'
        && $request['json'] == 1
        && str_contains($request->header('User-Agent')[0], 'MacroMeals'));
});

test('searching the same thing twice only calls the API once', function () {
    Http::fake(['world.openfoodfacts.org/*' => Http::response(openFoodFactsResponse())]);

    app(FoodLookup::class)->search('chicken');
    app(FoodLookup::class)->search('  Chicken ');

    Http::assertSentCount(1);
});

test('a server error becomes a friendly FoodLookupException and is not cached', function () {
    Http::fakeSequence('world.openfoodfacts.org/*')
        ->push('', 503)
        ->push('', 503) // the one retry
        ->push(openFoodFactsResponse());

    expect(fn () => app(FoodLookup::class)->search('oats'))
        ->toThrow(FoodLookupException::class, 'Try again');

    // next time it works, because the failure wasn't remembered
    expect(app(FoodLookup::class)->search('oats'))->toHaveCount(2);
});

test('a timeout becomes a FoodLookupException', function () {
    Http::fake(['world.openfoodfacts.org/*' => fn () => throw new ConnectionException('timed out')]);

    expect(fn () => app(FoodLookup::class)->search('rice'))->toThrow(FoodLookupException::class, "Couldn't reach");
});

test('being rate limited becomes a FoodLookupException without retrying', function () {
    Http::fake(['world.openfoodfacts.org/*' => Http::response('', 429)]);

    expect(fn () => app(FoodLookup::class)->search('pasta'))->toThrow(FoodLookupException::class, 'too many searches');
    Http::assertSentCount(1);
});

test('the built-in list matches every word', function () {
    $results = (new LocalFoodLookup)->search('chicken breast');

    expect($results)->toHaveCount(1)
        ->and($results[0]->name)->toBe('Chicken breast (raw)');
});

test('the recipe form shows the food lookup', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('recipes.create'))
        ->assertOk()
        ->assertSeeVolt('food-lookup');
});

test('the lookup component uses whatever FoodLookup the container gives it', function () {
    // swap the binding for the built-in list - the component doesn't need to change at all
    app()->instance(FoodLookup::class, new LocalFoodLookup);

    Volt::actingAs(User::factory()->create())
        ->test('food-lookup')
        ->set('query', 'oats')
        ->call('search')
        ->assertSee('Rolled oats')
        ->assertSee('Results from MacroMeals built-in list');
});

test('adding ingredients adds up the macros and fills in the recipe', function () {
    app()->instance(FoodLookup::class, new LocalFoodLookup);

    Volt::actingAs(User::factory()->create())
        ->test('food-lookup')
        ->set('query', 'chicken breast')->call('search')
        ->set('grams', '200')->call('add', 0)        // 45g protein, 0 carbs, 5.2g fat
        ->set('query', 'white rice')->call('search')
        ->set('grams', '150')->call('add', 0)        // 4.1g protein, 42.3g carbs, 0.5g fat
        ->assertSee('Total: 435 kcal · 49g protein · 42g carbs · 6g fat')
        ->call('fillRecipe')
        ->assertDispatched('fill-macros', fn ($name, $params) => $params['protein'] == 49
            && $params['carbs'] == 42
            && $params['ingredients'] === ['200g Chicken breast (raw)', '150g White rice (cooked)']);
});

test('if the API is down the page shows a message instead of breaking', function () {
    Http::fake(['world.openfoodfacts.org/*' => Http::response('', 500)]);

    Volt::actingAs(User::factory()->create())
        ->test('food-lookup')
        ->set('query', 'chicken')
        ->call('search')
        ->assertOk()
        ->assertSee('Try again in a moment')
        ->assertSee('You can still type the macros in yourself');
});

test('the search and amount are validated', function () {
    Volt::actingAs(User::factory()->create())
        ->test('food-lookup')
        ->set('query', 'a')->call('search')
        ->assertHasErrors('query')
        ->set('grams', '0')->call('add', 0)
        ->assertHasErrors('grams');
});
