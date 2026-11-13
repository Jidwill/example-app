<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Checks the filters on the recipes page (they come from the URL, e.g. ?min_protein=30).
 * If any are invalid the user goes back to the unfiltered list with an error message,
 * instead of the query running with nonsense values.
 */
class FilterRecipesRequest extends FormRequest
{
    protected $redirectRoute = 'recipes.index';

    public function authorize(): bool
    {
        return true; // the route already needs you to be logged in
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'tag' => ['nullable', 'integer', 'exists:tags,id'],
            'min_protein' => ['nullable', 'integer', 'min:0', 'max:500'],
            'max_calories' => ['nullable', 'integer', 'min:50', 'max:5000'],
            'sort' => ['nullable', 'in:newest,protein,lean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'min_protein' => 'minimum protein',
            'max_calories' => 'maximum calories',
        ];
    }
}
