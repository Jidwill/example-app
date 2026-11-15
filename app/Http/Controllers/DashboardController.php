<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Home page for logged in users. It only does one thing so it's an invokable controller.
 */
class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user()->load('profile')->loadCount('recipes', 'comments');

        // latest few recipes this user can see (same rule as the recipes page)
        $latest = Recipe::visibleTo($user)
            ->with(['user', 'tags'])
            ->withCount(['comments', 'likes'])
            ->latest()
            ->take(3)
            ->get();

        return view('dashboard', [
            'user' => $user,
            'latest' => $latest,
            'unread' => $user->unreadNotifications()->count(),
        ]);
    }
}
