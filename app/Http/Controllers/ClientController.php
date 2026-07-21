<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Http\Resources\ClientResource;
use App\Models\Client;
use App\Services\ClientService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ClientController extends Controller
{
    public function __construct(
        private readonly ClientService $clientService,
    ) {}

    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Client::class);

        $clients = $this->clientService->listForUser(auth()->id());

        return ClientResource::collection($clients);
    }

    public function store(StoreClientRequest $request): JsonResponse
    {
        $this->authorize('create', Client::class);

        $client = $this->clientService->createForUser(
            auth()->id(),
            $request->validated(),
        );

        return response()->json(new ClientResource($client), 201);
    }

    public function show(Client $client): ClientResource
    {
        $this->authorize('view', $client);

        $client->loadCount('projects');

        return new ClientResource($client);
    }

    public function update(UpdateClientRequest $request, Client $client): ClientResource
    {
        $this->authorize('update', $client);

        $client = $this->clientService->update($client, $request->validated());

        return new ClientResource($client);
    }

    public function destroy(Client $client): JsonResponse
    {
        $this->authorize('delete', $client);

        $this->clientService->delete($client);

        return response()->json(null, 204);
    }
}
