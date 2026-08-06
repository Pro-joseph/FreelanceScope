<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Models\Client;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

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
    public function index(): AnonymousResourceCollection
    {
        $clientIds = Client::where('user_id', auth()->id())->pluck('id');

        return ProjectResource::collection(
            Project::whereIn('client_id', $clientIds)
                ->with('client')
                ->withCount('features')
                ->latest()
                ->paginate(15)
        );
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
    public function store(StoreProjectRequest $request): JsonResponse
    {
        $project = Project::create($request->validated());

        return response()->json(['data' => new ProjectResource($project)], 201);
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
    public function show(Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $project->load(['client', 'features.estimate']);
        $project->loadCount('features');

        return response()->json(['data' => new ProjectResource($project)]);
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
    public function update(UpdateProjectRequest $request, Project $project): JsonResponse
    {
        $this->authorize('update', $project);

        $project->update($request->validated());

        return response()->json(['data' => new ProjectResource($project)]);
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
