<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Project;
use Illuminate\Pagination\LengthAwarePaginator;

class ProjectService
{
    public function listForUser(int $userId): LengthAwarePaginator
    {
        $clientIds = Client::where('user_id', $userId)->pluck('id');

        return Project::whereIn('client_id', $clientIds)
            ->withCount('features')
            ->latest()
            ->paginate(15);
    }

    public function createForUser(int $userId, array $data): Project
    {
        return Project::create($data);
    }

    public function update(Project $project, array $data): Project
    {
        $project->update($data);

        return $project;
    }

    public function delete(Project $project): void
    {
        $project->delete();
    }
}
