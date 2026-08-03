<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\RecipeCollection;
use App\Models\User;

final class RecipeCollectionPolicy
{
    public function view(User $user, RecipeCollection $collection): bool
    {
        return $collection->user_id === $user->id;
    }

    public function update(User $user, RecipeCollection $collection): bool
    {
        return $this->view($user, $collection);
    }

    public function delete(User $user, RecipeCollection $collection): bool
    {
        return $this->view($user, $collection);
    }
}
