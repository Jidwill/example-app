<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // RecipePolicy and CommentPolicy are found automatically because they follow
        // the naming convention (App\Models\Recipe -> App\Policies\RecipePolicy).
        // These gates are for admin-only actions that aren't about a single model.
        Gate::define('manage-tags', fn (User $user) => $user->isAdmin());
        Gate::define('assign-roles', fn (User $user) => $user->isAdmin());
    }
}
