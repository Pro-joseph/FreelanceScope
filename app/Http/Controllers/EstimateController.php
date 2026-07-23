<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateEstimateRequest;
use App\Http\Resources\EstimateResource;
use App\Models\Estimate;
use App\Models\ProjectFeature;
use Illuminate\Http\JsonResponse;

/**
 * @group Estimates
 *
 * Consultation et mise à jour des estimations de prix pour chaque fonctionnalité.
 * Les estimations sont généralement générées automatiquement par l'IA, mais peuvent être modifiées manuellement.
 */
class EstimateController extends Controller
{
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
     *   "data": {
     *     "id": 1, "feature_id": 1, "hourly_rate": 50,
     *     "total_hours": 16, "total_amount": 800, "created_at": "..."
     *   }
     * }
     * @response 404 {
     *   "message": "Aucune estimation générée pour cette fonctionnalité.",
     *   "feature_id": 1
     * }
     */
    public function show(ProjectFeature $feature): EstimateResource|JsonResponse
    {
        $estimate = $feature->estimate;

        if (is_null($estimate)) {
            return response()->json([
                'message' => 'Aucune estimation générée pour cette fonctionnalité.',
                'feature_id' => $feature->id,
            ], 404);
        }

        $this->authorize('view', $estimate);

        return new EstimateResource($estimate);
    }

    /**
     * Modifier une estimation
     *
     * Met à jour le taux horaire et/ou le nombre d'heures. Le montant total est recalculé automatiquement.
     *
     * @authenticated
     *
     * @urlParam estimate integer required L'ID de l'estimation. Example: 1
     * @bodyParam hourly_rate numeric Le taux horaire en DH. Example: 65
     * @bodyParam total_hours numeric Le nombre d'heures estimé. Example: 20
     *
     * @response 200 {
     *   "data": { "id": 1, "hourly_rate": 65, "total_hours": 20, "total_amount": 1300, ... }
     * }
     */
    public function update(UpdateEstimateRequest $request, Estimate $estimate): EstimateResource
    {
        $this->authorize('update', $estimate);

        $data = $request->validated();

        $estimate->update([
            ...$data,
            'total_amount' => ($data['hourly_rate'] ?? $estimate->hourly_rate)
                * ($data['total_hours'] ?? $estimate->total_hours),
        ]);

        return new EstimateResource($estimate);
    }
}
