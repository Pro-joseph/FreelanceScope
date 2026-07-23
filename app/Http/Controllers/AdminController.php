<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Enums\UserStatut;
use App\Http\Requests\StoreFreelanceRequest;
use App\Models\Client;
use App\Models\Devis;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * @group Admin
 *
 * Gestion administrative des freelances. Réservé aux utilisateurs avec le rôle `admin`.
 * Tous les endpoints nécessitent le middleware `admin` en plus de `auth:sanctum`.
 */
class AdminController extends Controller
{
    /**
     * Tableau de bord admin
     *
     * Retourne les statistiques globales de la plateforme.
     *
     * @authenticated
     *
     * @response 200 {
     *   "freelances_count": 10, "clients_count": 25, "projects_count": 40, "devis_count": 8
     * }
     */
    public function dashboard(): JsonResponse
    {
        return response()->json([
            'freelances_count' => User::where('role', UserRole::Freelance)->count(),
            'clients_count' => Client::count(),
            'projects_count' => Project::count(),
            'devis_count' => Devis::count(),
        ]);
    }

    /**
     * Liste des freelances
     *
     * Retourne la liste de tous les utilisateurs ayant le rôle `freelance`.
     *
     * @authenticated
     *
     * @response 200 [
     *   { "id": 2, "nom": "Jane", "prenom": "Doe", "email": "jane@example.com", "telephone": "+212600000001", "statut": "actif", "taux_horaire": 75 }
     * ]
     */
    public function freelances(): JsonResponse
    {
        $freelances = User::where('role', UserRole::Freelance)
            ->get(['id', 'nom', 'prenom', 'email', 'telephone', 'statut', 'taux_horaire']);

        return response()->json($freelances);
    }

    /**
     * Créer un freelance
     *
     * Crée un nouvel utilisateur avec le rôle `freelance`.
     *
     * @authenticated
     *
     * @bodyParam nom string required Le nom de famille. Example: Doe
     * @bodyParam prenom string required Le prénom. Example: Jane
     * @bodyParam email string required L'adresse email. Example: jane@example.com
     * @bodyParam password string required Le mot de passe. Example: password123
     * @bodyParam telephone string Le numéro de téléphone. Example: +212600000001
     * @bodyParam taux_horaire numeric Le taux horaire en DH. Example: 75
     *
     * @response 201 { "id": 3, "nom": "Doe", "prenom": "Jane", "email": "jane@example.com", "role": "freelance", ... }
     */
    public function storeFreelance(StoreFreelanceRequest $request): JsonResponse
    {
        $user = User::create([
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'email' => $request->email,
            'password' => Hash::make($request->string('password')),
            'telephone' => $request->telephone,
            'taux_horaire' => $request->taux_horaire,
            'role' => UserRole::Freelance,
        ]);

        return response()->json($user, 201);
    }

    /**
     * Modifier un freelance
     *
     * Met à jour les informations d'un freelance existant.
     *
     * @authenticated
     *
     * @urlParam user integer required L'ID du freelance. Example: 2
     *
     * @bodyParam nom string Le nom de famille. Example: Dupont
     * @bodyParam prenom string Le prénom. Example: Jeanne
     * @bodyParam email string L'adresse email. Example: jeanne@example.com
     * @bodyParam telephone string Le numéro de téléphone. Example: +212600000002
     * @bodyParam taux_horaire numeric Le taux horaire en DH. Example: 80
     *
     * @response 200 { "id": 2, "nom": "Dupont", ... }
     */
    public function updateFreelance(Request $request, User $user): JsonResponse
    {
        $this->authorize('update', $user);

        $validated = $request->validate([
            'nom' => ['sometimes', 'string', 'max:255'],
            'prenom' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'telephone' => ['nullable', 'string', 'max:20'],
            'taux_horaire' => ['nullable', 'numeric', 'min:0'],
        ]);

        $user->update($validated);

        return response()->json($user);
    }

    /**
     * Activer / désactiver un freelance
     *
     * Bascule le statut d'un freelance entre `actif` et `inactif`.
     *
     * @authenticated
     *
     * @urlParam user integer required L'ID du freelance. Example: 2
     *
     * @response 200 { "id": 2, "nom": "Jane", "prenom": "Doe", "statut": "inactif", ... }
     */
    public function toggleStatut(User $user): JsonResponse
    {
        $this->authorize('toggleStatut', $user);

        $user->update([
            'statut' => $user->statut === UserStatut::Actif ? UserStatut::Inactif->value : UserStatut::Actif->value,
        ]);

        return response()->json($user);
    }

    /**
     * Supprimer un freelance
     *
     * Supprime définitivement un compte freelance.
     *
     * @authenticated
     *
     * @urlParam user integer required L'ID du freelance. Example: 2
     *
     * @response 204
     */
    public function destroyFreelance(User $user): JsonResponse
    {
        $this->authorize('delete', $user);

        $user->delete();

        return response()->json(null, 204);
    }
}
