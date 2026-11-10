<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRecipeRequest;
use App\Models\Recipe;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RecipeController extends Controller
{
    /**
     * Show all the recipes as cards, 9 per page.
     */
    public function index(): View
    {
        // eager load the user and tags so each card doesn't run its own queries (N+1),
        // and withCount adds comments_count and likes_count without loading every row
        $recipes = Recipe::with(['user', 'tags'])
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
        // tags are needed for the checkboxes on the form
        return view('recipes.create', ['tags' => Tag::orderBy('name')->get()]);
    }

    /**
     * Save a new recipe. Validation happens in StoreRecipeRequest before
     * this method even runs - if it fails Laravel sends the user back to the form.
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
        // route model binding gives me the recipe (or a 404 if the id doesn't exist)
        $recipe->load([
            'user',
            'tags',
            'comments' => fn ($query) => $query->with('user')->withCount('likes')->latest(),
        ])->loadCount('likes');

        return view('recipes.show', ['recipe' => $recipe]);
    }
}
