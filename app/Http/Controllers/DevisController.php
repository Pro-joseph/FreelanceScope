<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDevisRequest;
use App\Http\Requests\UpdateDevisRequest;
use App\Http\Resources\DevisResource;
use App\Models\Devis;
use App\Services\DevisService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DevisController extends Controller
{
    public function __construct(
        private readonly DevisService $devisService,
    ) {}

    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Devis::class);

        $devis = $this->devisService->listForUser(auth()->id());

        return DevisResource::collection($devis);
    }

    public function store(StoreDevisRequest $request): JsonResponse
    {
        $this->authorize('create', Devis::class);

        $devis = $this->devisService->generate(
            $request->integer('client_id'),
            $request->integer('project_id'),
            $request->input('conditions'),
        );

        return response()->json(new DevisResource($devis), 201);
    }

    public function show(Devis $devis): DevisResource
    {
        $this->authorize('view', $devis);

        $devis->load(['client', 'project.features.estimate']);

        return new DevisResource($devis);
    }

    public function update(UpdateDevisRequest $request, Devis $devis): DevisResource
    {
        $this->authorize('update', $devis);

        $devis = $this->devisService->update($devis, $request->validated());

        return new DevisResource($devis);
    }

    public function destroy(Devis $devis): JsonResponse
    {
        $this->authorize('delete', $devis);

        $this->devisService->delete($devis);

        return response()->json(null, 204);
    }

    public function download(Devis $devis)
    {
        $this->authorize('view', $devis);

        $path = $this->devisService->generatePdf($devis);

        return response()->download(public_path($path));
    }
}
