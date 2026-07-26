<?php

namespace App\Policies;

use App\Models\Devis;
use App\Models\User;

class DevisPolicy
{
    public function view(User $user, Devis $devis): bool
    {
        return $devis->client?->user_id === $user->id;
    }

    public function update(User $user, Devis $devis): bool
    {
        return $devis->client?->user_id === $user->id;
    }

    public function delete(User $user, Devis $devis): bool
    {
        return $devis->client?->user_id === $user->id;
    }
}
