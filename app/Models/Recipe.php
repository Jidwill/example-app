<?php

namespace App\Models;

use Database\Factories\RecipeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Storage;

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
     * is_hidden is left out of fillable on purpose - only coaches and admins
     * should be able to change it, through the hide button.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_hidden' => 'boolean',
        ];
    }

    /**
     * Clean up when a recipe is deleted. Comments go automatically because of the
     * foreign key cascade, but likes are polymorphic so the database can't cascade them,
     * and the image file lives on disk rather than in the database.
     */
    protected static function booted(): void
    {
        static::deleting(function (Recipe $recipe) {
            $recipe->likes()->delete();

            // likes on this recipe's comments, since those comments are about to go too
            Like::where('likeable_type', Comment::class)
                ->whereIn('likeable_id', $recipe->comments()->select('id'))
                ->delete();

            // notifications about this recipe (comments and likes on it) would link to a 404 otherwise
            DatabaseNotification::whereJsonContains('data->recipe_id', $recipe->id)->delete();

            $recipe->deleteImage();
        });
    }

    /**
     * Full URL for the recipe photo, or null if there isn't one.
     * asset() uses the current site address so it works whatever APP_URL is set to.
     */
    protected function imageUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->image_path ? asset('storage/'.$this->image_path) : null,
        );
    }

    /**
     * Delete the photo file from storage/app/public (used when replacing or removing it).
     */
    public function deleteImage(): void
    {
        if ($this->image_path) {
            Storage::disk('public')->delete($this->image_path);
        }
    }

    /**
     * Only the recipes this user is allowed to see. Coaches and admins see everything,
     * everyone else sees recipes that aren't hidden plus their own.
     * Matches RecipePolicy@view so the list and the recipe page agree.
     */
    public function scopeVisibleTo(Builder $query, User $user): void
    {
        if ($user->isCoach() || $user->isAdmin()) {
            return;
        }

        $query->where(function (Builder $query) use ($user) {
            $query->where('is_hidden', false)->orWhere('user_id', $user->id);
        });
    }

    /**
     * Who posted the recipe.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * All the comments on this recipe.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Tags like Bulk or Meal prep, linked through the recipe_tag pivot table.
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class)->withTimestamps();
    }

    /**
     * Likes on this recipe (shares the likes table with comments).
     */
    public function likes(): MorphMany
    {
        return $this->morphMany(Like::class, 'likeable');
    }

    /**
     * Works out calories per serving from the macros
     * (4 per gram of protein and carbs, 9 per gram of fat).
     * Use it like $recipe->calories - it isn't saved in the database.
     */
    protected function calories(): Attribute
    {
        return Attribute::make(
            get: fn () => ($this->protein * 4) + ($this->carbs * 4) + ($this->fat * 9),
        );
    }

    /**
     * The same calories sum as above, but as SQL, so the database can filter and sort by it
     * (calories aren't a column, so the query has to work them out).
     */
    public const CALORIES_SQL = '(protein * 4 + carbs * 4 + fat * 9)';

    /**
     * Applies the filters from the recipes page. Every filter is optional.
     *
     * @param  array{tag?: int|string|null, min_protein?: int|string|null, max_calories?: int|string|null, sort?: string|null}  $filters
     */
    public function scopeFilter(Builder $query, array $filters): void
    {
        // only recipes with this tag (through the recipe_tag pivot table)
        $query->when($filters['tag'] ?? null, function (Builder $query, $tagId) {
            $query->whereHas('tags', fn (Builder $tags) => $tags->whereKey($tagId));
        });

        $query->when($filters['min_protein'] ?? null, function (Builder $query, $grams) {
            $query->where('protein', '>=', (int) $grams);
        });

        // the value is passed as a binding (?) so it can't be used for SQL injection
        $query->when($filters['max_calories'] ?? null, function (Builder $query, $kcal) {
            $query->whereRaw(self::CALORIES_SQL.' <= ?', [(int) $kcal]);
        });

        match ($filters['sort'] ?? 'newest') {
            'protein' => $query->orderByDesc('protein')->latest(),
            // grams of protein per 100 kcal - the best "lean" recipes first.
            // NULLIF stops a divide by zero if a recipe somehow has 0 calories
            'lean' => $query->orderByRaw('protein * 100.0 / NULLIF('.self::CALORIES_SQL.', 0) DESC')->latest(),
            default => $query->latest(),
        };
    }
}
