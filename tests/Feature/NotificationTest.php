<?php

use App\Models\Comment;
use App\Models\Like;
use App\Models\Recipe;
use App\Models\User;
use App\Notifications\NewComment;
use App\Notifications\NewLike;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Notifications\DatabaseNotification;
use Livewire\Volt\Volt;

test('commenting on someone elses recipe notifies the owner', function () {
    $recipe = Recipe::factory()->create(['title' => 'Beef chilli']);
    $commenter = User::factory()->create(['name' => 'Sam']);

    Volt::actingAs($commenter)
        ->test('recipe-comments', ['recipe' => $recipe])
        ->set('body', 'Made this, added extra beans.')
        ->call('addComment');

    $notification = $recipe->user->notifications()->first();

    expect($notification->type)->toBe(NewComment::class)
        ->and($notification->data['kind'])->toBe('comment')
        ->and($notification->data['actor_name'])->toBe('Sam')
        ->and($notification->data['recipe_title'])->toBe('Beef chilli')
        ->and($notification->data['excerpt'])->toBe('Made this, added extra beans.');
});

test('commenting on your own recipe does not notify you', function () {
    $recipe = Recipe::factory()->create();

    Comment::factory()->for($recipe)->for($recipe->user)->create();

    expect(DatabaseNotification::count())->toBe(0);
});

test('liking a recipe notifies its owner', function () {
    $recipe = Recipe::factory()->create();

    Volt::actingAs(User::factory()->create())
        ->test('like-button', ['likeable' => $recipe])
        ->call('toggle');

    $notification = $recipe->user->notifications()->first();

    expect($notification->type)->toBe(NewLike::class)
        ->and($notification->data['kind'])->toBe('recipe_like')
        ->and($notification->data['recipe_id'])->toBe($recipe->id);
});

test('liking a comment notifies the person who wrote it', function () {
    $comment = Comment::factory()->create(['body' => 'Swapped rice for cauliflower rice.']);
    DatabaseNotification::query()->delete(); // ignore the notification the comment itself made

    Volt::actingAs(User::factory()->create())
        ->test('like-button', ['likeable' => $comment])
        ->call('toggle');

    $notification = $comment->user->notifications()->first();

    expect($notification->data['kind'])->toBe('comment_like')
        ->and($notification->data['comment_id'])->toBe($comment->id)
        ->and($notification->data['recipe_id'])->toBe($comment->recipe_id)
        ->and($notification->data['excerpt'])->toBe('Swapped rice for cauliflower rice.');
});

test('unliking takes the notification back', function () {
    $recipe = Recipe::factory()->create();

    $button = Volt::actingAs(User::factory()->create())
        ->test('like-button', ['likeable' => $recipe])
        ->call('toggle');

    expect($recipe->user->notifications()->count())->toBe(1);

    $button->call('toggle');

    expect($recipe->user->notifications()->count())->toBe(0);
});

test('deleting a recipe or comment removes the notifications about it', function () {
    $recipe = Recipe::factory()->create();
    $comment = Comment::factory()->for($recipe)->create(); // notifies the recipe owner
    Like::factory()->for($comment, 'likeable')->create(); // notifies the comment author
    $otherRecipe = Recipe::factory()->create();
    Like::factory()->for($otherRecipe, 'likeable')->create(); // should be left alone

    $comment->delete();
    expect(DatabaseNotification::whereJsonContains('data->comment_id', $comment->id)->count())->toBe(0);

    $recipe->delete();
    expect(DatabaseNotification::whereJsonContains('data->recipe_id', $recipe->id)->count())->toBe(0)
        ->and(DatabaseNotification::whereJsonContains('data->recipe_id', $otherRecipe->id)->count())->toBe(1);
});

test('the bell shows how many unread notifications you have', function () {
    $recipe = Recipe::factory()->create();
    Like::factory()->count(3)->for($recipe, 'likeable')->create();

    Volt::actingAs($recipe->user)
        ->test('notification-bell')
        ->assertSet('unread', 3)
        ->assertSeeHtml('aria-label="Notifications, 3 unread"');

    $this->actingAs($recipe->user)->get(route('recipes.index'))
        ->assertSeeVolt('notification-bell');
});

test('the notifications page lists them and they can be marked as read', function () {
    $recipe = Recipe::factory()->create(['title' => 'Tuna pasta bake']);
    $liker = User::factory()->create(['name' => 'Alex']);
    Like::factory()->for($liker)->for($recipe, 'likeable')->create();
    Like::factory()->for($recipe, 'likeable')->create();
    $owner = $recipe->user;

    $this->actingAs($owner)->get(route('notifications.index'))
        ->assertOk()
        ->assertSee('Alex')
        ->assertSee('liked your recipe')
        ->assertSee('Tuna pasta bake');

    $first = $owner->notifications()->first();

    Volt::actingAs($owner)
        ->test('notifications-list')
        ->call('markAsRead', $first->id)
        ->assertDispatched('notifications-updated');

    expect($owner->unreadNotifications()->count())->toBe(1);

    Volt::actingAs($owner)
        ->test('notifications-list')
        ->call('markAllAsRead');

    expect($owner->unreadNotifications()->count())->toBe(0);
});

test('viewing a notification marks it read and goes to the comment', function () {
    $comment = Comment::factory()->create();
    $owner = $comment->recipe->user;
    $notification = $owner->notifications()->first();

    Volt::actingAs($owner)
        ->test('notifications-list')
        ->call('open', $notification->id)
        ->assertRedirect(route('recipes.show', $comment->recipe_id).'#comment-'.$comment->id);

    expect($notification->fresh()->read_at)->not->toBeNull();
});

test('you cannot mark someone elses notification as read', function () {
    $comment = Comment::factory()->create();
    $notification = $comment->recipe->user->notifications()->first();

    expect(fn () => Volt::actingAs(User::factory()->create())
        ->test('notifications-list')
        ->call('markAsRead', $notification->id)
    )->toThrow(ModelNotFoundException::class);

    expect($notification->fresh()->read_at)->toBeNull();
});

test('guests cannot see notifications', function () {
    $this->get(route('notifications.index'))->assertRedirect(route('login'));
});
