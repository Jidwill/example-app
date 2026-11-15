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
        // Laravel finds RecipePolicy and CommentPolicy by itself because of the naming
        // (App\Models\Recipe -> App\Policies\RecipePolicy).
        // These gates are for admin stuff that isn't about one model.
        Gate::define('manage-tags', fn (User $user) => $user->isAdmin());
        Gate::define('assign-roles', fn (User $user) => $user->isAdmin());
    }
}
