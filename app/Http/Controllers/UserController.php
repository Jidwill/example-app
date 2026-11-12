<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * A user's public page: their profile, plus all their recipes AND all their comments.
     * (This is separate from Breeze's /profile page, which is for your own account settings.)
     */
    public function show(Request $request, User $user): View
    {
        $viewer = $request->user();

        $user->load('profile')->loadCount('recipes', 'comments');

        // visibleTo() hides recipes the viewer isn't allowed to see (same rule as everywhere else).
        // Two lists on one page, so each paginator needs its own page name in the URL
        $recipes = $user->recipes()
            ->visibleTo($viewer)
            ->with(['user', 'tags'])
            ->withCount(['comments', 'likes'])
            ->latest()
            ->paginate(6, pageName: 'recipes_page')
            ->withQueryString()
            ->fragment('recipes');

        // only comments on recipes the viewer can see, with the recipe so it can link back to it
        $comments = $user->comments()
            ->whereHas('recipe', fn ($query) => $query->visibleTo($viewer))
            ->with('recipe')
            ->latest()
            ->paginate(10, pageName: 'comments_page')
            ->withQueryString()
            ->fragment('comments');

        return view('users.show', [
            'user' => $user,
            'recipes' => $recipes,
            'comments' => $comments,
        ]);
    }
}
