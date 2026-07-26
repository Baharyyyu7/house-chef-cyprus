<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StorePantryItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return ['ingredient_id' => ['required', 'exists:ingredients,id'], 'quantity' => ['nullable', 'numeric', 'min:0'], 'unit' => ['nullable', 'string', 'max:30'], 'expires_at' => ['nullable', 'date']];
    }
}
