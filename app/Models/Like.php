<?php

namespace App\Models;

use Database\Factories\LikeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Like extends Model
{
    /** @use HasFactory<LikeFactory> */
    use HasFactory;

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
