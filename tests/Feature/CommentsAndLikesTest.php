<?php

use App\Models\Comment;
use App\Models\Like;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Livewire\Volt\Volt;

test('the recipe page loads the comments and like button components', function () {
    $recipe = Recipe::factory()->create();

    $this->actingAs(User::factory()->create())
        ->get(route('recipes.show', $recipe))
        ->assertOk()
        ->assertSeeVolt('recipe-comments')
        ->assertSeeVolt('like-button');
});

test('a member can post a comment without reloading the page', function () {
    $user = User::factory()->create();
    $recipe = Recipe::factory()->create();

    Volt::actingAs($user)
        ->test('recipe-comments', ['recipe' => $recipe])
        ->set('body', 'Made this for meal prep, really good.')
        ->call('addComment')
        ->assertHasNoErrors()
        ->assertSet('body', '')
        ->assertSee('Made this for meal prep, really good.')
        ->assertSee('Comments (1)');

    $comment = Comment::first();
    expect($comment->user_id)->toBe($user->id)
        ->and($comment->recipe_id)->toBe($recipe->id);
});

test('empty and very long comments are rejected', function () {
    $recipe = Recipe::factory()->create();

    Volt::actingAs(User::factory()->create())
        ->test('recipe-comments', ['recipe' => $recipe])
        ->set('body', '')
        ->call('addComment')
        ->assertHasErrors(['body' => 'required'])
        ->assertSee('The comment field is required.')
        ->set('body', str_repeat('a', 1001))
        ->call('addComment')
        ->assertHasErrors(['body' => 'max']);

    expect(Comment::count())->toBe(0);
});

test('members can edit their own comments but not other peoples', function () {
    $recipe = Recipe::factory()->create();
    $mine = Comment::factory()->for($recipe)->create();
    $someoneElses = Comment::factory()->for($recipe)->create(['body' => 'Original text']);

    Volt::actingAs($mine->user)
        ->test('recipe-comments', ['recipe' => $recipe])
        ->call('startEditing', $mine->id)
        ->set('editBody', 'Updated: added extra chilli')
        ->call('saveEdit')
        ->assertHasNoErrors()
        ->assertSee('Updated: added extra chilli');

    Volt::actingAs($mine->user)
        ->test('recipe-comments', ['recipe' => $recipe])
        ->call('startEditing', $someoneElses->id)
        ->assertForbidden();

    Volt::actingAs($mine->user)
        ->test('recipe-comments', ['recipe' => $recipe])
        ->call('deleteComment', $someoneElses->id)
        ->assertForbidden();

    expect($someoneElses->fresh()->body)->toBe('Original text');
});

test('members only see edit and delete on their own comments', function () {
    $recipe = Recipe::factory()->create();
    $mine = Comment::factory()->for($recipe)->create();
    Comment::factory()->for($recipe)->create();

    Volt::actingAs($mine->user)
        ->test('recipe-comments', ['recipe' => $recipe])
        ->assertSeeHtml('wire:click="startEditing('.$mine->id.')"')
        ->assertDontSeeHtml('wire:click="startEditing('.($mine->id + 1).')"');
});

test('a coach can edit and delete anyone\'s comment', function () {
    $coach = User::factory()->coach()->create();
    $recipe = Recipe::factory()->create();
    $comment = Comment::factory()->for($recipe)->create();
    Like::factory()->for($comment, 'likeable')->create();

    Volt::actingAs($coach)
        ->test('recipe-comments', ['recipe' => $recipe])
        ->call('startEditing', $comment->id)
        ->set('editBody', 'Edited by a coach: please check the macros')
        ->call('saveEdit')
        ->assertHasNoErrors()
        ->assertSee('edited')
        ->call('deleteComment', $comment->id)
        ->assertSee('No comments yet');

    expect(Comment::count())->toBe(0)
        ->and(Like::count())->toBe(0); // its likes went with it
});

test('you cannot edit a comment from a different recipe through this one', function () {
    $coach = User::factory()->coach()->create();
    $recipe = Recipe::factory()->create();
    $otherComment = Comment::factory()->create();

    // in the browser this shows a 404 page, in tests the exception comes straight through
    expect(fn () => Volt::actingAs($coach)
        ->test('recipe-comments', ['recipe' => $recipe])
        ->call('startEditing', $otherComment->id)
    )->toThrow(ModelNotFoundException::class);
});

test('members cannot comment on a hidden recipe', function () {
    $recipe = Recipe::factory()->create(['is_hidden' => true]);

    Volt::actingAs(User::factory()->create())
        ->test('recipe-comments', ['recipe' => $recipe])
        ->set('body', 'Sneaky comment')
        ->call('addComment')
        ->assertForbidden();

    expect(Comment::count())->toBe(0);
});

test('liking a recipe and then unliking it', function () {
    $user = User::factory()->create();
    $recipe = Recipe::factory()->create();

    $button = Volt::actingAs($user)
        ->test('like-button', ['likeable' => $recipe])
        ->assertSet('count', 0)
        ->call('toggle')
        ->assertSet('liked', true)
        ->assertSet('count', 1)
        ->assertSeeHtml('aria-pressed="true"');

    expect($recipe->likes()->where('user_id', $user->id)->exists())->toBeTrue();

    $button->call('toggle')
        ->assertSet('liked', false)
        ->assertSet('count', 0);

    expect(Like::count())->toBe(0);
});

test('the same like button works on comments', function () {
    $user = User::factory()->create();
    $comment = Comment::factory()->create();

    Volt::actingAs($user)
        ->test('like-button', ['likeable' => $comment])
        ->call('toggle')
        ->assertSet('count', 1);

    $like = Like::first();
    expect($like->likeable_type)->toBe(Comment::class)
        ->and($like->likeable->is($comment))->toBeTrue();
});

test('you cannot like your own recipe or comment', function () {
    $recipe = Recipe::factory()->create();
    $comment = Comment::factory()->for($recipe->user)->create();

    Volt::actingAs($recipe->user)
        ->test('like-button', ['likeable' => $recipe])
        ->assertDontSeeHtml('wire:click="toggle"')
        ->call('toggle')
        ->assertSet('count', 0);

    Volt::actingAs($recipe->user)
        ->test('like-button', ['likeable' => $comment])
        ->call('toggle')
        ->assertSet('count', 0);

    expect(Like::count())->toBe(0);
});
