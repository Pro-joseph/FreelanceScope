<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateEstimateRequest;
use App\Http\Resources\EstimateResource;
use App\Models\Estimate;
use App\Models\ProjectFeature;

class EstimateController extends Controller
{
    public function show(ProjectFeature $feature): EstimateResource
    {
        $estimate = $feature->estimate;

        abort_if(is_null($estimate), 404);

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
