<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ContactRequest;
use App\Models\User;

final class ContactRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, ContactRequest $contactRequest): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, ContactRequest $contactRequest): bool
    {
        return $user->isAdmin();
    }

    public function note(User $user, ContactRequest $contactRequest): bool
    {
        return $user->isAdmin();
    }

    public function reply(User $user, ContactRequest $contactRequest): bool
    {
        return $user->isAdmin();
    }

    public function anonymize(User $user, ContactRequest $contactRequest): bool
    {
        return $user->isAdmin();
    }
}
