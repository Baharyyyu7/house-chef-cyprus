<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreRecipeStepRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'instruction' => ['required', 'string'],
            'duration_seconds' => ['nullable', 'integer', 'min:0'],
            'temperature_value' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'temperature_unit' => ['nullable', Rule::in(['celsius', 'fahrenheit'])],
            'timer_required' => ['nullable', 'boolean'],
            'is_optional' => ['nullable', 'boolean'],
            'equipment' => ['nullable', 'string', 'max:255'],
            'chef_tip' => ['nullable', 'string'],
            'safety_note' => ['nullable', 'string'],
            'animation_key' => ['nullable', 'string', 'max:100'],
            'completion_hint' => ['nullable', 'string', 'max:255'],
        ];
    }
}
