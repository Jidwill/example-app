<?php

namespace App\Notifications;

use App\Models\Comment;
use App\Models\Like;
use App\Models\Recipe;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

/**
 * Sent when someone likes your recipe OR your comment. One class works for both
 * because likes are polymorphic - $like->likeable is either a Recipe or a Comment.
 */
class NewLike extends Notification
{
    public function __construct(public Like $like) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $likeable = $this->like->likeable;

        // either way we want the recipe, so the notification can link to it
        $recipe = $likeable instanceof Recipe ? $likeable : $likeable->recipe;

        return [
            'kind' => $likeable instanceof Comment ? 'comment_like' : 'recipe_like',
            'actor_id' => $this->like->user_id,
            'actor_name' => $this->like->user->name,
            'recipe_id' => $recipe->id,
            'recipe_title' => $recipe->title,
            'comment_id' => $likeable instanceof Comment ? $likeable->id : null,
            'excerpt' => $likeable instanceof Comment ? Str::limit($likeable->body, 80) : null,
            // kept so the notification can be removed again if they unlike it
            'like_id' => $this->like->id,
        ];
    }
}
