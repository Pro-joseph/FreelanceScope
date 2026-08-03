<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Enums\UserStatut;
use App\Http\Requests\StoreFreelanceRequest;
use App\Http\Requests\UpdateFreelanceRequest;
use App\Http\Resources\DevisResource;
use App\Models\Client;
use App\Models\Devis;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
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
     * Liste des clients
     *
     * Retourne tous les clients de la plateforme.
     *
     * @authenticated
     *
     * @response 200 {
     *   "data": [
     *     { "id": 1, "company_name": "Acme Corp", "email": "contact@acme.com", "phone": "+212600000000", "projects_count": 3, "owner": { "nom": "Jane", "prenom": "Doe" } }
     *   ],
     *   "meta": { "current_page": 1, "per_page": 15, "total": 1 }
     * }
     */
    public function listClients(): JsonResponse
    {
        $clients = Client::with('user:id,nom,prenom')
            ->withCount('projects')
            ->latest()
            ->paginate(15);

        return response()->json($clients);
    }

    /**
     * Liste des devis
     *
     * Retourne tous les devis de la plateforme.
     *
     * @authenticated
     *
     * @response 200 {
     *   "data": [
     *     { "id": 1, "client": { "company_name": "Acme Corp" }, "project": { "name": "Site e-commerce" }, "total_amount": 3200, "status": "draft", "created_at": "..." }
     *   ],
     *   "meta": { "current_page": 1, "per_page": 15, "total": 1 }
     * }
     */
    public function listDevis(): AnonymousResourceCollection
    {
        $devis = Devis::with(['client', 'project'])
            ->latest()
            ->paginate(15);

        return DevisResource::collection($devis);
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
        $validated = $request->validated();

        $user = User::create([
            'nom' => $validated['nom'],
            'prenom' => $validated['prenom'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'telephone' => $validated['telephone'] ?? null,
            'taux_horaire' => $validated['taux_horaire'] ?? null,
            'role' => UserRole::Freelance,
        ]);

        return response()->json($user, 201);
    }

    /**
     * Afficher un freelance
     *
     * Retourne les détails d'un freelance spécifique.
     *
     * @authenticated
     *
     * @urlParam user integer required L'ID du freelance. Example: 2
     *
     * @response 200 { "id": 2, "nom": "Jane", "prenom": "Doe", "email": "jane@example.com", "telephone": "+212600000001", "statut": "actif", "taux_horaire": 75 }
     */
    public function showFreelance(User $user): JsonResponse
    {
        $this->authorize('view', $user);

        return response()->json($user->only(
            'id', 'nom', 'prenom', 'email', 'telephone', 'statut', 'taux_horaire'
        ));
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
    public function updateFreelance(UpdateFreelanceRequest $request, User $user): JsonResponse
    {
        $this->authorize('update', $user);

        $user->update($request->validated());

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

    /**
     * Autoriser l'accès à Telescope
     *
     * Déverrouille Telescope pour la session courante. Réservé aux admins
     * (le middleware `admin` vérifie le rôle avant d'atteindre cette méthode).
     *
     * @authenticated
     *
     * @response 200 { "message": "Accès autorisé." }
     */
    public function authorizeTelescope(): JsonResponse
    {
        session(['telescope_admin' => true]);

        return response()->json(['message' => 'Accès autorisé.']);
    }
}
