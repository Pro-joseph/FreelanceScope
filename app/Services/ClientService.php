<?php

namespace App\Services;

use App\Models\Client;
use Illuminate\Pagination\LengthAwarePaginator;

class ClientService
{
    public function listForUser(int $userId): LengthAwarePaginator
    {
        return Client::where('user_id', $userId)
            ->withCount('projects')
            ->latest()
            ->paginate(15);
    }

    public function createForUser(int $userId, array $data): Client
    {
        return Client::create([
            'user_id' => $userId,
            ...$data,
        ]);
    }

    public function update(Client $client, array $data): Client
    {
        $client->update($data);

        return $client;
    }

    public function delete(Client $client): void
    {
        $client->delete();
    }
}
