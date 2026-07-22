<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateEstimateRequest;
use App\Http\Resources\EstimateResource;
use App\Models\Estimate;
use App\Models\ProjectFeature;
use Illuminate\Http\JsonResponse;

class EstimateController extends Controller
{
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
