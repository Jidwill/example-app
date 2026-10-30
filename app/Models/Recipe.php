<?php

namespace App\Models;

use Database\Factories\RecipeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

#[Fillable([
    'title',
    'description',
    'ingredients',
    'method',
    'prep_minutes',
    'servings',
    'protein',
    'carbs',
    'fat',
    'image_path',
])]
class Recipe extends Model
{
    /** @use HasFactory<RecipeFactory> */
    use HasFactory;

    /**
     * One to many (inverse): the user who posted the recipe.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * One to many: comments on this recipe.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Many to many: tags such as "Bulk" or "Meal prep", through the recipe_tag pivot table.
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class)->withTimestamps();
    }

    /**
     * Polymorphic one to many: likes on this recipe.
     */
    public function likes(): MorphMany
    {
        return $this->morphMany(Like::class, 'likeable');
    }

    /**
     * Calories per serving, calculated from the macros
     * (4 kcal per gram of protein and carbs, 9 per gram of fat).
     * Used as $recipe->calories, but not stored in the database.
     */
    protected function calories(): Attribute
    {
        return Attribute::make(
            get: fn () => ($this->protein * 4) + ($this->carbs * 4) + ($this->fat * 9),
        );
    }
}
