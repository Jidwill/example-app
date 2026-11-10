<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRecipeRequest;
use App\Models\Recipe;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class RecipeController extends Controller
{
    /**
     * Show all the recipes as cards, 9 per page.
     */
    public function index(Request $request): View
    {
        // eager load the user and tags so each card doesn't run its own queries (N+1),
        // and withCount adds comments_count and likes_count without loading every row.
        // visibleTo() leaves out hidden recipes unless you're allowed to see them
        $recipes = Recipe::visibleTo($request->user())
            ->with(['user', 'tags'])
            ->withCount(['comments', 'likes'])
            ->latest()
            ->paginate(9);

        return view('recipes.index', ['recipes' => $recipes]);
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
     * Save a new recipe. Validation and the permission check happen in
     * StoreRecipeRequest before this method even runs.
     */
    public function store(StoreRecipeRequest $request): RedirectResponse
    {
        // creating it through the user's recipes() relationship fills in user_id for me,
        // and only validated fields get saved (tags go in the pivot table instead)
        $recipe = $request->user()->recipes()->create(
            $request->safe()->except('tags')
        );

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
        // RecipePolicy@view - a hidden recipe gives a 403 unless it's yours or you're a coach/admin
        Gate::authorize('view', $recipe);

        $recipe->load([
            'user',
            'tags',
            'comments' => fn ($query) => $query->with('user')->withCount('likes')->latest(),
        ])->loadCount('likes');

        return view('recipes.show', ['recipe' => $recipe]);
    }

    /**
     * Hide a recipe, or show it again if it's already hidden (coaches and admins only).
     */
    public function toggleHidden(Recipe $recipe): RedirectResponse
    {
        Gate::authorize('hide', $recipe);

        // set directly rather than mass assigned, since is_hidden isn't fillable
        $recipe->is_hidden = ! $recipe->is_hidden;
        $recipe->save();

        return redirect()
            ->route('recipes.show', $recipe)
            ->with('success', $recipe->is_hidden
                ? 'Recipe hidden. Only the owner, coaches and admins can see it now.'
                : 'Recipe is visible to everyone again.');
    }
}
