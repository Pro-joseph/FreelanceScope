<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * @group Clients
 *
 * Gestion des clients d'un freelance. Chaque client est lié à un utilisateur (freelance).
 */
class ClientController extends Controller
{
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
    public function index(): LengthAwarePaginator
    {
        return Client::where('user_id', auth()->id())
            ->withCount('projects')
            ->latest()
            ->paginate(15);
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
     *   "id": 1, "company_name": "Acme Corp", "email": "contact@acme.com",
     *   "phone": "+212600000000", "projects_count": 0, "created_at": "..."
     * }
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        $client = Client::create([
            'user_id' => auth()->id(),
            ...$validated,
        ]);

        return response()->json(['data' => $client], 201);
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
     *   "id": 1, "company_name": "Acme Corp", "email": "contact@acme.com",
     *   "phone": "+212600000000", "projects_count": 3, "created_at": "..."
     * }
     */
    public function show(Client $client): Client
    {
        $this->authorize('view', $client);

        $client->loadCount('projects');

        return response()->json(['data' => $client]);
    }

    /**
     * Modifier un client
     *
     * @authenticated
     *
     * @urlParam client integer required L'ID du client. Example: 1
     *
     * @bodyParam company_name string Le nom de l'entreprise. Example: Acme Corp Updated
     * @bodyParam email string L'email du client. Example: new@acme.com
     * @bodyParam phone string Le numéro de téléphone. Example: +212600000001
     *
     * @response 200 {
     *   "id": 1, "company_name": "Acme Corp Updated", ...
     * }
     */
    public function update(Request $request, Client $client): Client
    {
        $this->authorize('update', $client);

        $validated = $request->validate([
            'company_name' => ['sometimes', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        $client->update($validated);

        return response()->json(['data' => $client]);
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

        $client->delete();

        return response()->json(null, 204);
    }
}
