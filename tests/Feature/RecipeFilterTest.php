<?php

use App\Models\Profile;
use App\Models\Recipe;
use App\Models\Tag;
use App\Models\User;

// recipe with exact macros so the calories are easy to check (4p + 4c + 9f)
function recipeWith(string $title, int $protein, int $carbs, int $fat, array $extra = []): Recipe
{
    return Recipe::factory()->create(['title' => $title, 'protein' => $protein, 'carbs' => $carbs, 'fat' => $fat, ...$extra]);
}

function titlesOn($response): array
{
    return $response->viewData('recipes')->pluck('title')->all();
}

test('recipes can be filtered by tag', function () {
    $tag = Tag::create(['name' => 'Meal prep']);
    recipeWith('Tagged', 30, 30, 10)->tags()->attach($tag);
    recipeWith('Not tagged', 30, 30, 10);

    $response = $this->actingAs(User::factory()->create())->get(route('recipes.index', ['tag' => $tag->id]));

    expect(titlesOn($response))->toBe(['Tagged']);
});

test('recipes can be filtered by minimum protein and maximum calories', function () {
    recipeWith('High protein light', 40, 20, 5, ['created_at' => now()->subDays(3)]); // 285 kcal
    recipeWith('High protein heavy', 45, 80, 30, ['created_at' => now()->subDays(2)]); // 770 kcal
    recipeWith('Low protein light', 10, 30, 5, ['created_at' => now()->subDay()]); // 205 kcal
    $user = User::factory()->create();

    expect(titlesOn($this->actingAs($user)->get(route('recipes.index', ['min_protein' => 35]))))
        ->toBe(['High protein heavy', 'High protein light'])
        ->and(titlesOn($this->actingAs($user)->get(route('recipes.index', ['max_calories' => 300]))))
        ->toBe(['Low protein light', 'High protein light'])
        ->and(titlesOn($this->actingAs($user)->get(route('recipes.index', ['min_protein' => 35, 'max_calories' => 300]))))
        ->toBe(['High protein light']);
});

test('recipes can be sorted by protein or by protein per calorie', function () {
    recipeWith('Big bulk bowl', 50, 120, 30);  // 950 kcal, 5.3g per 100 kcal
    recipeWith('Egg whites', 30, 2, 1);        // 137 kcal, 21.9g per 100 kcal
    recipeWith('Pasta', 15, 90, 8);            // 492 kcal, 3.0g per 100 kcal
    $user = User::factory()->create();

    expect(titlesOn($this->actingAs($user)->get(route('recipes.index', ['sort' => 'protein']))))
        ->toBe(['Big bulk bowl', 'Egg whites', 'Pasta'])
        ->and(titlesOn($this->actingAs($user)->get(route('recipes.index', ['sort' => 'lean']))))
        ->toBe(['Egg whites', 'Big bulk bowl', 'Pasta']);
});

test('filters still leave out hidden recipes', function () {
    recipeWith('Visible', 40, 20, 5);
    recipeWith('Hidden', 40, 20, 5, ['is_hidden' => true]);

    $response = $this->actingAs(User::factory()->create())->get(route('recipes.index', ['min_protein' => 30]));

    expect(titlesOn($response))->toBe(['Visible']);
});

test('the next page keeps the filters', function () {
    Recipe::factory()->count(12)->create(['protein' => 40]);

    $this->actingAs(User::factory()->create())
        ->get(route('recipes.index', ['min_protein' => 35]))
        ->assertSee('min_protein=35', false)
        ->assertSee('12 recipes match your filters');
});

test('invalid filters go back to the full list with an error', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('recipes.index', ['min_protein' => 'lots', 'sort' => 'random']))
        ->assertRedirect(route('recipes.index'))
        ->assertSessionHasErrors(['min_protein', 'sort']);
});

test('tags on cards link to the filtered list', function () {
    $tag = Tag::create(['name' => 'Quick']);
    recipeWith('Fast eggs', 20, 2, 10)->tags()->attach($tag);

    $this->actingAs(User::factory()->create())
        ->get(route('recipes.index'))
        ->assertSee(route('recipes.index', ['tag' => $tag->id]), false)
        ->assertSee('Show all Quick recipes');
});

test('the recipe page shows how a serving fits your targets', function () {
    $user = User::factory()->has(Profile::factory()->state(['daily_protein' => 160, 'daily_calories' => 2000]), 'profile')->create();
    $recipe = recipeWith('Chicken bowl', 40, 50, 10); // 450 kcal

    $this->actingAs($user)
        ->get(route('recipes.show', $recipe))
        ->assertSee('How one serving fits your day')
        ->assertSee('40g is 25% of your daily 160g')
        ->assertSee('450 kcal is 23% of your daily 2,000 kcal');

    $this->actingAs($user)
        ->get(route('recipes.index'))
        ->assertSee('25% of your daily protein');
});

test('people without targets are told where to set them', function () {
    $recipe = recipeWith('Chicken bowl', 40, 50, 10);

    $this->actingAs(User::factory()->create())
        ->get(route('recipes.show', $recipe))
        ->assertSee('Set your daily targets')
        ->assertDontSee('of your daily protein');
});
