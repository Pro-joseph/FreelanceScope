<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectFeatureRequest;
use App\Http\Requests\UpdateProjectFeatureRequest;
use App\Http\Resources\ProjectFeatureResource;
use App\Models\Project;
use App\Models\ProjectFeature;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProjectFeatureController extends Controller
{
    public function index(Project $project): AnonymousResourceCollection
    {
        $this->authorize('view', $project);

        $features = $project->features()->latest()->get();

        return ProjectFeatureResource::collection($features);
    }

    public function store(StoreProjectFeatureRequest $request, Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $feature = $project->features()->create($request->validated());

        return response()->json(new ProjectFeatureResource($feature), 201);
    }

    public function show(ProjectFeature $feature): ProjectFeatureResource
    {
        $this->authorize('view', $feature);

        return new ProjectFeatureResource($feature);
    }

    public function update(UpdateProjectFeatureRequest $request, ProjectFeature $feature): ProjectFeatureResource
    {
        $this->authorize('update', $feature);

        $feature->update($request->validated());

        return new ProjectFeatureResource($feature);
    }

    public function destroy(ProjectFeature $feature): JsonResponse
    {
        $this->authorize('delete', $feature);

        $feature->delete();

        return response()->json(null, 204);
    }
}
