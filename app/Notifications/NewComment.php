<?php

namespace App\Notifications;

use App\Models\Comment;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

/**
 * Sent to a recipe's owner when someone else comments on it.
 * Stored in the notifications table (database channel) and shown on the notifications page.
 */
class NewComment extends Notification
{
    public function __construct(public Comment $comment) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * What gets saved in the data column. Names and titles are copied in so the
     * notification still reads properly even if they change later.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'kind' => 'comment',
            'actor_id' => $this->comment->user_id,
            'actor_name' => $this->comment->user->name,
            'recipe_id' => $this->comment->recipe_id,
            'recipe_title' => $this->comment->recipe->title,
            'comment_id' => $this->comment->id,
            'excerpt' => Str::limit($this->comment->body, 80),
        ];
    }
}
