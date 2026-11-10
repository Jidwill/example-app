<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\Recipe;
use App\Models\User;

/**
 * Who can do what with comments.
 *
 * Member - comments on recipes they can see, edits/deletes their own comments
 * Coach  - can also edit or delete anyone's comments (moderation)
 * Admin  - can do everything (see before())
 */
class CommentPolicy
{
    /**
     * Admins are allowed everything, null means "carry on to the normal check".
     */
    public function before(User $user, string $ability): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    /**
     * You can comment on any recipe you're allowed to see.
     */
    public function create(User $user, Recipe $recipe): bool
    {
        return $user->can('view', $recipe);
    }

    /**
     * Your own comments, or anyone's if you're a coach.
     */
    public function update(User $user, Comment $comment): bool
    {
        return $user->id === $comment->user_id || $user->isCoach();
    }

    /**
     * Same rule as editing.
     */
    public function delete(User $user, Comment $comment): bool
    {
        return $this->update($user, $comment);
    }
}
