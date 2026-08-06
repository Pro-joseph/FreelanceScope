<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use App\Models\Client;
use App\Models\Devis;
use App\Models\Project;
use Illuminate\Http\JsonResponse;

/**
 * @group Freelance Profile
 *
 * Consultation et mise à jour du profil du freelance connecté, ainsi que son tableau de bord.
 */
class FreelanceController extends Controller
{
    /**
     * Profil du freelance
     *
     * Retourne les informations du profil de l'utilisateur connecté.
     *
     * @authenticated
     *
     * @response 200 {
     *   "id": 1, "nom": "Doe", "prenom": "John", "email": "john@example.com",
     *   "telephone": "+212600000000", "taux_horaire": 50, "statut": "actif"
     * }
     */
    public function profile(): JsonResponse
    {
        $user = auth()->user();

        return response()->json(new UserResource($user));
    }

    /**
     * Mettre à jour le profil
     *
     * Met à jour les informations du profil de l'utilisateur connecté. Tous les champs sont optionnels.
     *
     * @authenticated
     *
     * @bodyParam nom string Le nom de famille. Example: Dupont
     * @bodyParam prenom string Le prénom. Example: Jean
     * @bodyParam email string L'adresse email. Example: jean@example.com
     * @bodyParam telephone string Le numéro de téléphone. Example: +212600000000
     * @bodyParam taux_horaire numeric Le taux horaire en DH. Example: 65
     *
     * @response 200 {
     *   "id": 1, "nom": "Dupont", "prenom": "Jean", "email": "jean@example.com",
     *   "telephone": "+212600000000", "taux_horaire": 65, "statut": "actif"
     * }
     */
    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $user = auth()->user();

        $user->update($request->validated());

        return response()->json(new UserResource($user));
    }

    /**
     * Tableau de bord du freelance
     *
     * Retourne les statistiques globales du freelance connecté.
     *
     * @authenticated
     *
     * @response 200 {
     *   "clients_count": 5, "projects_count": 12, "devis_count": 3
     * }
     */
    public function dashboard(): JsonResponse
    {
        $userId = auth()->id();

        $clientIds = Client::where('user_id', $userId)->pluck('id');

        return response()->json([
            'clients_count' => $clientIds->count(),
            'projects_count' => Project::whereIn('client_id', $clientIds)->count(),
            'devis_count' => Devis::whereIn('client_id', $clientIds)->count(),
        ]);
    }
}
