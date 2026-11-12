<?php

use App\Http\Controllers\RecipeController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

// recipes - you have to be logged in for all of these
// resource() makes all 7 named routes for me (recipes.index, recipes.edit etc.),
// and the policies decide who can actually edit or delete
Route::resource('recipes', RecipeController::class)
    ->middleware('auth');

// public user pages - clicking someone's name shows their recipes and comments.
// Not /profile because Breeze already uses that for your own account settings
Route::get('users/{user}', [UserController::class, 'show'])
    ->middleware('auth')
    ->name('users.show');

// your notifications (likes and comments on your stuff) - the list itself is a Livewire component
Route::view('notifications', 'notifications.index')
    ->middleware('auth')
    ->name('notifications.index');

// coaches and admins can hide a recipe (checked in RecipePolicy@hide)
Route::patch('recipes/{recipe}/hide', [RecipeController::class, 'toggleHidden'])
    ->middleware('auth')
    ->name('recipes.hide');

require __DIR__.'/auth.php';
