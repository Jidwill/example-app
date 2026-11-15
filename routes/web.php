<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RecipeController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// landing page for guests, logged in users go straight to the dashboard
Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : view('welcome');
})->name('home');

// the logged-in home page (Breeze sends people here after logging in)
Route::get('dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

// recipes, all of these need you to be logged in
// resource() makes all 7 routes (recipes.index, recipes.edit etc.)
// and the policies decide who can actually edit or delete
Route::resource('recipes', RecipeController::class)
    ->middleware('auth');

// user pages, clicking someone's name shows their recipes and comments.
// Not /profile because Breeze already uses that for your own account settings
Route::get('users/{user}', [UserController::class, 'show'])
    ->middleware('auth')
    ->name('users.show');

// your notifications (likes and comments on your stuff), the list is a Livewire component
Route::view('notifications', 'notifications.index')
    ->middleware('auth')
    ->name('notifications.index');

// admin page for roles and tags. can: checks the assign-roles gate
// (admins only) before the page loads, so members and coaches get a 403
Route::view('admin', 'admin.index')
    ->middleware(['auth', 'can:assign-roles'])
    ->name('admin');

// coaches and admins can hide a recipe (checked in RecipePolicy@hide)
Route::patch('recipes/{recipe}/hide', [RecipeController::class, 'toggleHidden'])
    ->middleware('auth')
    ->name('recipes.hide');

require __DIR__.'/auth.php';
