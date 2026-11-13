<?php

use App\Models\Profile;
use App\Models\Recipe;
use App\Models\Tag;
use App\Models\User;
use Livewire\Volt\Volt;

test('guests see the MacroMeals landing page', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('Gym-friendly recipes')
        ->assertSee(route('login'))
        ->assertSee(route('register'));
});

test('logged in users go from the landing page to their dashboard', function () {
    $this->actingAs(User::factory()->create())->get('/')->assertRedirect(route('dashboard'));
});

test('the dashboard shows your summary and the latest recipes', function () {
    $user = User::factory()->has(Profile::factory()->state(['daily_protein' => 180, 'goal' => 'bulk']), 'profile')->create();
    Recipe::factory()->for($user)->create();
    Recipe::factory()->create(['title' => 'Newest bowl']);

    $this->actingAs($user)->get(route('dashboard'))
        ->assertOk()
        ->assertSee("Welcome back, {$user->name}") // escaped like the page, in case the name has an apostrophe
        ->assertSee('180g')
        ->assertSee('Bulk goal')
        ->assertSee('Newest bowl')
        ->assertSeeVolt('layout.navigation');
});

test('only admins can open the admin page', function () {
    foreach ([User::factory()->create(), User::factory()->coach()->create()] as $user) {
        $this->actingAs($user)->get(route('admin'))->assertForbidden();
        $this->actingAs($user)->get(route('recipes.index'))->assertDontSee(route('admin'));
    }

    $admin = User::factory()->admin()->create();
    $this->actingAs($admin)->get(route('admin'))
        ->assertOk()
        ->assertSeeVolt('admin.manage-roles')
        ->assertSeeVolt('admin.manage-tags');
    $this->actingAs($admin)->get(route('recipes.index'))->assertSee(route('admin'));
});

test('admins can add, rename and delete tags', function () {
    $recipe = Recipe::factory()->create();
    $tag = Tag::create(['name' => 'Quick']);
    $recipe->tags()->attach($tag);

    $component = Volt::actingAs(User::factory()->admin()->create())
        ->test('admin.manage-tags')
        ->set('newName', 'High fibre')
        ->call('addTag')
        ->assertHasNoErrors()
        ->assertSee('High fibre')
        ->call('startEditing', $tag->id)
        ->set('editName', 'Under 15 minutes')
        ->call('saveEdit')
        ->assertHasNoErrors();

    expect($tag->fresh()->name)->toBe('Under 15 minutes');

    $component->call('deleteTag', $tag->id);

    expect(Tag::find($tag->id))->toBeNull()
        ->and($recipe->fresh()->tags)->toHaveCount(0) // the recipe stays, it just loses the tag
        ->and(Recipe::find($recipe->id))->not->toBeNull();
});

test('tag names have to be unique and not empty', function () {
    Tag::create(['name' => 'Bulk']);

    Volt::actingAs(User::factory()->admin()->create())
        ->test('admin.manage-tags')
        ->set('newName', 'Bulk')->call('addTag')
        ->assertHasErrors(['newName' => 'unique'])
        ->set('newName', '')->call('addTag')
        ->assertHasErrors(['newName' => 'required']);

    expect(Tag::count())->toBe(1);
});

test('admins can change other peoples roles but not their own', function () {
    $admin = User::factory()->admin()->create();
    $member = User::factory()->create();

    Volt::actingAs($admin)
        ->test('admin.manage-roles')
        ->call('changeRole', $member->id, 'coach')
        ->assertSee("{$member->name} is now a coach.")
        ->call('changeRole', $admin->id, 'member')
        ->assertHasErrors('role');

    expect($member->fresh()->role)->toBe('coach')
        ->and($admin->fresh()->role)->toBe('admin');
});

test('made up roles are rejected', function () {
    $member = User::factory()->create();

    Volt::actingAs(User::factory()->admin()->create())
        ->test('admin.manage-roles')
        ->call('changeRole', $member->id, 'superuser')
        ->assertHasErrors('role');

    expect($member->fresh()->role)->toBe('member');
});

test('members and coaches cannot use the admin components', function () {
    foreach ([User::factory()->create(), User::factory()->coach()->create()] as $user) {
        Volt::actingAs($user)->test('admin.manage-tags')->assertForbidden();
        Volt::actingAs($user)->test('admin.manage-roles')->assertForbidden();
    }
});

test('the user list can be searched', function () {
    User::factory()->create(['name' => 'Priya Patel']);
    User::factory()->create(['name' => 'Tom Jones']);

    Volt::actingAs(User::factory()->admin()->create(['name' => 'Admin Person']))
        ->test('admin.manage-roles')
        ->set('search', 'priya')
        ->assertSee('Priya Patel')
        ->assertDontSee('Tom Jones');
});
