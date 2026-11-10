<?php

use App\Models\Comment;
use App\Models\Like;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

// a valid form submission for editing, tweaked in each test
function editedRecipeData(array $overrides = []): array
{
    return array_merge([
        'title' => 'Updated chicken bowl',
        'description' => 'Now with more rice.',
        'ingredients' => "250g chicken\n200g rice",
        'method' => "Cook the chicken.\nServe over the rice.",
        'prep_minutes' => 25,
        'servings' => 2,
        'protein' => 50,
        'carbs' => 80,
        'fat' => 12,
    ], $overrides);
}

beforeEach(function () {
    // uploads go to a fake disk so tests don't leave files in storage
    Storage::fake('public');
});

test('the owner can edit their recipe', function () {
    $recipe = Recipe::factory()->create();

    $this->actingAs($recipe->user)->get(route('recipes.edit', $recipe))
        ->assertOk()
        ->assertSee('value="'.$recipe->title.'"', false);

    $this->actingAs($recipe->user)
        ->put(route('recipes.update', $recipe), editedRecipeData())
        ->assertRedirect(route('recipes.show', $recipe))
        ->assertSessionHas('success');

    expect($recipe->fresh()->title)->toBe('Updated chicken bowl');
});

test('other members and coaches cannot edit someone elses recipe', function () {
    $recipe = Recipe::factory()->create();

    foreach ([User::factory()->create(), User::factory()->coach()->create()] as $user) {
        $this->actingAs($user)->get(route('recipes.edit', $recipe))->assertForbidden();
        $this->actingAs($user)->put(route('recipes.update', $recipe), editedRecipeData())->assertForbidden();
        $this->actingAs($user)->delete(route('recipes.destroy', $recipe))->assertForbidden();
    }

    expect($recipe->fresh()->title)->not->toBe('Updated chicken bowl');
});

test('admins can edit and delete anyones recipe', function () {
    $admin = User::factory()->admin()->create();
    $recipe = Recipe::factory()->create();

    $this->actingAs($admin)->put(route('recipes.update', $recipe), editedRecipeData())->assertRedirect();
    $this->actingAs($admin)->delete(route('recipes.destroy', $recipe))->assertRedirect(route('recipes.index'));

    expect(Recipe::find($recipe->id))->toBeNull();
});

test('edit and delete buttons only show for people who can use them', function () {
    $recipe = Recipe::factory()->create();

    $this->actingAs($recipe->user)->get(route('recipes.show', $recipe))
        ->assertSee(route('recipes.edit', $recipe))
        ->assertSee(route('recipes.destroy', $recipe));

    $this->actingAs(User::factory()->create())->get(route('recipes.show', $recipe))
        ->assertDontSee(route('recipes.edit', $recipe))
        ->assertDontSee('Delete this recipe?');
});

test('deleting a recipe also deletes its comments and all the likes', function () {
    $recipe = Recipe::factory()->create();
    $comment = Comment::factory()->for($recipe)->create();
    Like::factory()->for($recipe, 'likeable')->create();
    Like::factory()->for($comment, 'likeable')->create();
    $otherLike = Like::factory()->create(); // on a different recipe, should stay

    $this->actingAs($recipe->user)->delete(route('recipes.destroy', $recipe))
        ->assertSessionHas('success');

    expect(Comment::count())->toBe(0)
        ->and(Like::pluck('id')->all())->toBe([$otherLike->id]);
});

test('a photo can be uploaded with a new recipe and is shown with alt text', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('recipes.store'), editedRecipeData([
        'image' => UploadedFile::fake()->image('bowl.jpg', 800, 600),
    ]));

    $recipe = Recipe::first();
    Storage::disk('public')->assertExists($recipe->image_path);

    $this->get(route('recipes.show', $recipe))
        ->assertSee($recipe->image_url)
        ->assertSee('alt="Photo of Updated chicken bowl"', false);

    $this->get(route('recipes.index'))->assertSee($recipe->image_url);
});

test('replacing or removing a photo deletes the old file', function () {
    $recipe = Recipe::factory()->create();
    $this->actingAs($recipe->user);

    $this->put(route('recipes.update', $recipe), editedRecipeData(['image' => UploadedFile::fake()->image('one.jpg')]));
    $first = $recipe->fresh()->image_path;

    $this->put(route('recipes.update', $recipe), editedRecipeData(['image' => UploadedFile::fake()->image('two.png')]));
    $second = $recipe->fresh()->image_path;

    Storage::disk('public')->assertMissing($first);
    Storage::disk('public')->assertExists($second);

    $this->put(route('recipes.update', $recipe), editedRecipeData(['remove_image' => '1']));

    Storage::disk('public')->assertMissing($second);
    expect($recipe->fresh()->image_path)->toBeNull();
});

test('deleting a recipe deletes its photo', function () {
    $recipe = Recipe::factory()->create();
    $this->actingAs($recipe->user)
        ->put(route('recipes.update', $recipe), editedRecipeData(['image' => UploadedFile::fake()->image('bowl.jpg')]));
    $path = $recipe->fresh()->image_path;

    $this->delete(route('recipes.destroy', $recipe));

    Storage::disk('public')->assertMissing($path);
});

test('files that are not images are rejected', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('recipes.store'), editedRecipeData([
            'image' => UploadedFile::fake()->create('notes.pdf', 100, 'application/pdf'),
        ]))
        ->assertSessionHasErrors('image');

    expect(Recipe::count())->toBe(0);
});
