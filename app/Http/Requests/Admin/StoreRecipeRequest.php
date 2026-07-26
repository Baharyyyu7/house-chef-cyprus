<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreRecipeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return ['title' => ['required', 'string', 'max:255'], 'slug' => ['required', 'alpha_dash', 'max:255', Rule::unique('recipes', 'slug')->ignore($this->route('recipe'))], 'excerpt' => ['nullable', 'string', 'max:500'], 'description' => ['nullable', 'string'], 'cuisine_id' => ['nullable', 'exists:cuisines,id'], 'category_id' => ['nullable', 'exists:categories,id'], 'servings' => ['required', 'integer', 'min:1', 'max:100'], 'prep_minutes' => ['required', 'integer', 'min:0', 'max:1440'], 'cook_minutes' => ['required', 'integer', 'min:0', 'max:1440'], 'difficulty' => ['required', Rule::in(['Kolay', 'Orta', 'Zor'])], 'status' => ['required', Rule::in(['draft', 'published'])]];
    }
}
