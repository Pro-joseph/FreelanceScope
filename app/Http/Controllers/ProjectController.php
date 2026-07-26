<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\Rules;

/**
 * @group Projects
 *
 * Gestion des projets liés aux clients d'un freelance.
 */
class ProjectController extends Controller
{
    /**
     * Liste des projets
     *
     * Retourne la liste paginée des projets de l'utilisateur connecté.
     *
     * @authenticated
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1, "client_id": 1, "name": "Site e-commerce",
     *       "description": "Site de vente en ligne...", "status": "draft",
     *       "features_count": 5, "created_at": "2026-07-21T09:00:00.000000Z"
     *     }
     *   ],
     *   "meta": { "current_page": 1, "per_page": 15, "total": 1 }
     * }
     */
    public function index(): LengthAwarePaginator
    {
        $clientIds = Client::where('user_id', auth()->id())->pluck('id');

        return Project::whereIn('client_id', $clientIds)
            ->with('client')
            ->withCount('features')
            ->latest()
            ->paginate(15);
    }

    /**
     * Créer un projet
     *
     * Ajoute un nouveau projet pour un client existant.
     *
     * @authenticated
     *
     * @bodyParam client_id integer required L'ID du client (appartenant à l'utilisateur). Example: 1
     * @bodyParam name string required Le nom du projet. Example: Site e-commerce
     * @bodyParam description string La description du projet. Example: Site de vente en ligne avec catalogue et paiement
     *
     * @response 201 {
     *   "id": 1, "client_id": 1, "name": "Site e-commerce", "status": "draft", ...
     * }
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $project = Project::create($validated);

        return response()->json($project, 201);
    }

    /**
     * Afficher un projet
     *
     * @authenticated
     *
     * @urlParam project integer required L'ID du projet. Example: 1
     *
     * @response 200 {
     *   "id": 1, "client_id": 1, "name": "Site e-commerce", "status": "draft", "features_count": 5, ...
     * }
     */
    public function show(Project $project): Project
    {
        $this->authorize('view', $project);

        $project->load(['client', 'features.estimate']);
        $project->loadCount('features');

        return $project;
    }

    /**
     * Modifier un projet
     *
     * @authenticated
     *
     * @urlParam project integer required L'ID du projet. Example: 1
     *
     * @bodyParam name string Le nom du projet. Example: Site e-commerce v2
     * @bodyParam description string La description du projet.
     * @bodyParam status string Le statut. Possibilités : `draft`, `in_progress`, `completed`, `cancelled`. Example: in_progress
     *
     * @response 200 {
     *   "id": 1, "name": "Site e-commerce v2", "status": "in_progress", ...
     * }
     */
    public function update(Request $request, Project $project): Project
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['sometimes', 'string', 'in:draft,in_progress,completed,cancelled'],
        ]);

        $project->update($validated);

        return $project;
    }

    /**
     * Supprimer un projet
     *
     * @authenticated
     *
     * @urlParam project integer required L'ID du projet. Example: 1
     *
     * @response 204
     */
    public function destroy(Project $project): JsonResponse
    {
        $this->authorize('delete', $project);

        $project->delete();

        return response()->json(null, 204);
    }
}
