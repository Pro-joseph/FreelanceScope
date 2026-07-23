<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Http\Resources\ClientResource;
use App\Models\Client;
use App\Services\ClientService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @group Clients
 *
 * Gestion des clients d'un freelance. Chaque client est lié à un utilisateur (freelance).
 */
class ClientController extends Controller
{
    public function __construct(
        private readonly ClientService $clientService,
    ) {}

    /**
     * Liste des clients
     *
     * Retourne la liste paginée des clients de l'utilisateur connecté.
     *
     * @authenticated
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1, "company_name": "Acme Corp", "email": "contact@acme.com",
     *       "phone": "+212600000000", "projects_count": 3, "created_at": "2026-07-21T09:00:00.000000Z"
     *     }
     *   ],
     *   "meta": { "current_page": 1, "per_page": 15, "total": 1 }
     * }
     */
    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Client::class);

        $clients = $this->clientService->listForUser(auth()->id());

        return ClientResource::collection($clients);
    }

    /**
     * Créer un client
     *
     * Ajoute un nouveau client pour l'utilisateur connecté.
     *
     * @authenticated
     *
     * @bodyParam company_name string required Le nom de l'entreprise. Example: Acme Corp
     * @bodyParam email string L'email du client. Example: contact@acme.com
     * @bodyParam phone string Le numéro de téléphone. Example: +212600000000
     *
     * @response 201 {
     *   "data": {
     *     "id": 1, "company_name": "Acme Corp", "email": "contact@acme.com",
     *     "phone": "+212600000000", "projects_count": 0, "created_at": "..."
     *   }
     * }
     */
    public function store(StoreClientRequest $request): JsonResponse
    {
        $this->authorize('create', Client::class);

        $client = $this->clientService->createForUser(
            auth()->id(),
            $request->validated(),
        );

        return response()->json(new ClientResource($client), 201);
    }

    /**
     * Afficher un client
     *
     * Retourne les détails d'un client spécifique.
     *
     * @authenticated
     *
     * @urlParam client integer required L'ID du client. Example: 1
     *
     * @response 200 {
     *   "data": {
     *     "id": 1, "company_name": "Acme Corp", "email": "contact@acme.com",
     *     "phone": "+212600000000", "projects_count": 3, "created_at": "..."
     *   }
     * }
     */
    public function show(Client $client): ClientResource
    {
        $this->authorize('view', $client);

        $client->loadCount('projects');

        return new ClientResource($client);
    }

    /**
     * Modifier un client
     *
     * @authenticated
     *
     * @urlParam client integer required L'ID du client. Example: 1
     * @bodyParam company_name string Le nom de l'entreprise. Example: Acme Corp Updated
     * @bodyParam email string L'email du client. Example: new@acme.com
     * @bodyParam phone string Le numéro de téléphone. Example: +212600000001
     *
     * @response 200 {
     *   "data": { "id": 1, "company_name": "Acme Corp Updated", ... }
     * }
     */
    public function update(UpdateClientRequest $request, Client $client): ClientResource
    {
        $this->authorize('update', $client);

        $client = $this->clientService->update($client, $request->validated());

        return new ClientResource($client);
    }

    /**
     * Supprimer un client
     *
     * @authenticated
     *
     * @urlParam client integer required L'ID du client. Example: 1
     *
     * @response 204
     */
    public function destroy(Client $client): JsonResponse
    {
        $this->authorize('delete', $client);

        $this->clientService->delete($client);

        return response()->json(null, 204);
    }
}
