<?php

namespace App\Policies;

use App\Models\Recipe;
use App\Models\User;

/**
 * Who can do what with recipes.
 *
 * Member - posts recipes and edits/deletes their own
 * Coach  - same as a member, plus can hide or unhide anyone's recipe
 * Admin  - can do everything (see before())
 */
class RecipePolicy
{
    /**
     * Runs before every other check. Admins are allowed everything,
     * returning null means "carry on to the normal check".
     */
    public function before(User $user, string $ability): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    /**
     * Anyone logged in can see the recipe list.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Hidden recipes can only be seen by their owner and coaches.
     */
    public function view(User $user, Recipe $recipe): bool
    {
        return ! $recipe->is_hidden
            || $user->id === $recipe->user_id
            || $user->isCoach();
    }

    /**
     * Any logged in user can post a recipe.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Only the person who posted it can edit it (coaches moderate by hiding instead).
     */
    public function update(User $user, Recipe $recipe): bool
    {
        return $user->id === $recipe->user_id;
    }

    /**
     * Only the person who posted it can delete it.
     */
    public function delete(User $user, Recipe $recipe): bool
    {
        return $user->id === $recipe->user_id;
    }

    /**
     * Coaches can hide or unhide any recipe, e.g. if the macros are clearly wrong.
     */
    public function hide(User $user, Recipe $recipe): bool
    {
        return $user->isCoach();
    }
}
