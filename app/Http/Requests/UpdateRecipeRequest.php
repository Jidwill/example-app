<?php

namespace App\Http\Requests;

/**
 * Editing has the same rules as posting, so this just extends StoreRecipeRequest
 * and changes who's allowed, plus adds the "remove photo" checkbox.
 */
class UpdateRecipeRequest extends StoreRecipeRequest
{
    /**
     * Checks RecipePolicy@update - only the owner (or an admin) can edit it.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('recipe'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'remove_image' => ['nullable', 'boolean'],
        ];
    }
}
