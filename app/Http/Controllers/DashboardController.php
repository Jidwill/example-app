<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * The logged-in home page. Only one action, so it's an invokable controller (__invoke).
 */
class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user()->load('profile')->loadCount('recipes', 'comments');

        // the newest few recipes this user is allowed to see, same as the recipes page
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
