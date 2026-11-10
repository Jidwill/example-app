<?php

use App\Models\Comment;
use App\Models\Recipe;
use App\Models\User;

test('members can only edit and delete their own recipes', function () {
    $member = User::factory()->create();
    $own = Recipe::factory()->for($member)->create();
    $someoneElses = Recipe::factory()->create();

    expect($member->can('update', $own))->toBeTrue()
        ->and($member->can('delete', $own))->toBeTrue()
        ->and($member->can('update', $someoneElses))->toBeFalse()
        ->and($member->can('delete', $someoneElses))->toBeFalse()
        ->and($member->can('hide', $someoneElses))->toBeFalse();
});

test('coaches can hide recipes but not edit other peoples', function () {
    $coach = User::factory()->coach()->create();
    $recipe = Recipe::factory()->create();

    expect($coach->can('hide', $recipe))->toBeTrue()
        ->and($coach->can('update', $recipe))->toBeFalse()
        ->and($coach->can('delete', $recipe))->toBeFalse();
});

test('coaches can edit and delete anyone\'s comments but members cannot', function () {
    $comment = Comment::factory()->create();
    $member = User::factory()->create();
    $coach = User::factory()->coach()->create();

    expect($member->can('update', $comment))->toBeFalse()
        ->and($member->can('delete', $comment))->toBeFalse()
        ->and($comment->user->can('update', $comment))->toBeTrue()
        ->and($coach->can('update', $comment))->toBeTrue()
        ->and($coach->can('delete', $comment))->toBeTrue();
});

test('admins can do everything, including managing tags and roles', function () {
    $admin = User::factory()->admin()->create();
    $coach = User::factory()->coach()->create();
    $recipe = Recipe::factory()->create();

    expect($admin->can('update', $recipe))->toBeTrue()
        ->and($admin->can('delete', $recipe))->toBeTrue()
        ->and($admin->can('hide', $recipe))->toBeTrue()
        ->and($admin->can('manage-tags'))->toBeTrue()
        ->and($admin->can('assign-roles'))->toBeTrue()
        ->and($coach->can('manage-tags'))->toBeFalse()
        ->and($coach->can('assign-roles'))->toBeFalse();
});

test('a coach can hide a recipe and members stop seeing it', function () {
    $coach = User::factory()->coach()->create();
    $member = User::factory()->create();
    $recipe = Recipe::factory()->create(['title' => 'Dodgy macros']);

    $this->actingAs($coach)
        ->patch(route('recipes.hide', $recipe))
        ->assertRedirect(route('recipes.show', $recipe));

    expect($recipe->fresh()->is_hidden)->toBeTrue();

    $this->actingAs($member)->get(route('recipes.show', $recipe))->assertForbidden();
    $this->actingAs($member)->get(route('recipes.index'))->assertDontSee('Dodgy macros');

    // the owner and coaches can still see it
    $this->actingAs($recipe->user)->get(route('recipes.show', $recipe))->assertOk();
    $this->actingAs($coach)->get(route('recipes.index'))->assertSee('Dodgy macros');
});

test('members cannot hide recipes', function () {
    $recipe = Recipe::factory()->create();

    $this->actingAs(User::factory()->create())
        ->patch(route('recipes.hide', $recipe))
        ->assertForbidden();

    expect($recipe->fresh()->is_hidden)->toBeFalse();
});

test('only coaches and admins see the hide button', function () {
    $recipe = Recipe::factory()->create();

    $this->actingAs(User::factory()->create())
        ->get(route('recipes.show', $recipe))
        ->assertDontSee('Hide recipe');

    $this->actingAs(User::factory()->coach()->create())
        ->get(route('recipes.show', $recipe))
        ->assertSee('Hide recipe');
});

test('is_hidden cannot be set through the recipe form', function () {
    $this->actingAs(User::factory()->create())->post(route('recipes.store'), [
        'title' => 'Sneaky',
        'description' => 'Trying to hide my own recipe.',
        'ingredients' => 'Eggs',
        'method' => 'Cook them.',
        'prep_minutes' => 5,
        'servings' => 1,
        'protein' => 20,
        'carbs' => 1,
        'fat' => 10,
        'is_hidden' => true,
    ]);

    expect(Recipe::first()->is_hidden)->toBeFalse();
});
