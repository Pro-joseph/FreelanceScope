<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFeatureRequest;
use App\Http\Requests\UpdateEstimateRequest;
use App\Http\Requests\UpdateFeatureRequest;
use App\Http\Resources\EstimateResource;
use App\Http\Resources\ProjectFeatureResource;
use App\Models\Estimate;
use App\Models\Project;
use App\Models\ProjectFeature;
use Illuminate\Http\JsonResponse;

/**
 * @group Project Features
 *
 * Gestion des fonctionnalités d'un projet. Les routes sont shallow : lister/créer sont sur `/projects/{project}/features`,
 * les actions individuelles sur `/features/{feature}`.
 * @group Estimates
 *
 * Consultation et mise à jour des estimations de prix pour chaque fonctionnalité.
 * Les estimations sont généralement générées automatiquement par l'IA, mais peuvent être modifiées manuellement.
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
    public function index(Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        return response()->json([
            'data' => ProjectFeatureResource::collection($project->features()->latest()->get()),
        ]);
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
    public function store(StoreFeatureRequest $request, Project $project): JsonResponse
    {
        $this->authorize('update', $project);

        $feature = $project->features()->create($request->validated());

        return response()->json(['data' => new ProjectFeatureResource($feature)], 201);
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
    public function show(ProjectFeature $feature): JsonResponse
    {
        $this->authorize('view', $feature);

        return response()->json(['data' => new ProjectFeatureResource($feature)]);
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
    public function update(UpdateFeatureRequest $request, Project $project, ProjectFeature $feature): JsonResponse
    {
        $this->authorize('update', $feature);

        $feature->update($request->validated());

        return response()->json(['data' => new ProjectFeatureResource($feature)]);
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

    /**
     * Afficher l'estimation d'une fonctionnalité
     *
     * Retourne l'estimation associée à une fonctionnalité. Si aucune estimation n'existe, retourne une erreur 404 explicite.
     *
     * @authenticated
     *
     * @urlParam feature integer required L'ID de la fonctionnalité. Example: 1
     *
     * @response 200 {
     *   "id": 1, "feature_id": 1, "hourly_rate": 50,
     *   "total_hours": 16, "total_amount": 800, "created_at": "..."
     * }
     * @response 404 {
     *   "message": "Aucune estimation générée pour cette fonctionnalité.",
     *   "feature_id": 1
     * }
     */
    public function showEstimate(ProjectFeature $feature): Estimate|JsonResponse
    {
        $estimate = $feature->estimate;

        if (is_null($estimate)) {
            return response()->json([
                'message' => 'Aucune estimation générée pour cette fonctionnalité.',
                'feature_id' => $feature->id,
            ], 404);
        }

        $this->authorize('view', $estimate);

        return response()->json(['data' => new EstimateResource($estimate)]);
    }

    /**
     * Modifier une estimation
     *
     * Met à jour le taux horaire et/ou le nombre d'heures. Le montant total est recalculé automatiquement.
     *
     * @authenticated
     *
     * @urlParam estimate integer required L'ID de l'estimation. Example: 1
     *
     * @bodyParam hourly_rate numeric Le taux horaire en DH. Example: 65
     * @bodyParam total_hours numeric Le nombre d'heures estimé. Example: 20
     *
     * @response 200 {
     *   "id": 1, "hourly_rate": 65, "total_hours": 20, "total_amount": 1300, ...
     * }
     */
    public function updateEstimate(UpdateEstimateRequest $request, Estimate $estimate): JsonResponse
    {
        $this->authorize('update', $estimate);

        $validated = $request->validated();

        $estimate->update([
            ...$validated,
            'total_amount' => ($validated['hourly_rate'] ?? $estimate->hourly_rate)
                * ($validated['total_hours'] ?? $estimate->total_hours),
        ]);

        return response()->json(['data' => new EstimateResource($estimate)]);
    }
}
