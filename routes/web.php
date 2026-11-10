<?php

use App\Http\Controllers\RecipeController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

// recipes - you have to be logged in for all of these
// resource() makes the named routes for me (recipes.index, recipes.show etc.)
// edit, update and destroy get added once the policies are done
Route::resource('recipes', RecipeController::class)
    ->only(['index', 'create', 'store', 'show'])
    ->middleware('auth');

// coaches and admins can hide a recipe (checked in RecipePolicy@hide)
Route::patch('recipes/{recipe}/hide', [RecipeController::class, 'toggleHidden'])
    ->middleware('auth')
    ->name('recipes.hide');

require __DIR__.'/auth.php';
