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
        return ['ingredient_id' => ['required', 'exists:ingredients,id'], 'quantity' => ['nullable', 'numeric', 'gt:0', 'max:100000'], 'unit' => ['nullable', 'string', 'max:30'], 'canonical_quantity' => ['nullable', 'numeric', 'gt:0', 'max:1000000'], 'canonical_unit' => ['nullable', 'in:g,ml,adet'], 'conversion_confidence' => ['nullable', 'in:verified,high,medium,low,unknown'], 'expires_at' => ['nullable', 'date'], 'notes' => ['nullable', 'string', 'max:1000']];
    }
}
