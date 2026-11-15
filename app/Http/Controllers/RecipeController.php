<?php

namespace App\Http\Controllers;

use App\Http\Requests\FilterRecipesRequest;
use App\Http\Requests\StoreRecipeRequest;
use App\Http\Requests\UpdateRecipeRequest;
use App\Models\Recipe;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class RecipeController extends Controller
{
    /**
     * Lists the recipes as cards, 9 per page, with optional filters
     * (tag, min protein, max calories) and sorting.
     */
    public function index(FilterRecipesRequest $request): View
    {
        $filters = $request->validated();

        // eager load user and tags so it's not doing extra queries for every card (N+1).
        // withCount gives comments_count and likes_count without loading them all.
        // visibleTo() leaves out hidden recipes unless you're allowed to see them
        // and filter() is my scope in the Recipe model
        $recipes = Recipe::visibleTo($request->user())
            ->filter($filters)
            ->with(['user', 'tags'])
            ->withCount(['comments', 'likes'])
            ->paginate(9)
            ->withQueryString(); // so page 2 keeps the same filters

        return view('recipes.index', [
            'recipes' => $recipes,
            'tags' => Tag::orderBy('name')->get(),
            'filters' => $filters,
        ]);
    }

    /**
     * Show the form for posting a new recipe.
     */
    public function create(): View
    {
        Gate::authorize('create', Recipe::class);

        // tags are needed for the checkboxes on the form
        return view('recipes.create', ['tags' => Tag::orderBy('name')->get()]);
    }

    /**
     * Saves a new recipe. StoreRecipeRequest does the validation and
     * permission check before this runs.
     */
    public function store(StoreRecipeRequest $request): RedirectResponse
    {
        // just the validated fields, minus the tags and the photo
        $data = $request->safe()->except(['tags', 'image']);

        if ($request->hasFile('image')) {
            // saves into storage/app/public/recipes with a random file name
            $data['image_path'] = $request->file('image')->store('recipes', 'public');
        }

        // going through $user->recipes() fills in user_id automatically
        $recipe = $request->user()->recipes()->create($data);

        $recipe->tags()->sync($request->validated('tags', []));

        return redirect()
            ->route('recipes.show', $recipe)
            ->with('success', 'Your recipe has been posted.');
    }

    /**
     * Show one recipe with its comments.
     */
    public function show(Recipe $recipe): View
    {
        // RecipePolicy@view - hidden recipes give a 403 unless it's yours or you're a coach/admin
        Gate::authorize('view', $recipe);

        // comments and likes are Livewire components so they load their own data
        $recipe->load(['user', 'tags']);

        return view('recipes.show', ['recipe' => $recipe]);
    }

    /**
     * Show the form for editing a recipe (owner or admin only).
     */
    public function edit(Recipe $recipe): View
    {
        Gate::authorize('update', $recipe);

        return view('recipes.edit', [
            'recipe' => $recipe->load('tags'),
            'tags' => Tag::orderBy('name')->get(),
        ]);
    }

    /**
     * Saves changes to a recipe. UpdateRecipeRequest checks permission and validates.
     */
    public function update(UpdateRecipeRequest $request, Recipe $recipe): RedirectResponse
    {
        $data = $request->safe()->except(['tags', 'image', 'remove_image']);

        // the old photo gets deleted if it's being replaced or removed
        if ($request->hasFile('image') || $request->boolean('remove_image')) {
            $recipe->deleteImage();
            $data['image_path'] = null;
        }

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('recipes', 'public');
        }

        $recipe->update($data);
        $recipe->tags()->sync($request->validated('tags', []));

        return redirect()
            ->route('recipes.show', $recipe)
            ->with('success', 'Recipe updated.');
    }

    /**
     * Deletes a recipe (owner or admin only). The Recipe model deletes
     * its likes and photo too.
     */
    public function destroy(Recipe $recipe): RedirectResponse
    {
        Gate::authorize('delete', $recipe);

        $recipe->delete();

        return redirect()
            ->route('recipes.index')
            ->with('success', 'Recipe deleted.');
    }

    /**
     * Hide a recipe, or show it again if it's already hidden (coaches and admins only).
     */
    public function toggleHidden(Recipe $recipe): RedirectResponse
    {
        Gate::authorize('hide', $recipe);

        // set it directly because is_hidden isn't fillable
        $recipe->is_hidden = ! $recipe->is_hidden;
        $recipe->save();

        return redirect()
            ->route('recipes.show', $recipe)
            ->with('success', $recipe->is_hidden
                ? 'Recipe hidden. Only the owner, coaches and admins can see it now.'
                : 'Recipe is visible to everyone again.');
    }
}
