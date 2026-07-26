<?php

namespace App\Policies;

use App\Models\ProjectFeature;
use App\Models\User;

class ProjectFeaturePolicy
{
    public function view(User $user, ProjectFeature $feature): bool
    {
        return $feature->project->client->user_id === $user->id;
    }

    public function update(User $user, ProjectFeature $feature): bool
    {
        return $feature->project->client->user_id === $user->id;
    }

    public function delete(User $user, ProjectFeature $feature): bool
    {
        return $feature->project->client->user_id === $user->id;
    }
}
