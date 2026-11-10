<?php

use App\Models\Comment;
use App\Models\Recipe;
use App\Models\Tag;
use App\Models\User;

// a valid form submission I can tweak in each test
function validRecipeData(array $overrides = []): array
{
    return array_merge([
        'title' => 'Chicken rice bowl',
        'description' => 'Quick high protein lunch.',
        'ingredients' => "200g chicken\r\n150g rice",
        'method' => "Cook the chicken.\r\nServe over the rice.",
        'prep_minutes' => 20,
        'servings' => 2,
        'protein' => 45,
        'carbs' => 60,
        'fat' => 12,
    ], $overrides);
}

test('guests are sent to the login page', function () {
    $this->get(route('recipes.index'))->assertRedirect(route('login'));
    $this->get(route('recipes.create'))->assertRedirect(route('login'));
});

test('the recipes page lists recipes with their macros', function () {
    $recipe = Recipe::factory()->create(['protein' => 40, 'carbs' => 50, 'fat' => 10]);

    $this->actingAs(User::factory()->create())
        ->get(route('recipes.index'))
        ->assertOk()
        ->assertSee($recipe->title)
        ->assertSee('450 kcal'); // 40*4 + 50*4 + 10*9
});

test('the recipes page is paginated', function () {
    Recipe::factory()->count(12)->create();

    $this->actingAs(User::factory()->create())
        ->get(route('recipes.index', ['page' => 2]))
        ->assertOk()
        ->assertViewHas('recipes', fn ($recipes) => $recipes->count() === 3);
});

test('a recipe page shows its comments', function () {
    $comment = Comment::factory()->create(['body' => 'Made this for meal prep.']);

    $this->actingAs(User::factory()->create())
        ->get(route('recipes.show', $comment->recipe))
        ->assertOk()
        ->assertSee($comment->recipe->title)
        ->assertSee('Made this for meal prep.')
        ->assertSee($comment->user->name);
});

test('a user can post a recipe with tags', function () {
    $user = User::factory()->create();
    $tag = Tag::create(['name' => 'Meal prep']);

    $response = $this->actingAs($user)
        ->post(route('recipes.store'), validRecipeData(['tags' => [$tag->id]]));

    $recipe = Recipe::first();
    $response->assertRedirect(route('recipes.show', $recipe))
        ->assertSessionHas('success');

    expect($recipe->user_id)->toBe($user->id)
        ->and($recipe->tags->pluck('name')->all())->toBe(['Meal prep']);
});

test('invalid recipes are rejected with errors', function () {
    $this->actingAs(User::factory()->create())
        ->from(route('recipes.create'))
        ->post(route('recipes.store'), validRecipeData(['title' => '', 'protein' => -5, 'tags' => [999]]))
        ->assertRedirect(route('recipes.create'))
        ->assertSessionHasErrors(['title', 'protein', 'tags.0']);

    expect(Recipe::count())->toBe(0);
});

test('users cannot set the owner or image through the form', function () {
    $user = User::factory()->create();
    $someoneElse = User::factory()->create();

    $this->actingAs($user)->post(route('recipes.store'), validRecipeData([
        'user_id' => $someoneElse->id,
        'image_path' => 'hacked.jpg',
    ]));

    $recipe = Recipe::first();
    expect($recipe->user_id)->toBe($user->id)
        ->and($recipe->image_path)->toBeNull();
});
