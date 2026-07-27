<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Unit;
use Illuminate\Foundation\Http\FormRequest;

final class StorePantryItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return ['ingredient_id' => ['required', 'exists:ingredients,id'], 'quantity' => ['nullable', 'numeric', 'gt:0', 'max:100000'], 'unit' => ['nullable', 'string', 'max:30', function (string $attribute, mixed $value, \Closure $fail): void {
            $legacy = ['g', 'kg', 'ml', 'l', 'demet'];
            $unit = Unit::query()->where('code', $value)->first();
            if (($unit !== null && ! $unit->is_active) || ($unit === null && ! in_array($value, $legacy, true))) {
                $fail('Geçersiz veya pasif ölçü seçilemez.');
            }
        }], 'canonical_quantity' => ['nullable', 'numeric', 'gt:0', 'max:1000000'], 'canonical_unit' => ['nullable', 'in:g,ml,adet'], 'conversion_confidence' => ['nullable', 'in:verified,high,medium,low,unknown'], 'expires_at' => ['nullable', 'date'], 'notes' => ['nullable', 'string', 'max:1000']];
    }
}
