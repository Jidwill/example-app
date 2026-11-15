<?php

namespace App\Models;

use App\Notifications\NewComment;
use Database\Factories\CommentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Notifications\DatabaseNotification;

#[Fillable(['body'])]
class Comment extends Model
{
    /** @use HasFactory<CommentFactory> */
    use HasFactory;

    /**
     * When a comment is posted, notify the recipe owner (unless it's their own comment).
     * When it's deleted, delete its likes too (they're polymorphic so the database can't cascade them)
     * and any notifications about it, so nobody gets a link to a comment that's gone.
     * I used whereJsonContains because where('data->comment_id', ...) threw an error on MySQL
     * for notifications where comment_id is null (like recipe likes).
     * The seeders turn model events off so seeding doesn't make hundreds of these.
     */
    protected static function booted(): void
    {
        static::created(function (Comment $comment) {
            $owner = $comment->recipe->user;

            if ($owner->id !== $comment->user_id) {
                $owner->notify(new NewComment($comment));
            }
        });

        static::deleting(function (Comment $comment) {
            $comment->likes()->delete();
            DatabaseNotification::whereJsonContains('data->comment_id', $comment->id)->delete();
        });
    }

    /**
     * Who wrote the comment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The recipe this comment is on.
     */
    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }

    /**
     * Likes on this comment (same likes table as recipe likes).
     */
    public function likes(): MorphMany
    {
        return $this->morphMany(Like::class, 'likeable');
    }
}
