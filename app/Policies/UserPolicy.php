<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;

class UserPolicy
{
    public function viewAny(User $admin): bool
    {
        return true;
    }

    public function view(User $admin, User $freelance): bool
    {
        return $freelance->role === UserRole::Freelance;
    }

    public function update(User $admin, User $freelance): bool
    {
        return $freelance->role === UserRole::Freelance;
    }

    public function toggleStatut(User $admin, User $freelance): bool
    {
        return $freelance->role === UserRole::Freelance;
    }

    public function delete(User $admin, User $freelance): bool
    {
        return $freelance->role === UserRole::Freelance;
    }
}
