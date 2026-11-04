<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['name'])]
class Tag extends Model
{
    /**
     * Recipes that have this tag.
     */
    public function recipes(): BelongsToMany
    {
        return $this->belongsToMany(Recipe::class)->withTimestamps();
    }
}
