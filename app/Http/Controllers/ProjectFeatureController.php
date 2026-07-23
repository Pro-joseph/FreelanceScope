<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectFeatureRequest;
use App\Http\Requests\UpdateProjectFeatureRequest;
use App\Http\Resources\ProjectFeatureResource;
use App\Models\Project;
use App\Models\ProjectFeature;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @group Project Features
 *
 * Gestion des fonctionnalités d'un projet. Les routes sont shallow : lister/créer sont sur `/projects/{project}/features`,
 * les actions individuelles sur `/features/{feature}`.
 */
class ProjectFeatureController extends Controller
{
    /**
     * Liste des fonctionnalités d'un projet
     *
     * @authenticated
     *
     * @urlParam project integer required L'ID du projet. Example: 1
     *
     * @response 200 [
     *   { "id": 1, "project_id": 1, "name": "Page d'accueil", "description": "Page d'accueil avec présentation", "complexity": "moyen", "created_at": "..." }
     * ]
     */
    public function index(Project $project): AnonymousResourceCollection
    {
        $this->authorize('view', $project);

        $features = $project->features()->latest()->get();

        return ProjectFeatureResource::collection($features);
    }

    /**
     * Ajouter une fonctionnalité
     *
     * @authenticated
     *
     * @urlParam project integer required L'ID du projet. Example: 1
     *
     * @bodyParam name string required Le nom de la fonctionnalité. Example: Page d'accueil
     * @bodyParam description string La description. Example: Page d'accueil avec présentation
     * @bodyParam complexity string La complexité. Possibilités : `simple`, `moyen`, `complexe`. Example: moyen
     *
     * @response 201 {
     *   "id": 1, "project_id": 1, "name": "Page d'accueil",
     *   "description": "Page d'accueil avec présentation", "complexity": "moyen", "created_at": "..."
     * }
     */
    public function store(StoreProjectFeatureRequest $request, Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $feature = $project->features()->create($request->validated());

        return response()->json(new ProjectFeatureResource($feature), 201);
    }

    /**
     * Afficher une fonctionnalité
     *
     * @authenticated
     *
     * @urlParam feature integer required L'ID de la fonctionnalité. Example: 1
     *
     * @response 200 {
     *   "id": 1, "project_id": 1, "name": "Page d'accueil", "description": "...", "complexity": "moyen", "created_at": "..."
     * }
     */
    public function show(ProjectFeature $feature): ProjectFeatureResource
    {
        $this->authorize('view', $feature);

        return new ProjectFeatureResource($feature);
    }

    /**
     * Modifier une fonctionnalité
     *
     * @authenticated
     *
     * @urlParam feature integer required L'ID de la fonctionnalité. Example: 1
     *
     * @bodyParam name string Le nom de la fonctionnalité. Example: Page d'accueil v2
     * @bodyParam description string La description.
     * @bodyParam complexity string La complexité. Possibilités : `simple`, `moyen`, `complexe`. Example: simple
     *
     * @response 200 {
     *   "id": 1, "name": "Page d'accueil v2", "complexity": "simple", ...
     * }
     */
    public function update(UpdateProjectFeatureRequest $request, ProjectFeature $feature): ProjectFeatureResource
    {
        $this->authorize('update', $feature);

        $feature->update($request->validated());

        return new ProjectFeatureResource($feature);
    }

    /**
     * Supprimer une fonctionnalité
     *
     * @authenticated
     *
     * @urlParam feature integer required L'ID de la fonctionnalité. Example: 1
     *
     * @response 204
     */
    public function destroy(ProjectFeature $feature): JsonResponse
    {
        $this->authorize('delete', $feature);

        $feature->delete();

        return response()->json(null, 204);
    }
}
