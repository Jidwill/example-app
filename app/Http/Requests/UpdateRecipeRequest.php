<?php

namespace App\Http\Requests;

/**
 * Editing uses the same rules as posting a recipe, so this extends StoreRecipeRequest
 * and only changes who's allowed and adds the "remove photo" checkbox.
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
