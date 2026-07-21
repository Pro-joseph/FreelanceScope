<?php

namespace App\Http\Controllers;

use App\Http\Requests\GenerateEstimationRequest;
use App\Jobs\GenerateEstimationJob;
use App\Models\Project;
use Illuminate\Http\JsonResponse;

class AIController extends Controller
{
    public function __invoke(GenerateEstimationRequest $request, Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        GenerateEstimationJob::dispatch(
            $project,
            auth()->id(),
            $request->input('prompt'),
        );

        return response()->json([
            'message' => 'Estimation en cours de génération.',
        ], 202);
    }
}
