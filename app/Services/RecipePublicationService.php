<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Recipe;
use App\Models\User;
use Illuminate\Validation\ValidationException;

final class RecipePublicationService
{
    public function __construct(private readonly RecipeCompletenessService $completeness) {}

    /** @return array{score:int,status:string,checks:array<string,array{complete:bool,weight:int,message:string}>} */
    public function publish(Recipe $recipe, User $reviewer): array
    {
        $result = $this->completeness->refresh($recipe);
        if ($result['status'] !== 'publishable' || $recipe->verification_status !== 'verified') {
            throw ValidationException::withMessages(['publication' => 'Tarif yayın kontrol listesini henüz tamamlamadı.']);
        }

        $recipe->update([
            'status' => 'published',
            'editorial_status' => 'published',
            'published_at' => $recipe->published_at ?? now(),
            'editorial_reviewed_at' => now(),
            'editorial_reviewed_by_user_id' => $reviewer->id,
        ]);

        return $result;
    }

    public function unpublish(Recipe $recipe): void
    {
        $recipe->update(['status' => 'draft', 'editorial_status' => 'needs_update']);
    }
}
