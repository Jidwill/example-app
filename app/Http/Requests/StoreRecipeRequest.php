<?php

namespace App\Http\Requests;

use App\Models\Recipe;
use Illuminate\Foundation\Http\FormRequest;

class StoreRecipeRequest extends FormRequest
{
    /**
     * Checks RecipePolicy@create, so the rule for who can post lives in one place.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Recipe::class);
    }

    /**
     * Validation rules for the create recipe form.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:1000'],
            'ingredients' => ['required', 'string', 'max:5000'],
            'method' => ['required', 'string', 'max:10000'],
            'prep_minutes' => ['required', 'integer', 'min:1', 'max:600'],
            'servings' => ['required', 'integer', 'min:1', 'max:20'],
            // macros are grams per serving
            'protein' => ['required', 'integer', 'min:0', 'max:500'],
            'carbs' => ['required', 'integer', 'min:0', 'max:500'],
            'fat' => ['required', 'integer', 'min:0', 'max:500'],
            // tags are optional, but any that are sent have to actually exist
            'tags' => ['nullable', 'array'],
            'tags.*' => ['integer', 'distinct', 'exists:tags,id'],
            // optional photo, up to 2MB
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    /**
     * Nicer names for the error messages, e.g. "The prep time field is required."
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'prep_minutes' => 'prep time',
            'tags.*' => 'tag',
            'image' => 'photo',
        ];
    }
}
