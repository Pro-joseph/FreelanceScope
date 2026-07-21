<?php

namespace App\Policies;

use App\Models\Estimate;
use App\Models\User;

class EstimatePolicy
{
    public function view(User $user, Estimate $estimate): bool
    {
        return $estimate->feature->project->client->user_id === $user->id;
    }

    public function update(User $user, Estimate $estimate): bool
    {
        return $estimate->feature->project->client->user_id === $user->id;
    }
}
