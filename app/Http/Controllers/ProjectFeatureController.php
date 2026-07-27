<?php

namespace App\Http\Controllers;

use App\Models\Estimate;
use App\Models\Project;
use App\Models\ProjectFeature;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
            'data' => $project->features()->latest()->get()->toArray(),
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
    public function store(Request $request, Project $project): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'complexity' => ['nullable', 'string', 'in:simple,moyen,complexe'],
        ]);

        $feature = $project->features()->create($validated);

        return response()->json(['data' => $feature], 201);
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

        return response()->json(['data' => $feature]);
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
    public function update(Request $request, Project $project, ProjectFeature $feature): JsonResponse
    {
        $this->authorize('update', $feature);

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'complexity' => ['nullable', 'string', 'in:simple,moyen,complexe'],
        ]);

        $feature->update($validated);

        return response()->json(['data' => $feature]);
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

        return response()->json(['data' => $estimate]);
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
    public function updateEstimate(Request $request, Estimate $estimate): Estimate
    {
        $this->authorize('update', $estimate);

        $validated = $request->validate([
            'hourly_rate' => ['sometimes', 'numeric', 'min:0'],
            'total_hours' => ['sometimes', 'numeric', 'min:0'],
        ]);

        $estimate->update([
            ...$validated,
            'total_amount' => ($validated['hourly_rate'] ?? $estimate->hourly_rate)
                * ($validated['total_hours'] ?? $estimate->total_hours),
        ]);

        return $estimate;
    }
}
