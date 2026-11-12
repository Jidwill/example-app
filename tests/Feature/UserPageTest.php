<?php

use App\Models\Comment;
use App\Models\Profile;
use App\Models\Recipe;
use App\Models\User;
use Livewire\Volt\Volt;

test('a user page shows their profile, recipes and comments', function () {
    $user = User::factory()->has(Profile::factory()->state(['goal' => 'bulk', 'daily_protein' => 180]), 'profile')->create();
    $recipe = Recipe::factory()->for($user)->create(['title' => 'My chicken bowl']);
    $otherRecipe = Recipe::factory()->create(['title' => 'Someone elses chilli']);
    Comment::factory()->for($user)->for($otherRecipe)->create(['body' => 'Doubled the beans, great.']);

    $this->actingAs(User::factory()->create())
        ->get(route('users.show', $user))
        ->assertOk()
        ->assertSee($user->name)
        ->assertSee('Bulk')
        ->assertSee('180g')
        ->assertSee('My chicken bowl')
        ->assertSee('Doubled the beans, great.')
        ->assertSee('Someone elses chilli') // the recipe the comment is on
        ->assertSee(route('recipes.show', $otherRecipe));
});

test('guests cannot see user pages', function () {
    $this->get(route('users.show', User::factory()->create()))->assertRedirect(route('login'));
});

test('a user without a profile still has a page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('users.show', $user))
        ->assertOk()
        ->assertSee('set a goal yet');
});

test('names link to the user page on cards, recipe pages and comments', function () {
    $recipe = Recipe::factory()->create();
    $comment = Comment::factory()->for($recipe)->create();
    $viewer = User::factory()->create();

    $this->actingAs($viewer)->get(route('recipes.index'))
        ->assertSee(route('users.show', $recipe->user));

    $this->actingAs($viewer)->get(route('recipes.show', $recipe))
        ->assertSee(route('users.show', $recipe->user))
        ->assertSee(route('users.show', $comment->user));
});

test('hidden recipes and comments on them only show to people allowed to see them', function () {
    $author = User::factory()->create();
    $hidden = Recipe::factory()->for($author)->create(['title' => 'Hidden bowl', 'is_hidden' => true]);
    Comment::factory()->for($author)->for($hidden)->create(['body' => 'Comment on a hidden recipe']);

    $this->actingAs(User::factory()->create())->get(route('users.show', $author))
        ->assertDontSee('Hidden bowl')
        ->assertDontSee('Comment on a hidden recipe');

    foreach ([$author, User::factory()->coach()->create()] as $allowed) {
        $this->actingAs($allowed)->get(route('users.show', $author))
            ->assertSee('Hidden bowl')
            ->assertSee('Comment on a hidden recipe');
    }
});

test('the recipes and comments lists have separate pages', function () {
    $user = User::factory()->create();
    Recipe::factory()->count(8)->for($user)->create();
    Comment::factory()->count(12)->for($user)->create();

    $this->actingAs($user)
        ->get(route('users.show', ['user' => $user, 'recipes_page' => 2, 'comments_page' => 2]))
        ->assertOk()
        ->assertViewHas('recipes', fn ($recipes) => $recipes->count() === 2)
        ->assertViewHas('comments', fn ($comments) => $comments->count() === 2);
});

test('registering creates a starter profile', function () {
    Volt::test('pages.auth.register')
        ->set('name', 'New Lifter')
        ->set('email', 'new@example.com')
        ->set('password', 'password')
        ->set('password_confirmation', 'password')
        ->call('register');

    $profile = User::where('email', 'new@example.com')->first()->profile;

    expect($profile)->not->toBeNull()
        ->and($profile->goal)->toBe('maintain');
});

test('users can update their goal and targets', function () {
    $user = User::factory()->create(); // no profile yet, like an older account

    Volt::actingAs($user)
        ->test('profile.update-fitness-profile-form')
        ->set('goal', 'cut')
        ->set('daily_calories', 2100)
        ->set('daily_protein', 170)
        ->set('bio', 'Cutting for summer.')
        ->call('updateFitnessProfile')
        ->assertHasNoErrors()
        ->assertDispatched('fitness-profile-updated');

    expect($user->fresh()->profile->only('goal', 'daily_calories', 'daily_protein', 'bio'))->toBe([
        'goal' => 'cut',
        'daily_calories' => 2100,
        'daily_protein' => 170,
        'bio' => 'Cutting for summer.',
    ]);
});

test('goal and targets are validated', function () {
    Volt::actingAs(User::factory()->create())
        ->test('profile.update-fitness-profile-form')
        ->set('goal', 'shred')
        ->set('daily_calories', 200)
        ->set('daily_protein', '')
        ->call('updateFitnessProfile')
        ->assertHasErrors(['goal', 'daily_calories', 'daily_protein'])
        ->assertSee('The daily calories field must be at least 1000.');
});

test('the profile page shows the training goal form', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('profile'))
        ->assertOk()
        ->assertSeeVolt('profile.update-fitness-profile-form');
});
