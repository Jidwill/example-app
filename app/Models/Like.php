<?php

namespace App\Models;

use App\Notifications\NewLike;
use Database\Factories\LikeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Notifications\DatabaseNotification;

class Like extends Model
{
    /** @use HasFactory<LikeFactory> */
    use HasFactory;

    /**
     * Liking someone's recipe or comment notifies them. Unliking removes that
     * notification again, so liking and unliking over and over doesn't spam them.
     */
    protected static function booted(): void
    {
        static::created(function (Like $like) {
            $owner = $like->likeable->user;

            if ($owner->id !== $like->user_id) {
                $owner->notify(new NewLike($like));
            }
        });

        static::deleted(function (Like $like) {
            DatabaseNotification::whereJsonContains('data->like_id', $like->id)->delete();
        });
    }

    /**
     * Who liked it.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The thing that was liked - either a Recipe or a Comment.
     */
    public function likeable(): MorphTo
    {
        return $this->morphTo();
    }
}
