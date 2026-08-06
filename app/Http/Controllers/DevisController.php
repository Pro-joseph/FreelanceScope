<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDevisRequest;
use App\Http\Requests\UpdateDevisRequest;
use App\Http\Resources\DevisResource;
use App\Models\Devis;
use App\Models\Project;
use App\Services\DevisService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @group Devis
 *
 * Génération et gestion des devis (documents PDF) à partir des estimations d'un projet.
 * Les devis sont basés sur les fonctionnalités d'un projet et leurs estimations de coûts.
 */
class DevisController extends Controller
{
    public function __construct(
        private readonly DevisService $devisService,
    ) {}

    /**
     * Liste des devis
     *
     * Retourne la liste paginée des devis de l'utilisateur connecté.
     *
     * @authenticated
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "client": { "company_name": "Acme Corp", "email": "contact@acme.com", "phone": "+212600000000" },
     *       "project": { "name": "Site e-commerce", "description": "..." },
     *       "features": null, "total_amount": 3200, "conditions": null,
     *       "status": "draft", "pdf_path": null, "created_at": "..."
     *     }
     *   ],
     *   "meta": { "current_page": 1, "per_page": 15, "total": 1 }
     * }
     */
    public function index(Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $devis = $this->devisService->listForProject($project);

        return response()->json(['data' => DevisResource::collection($devis)]);
    }

    /**
     * Tous les devis de l'utilisateur
     *
     * Retourne la liste paginée de tous les devis de l'utilisateur connecté, tous projets confondus.
     *
     * @authenticated
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "client": { "company_name": "Acme Corp", "email": "contact@acme.com", "phone": "+212600000000" },
     *       "project": { "name": "Site e-commerce", "description": "..." },
     *       "features": null, "total_amount": 3200, "conditions": null,
     *       "status": "draft", "pdf_path": null, "created_at": "..."
     *     }
     *   ],
     *   "meta": { "current_page": 1, "per_page": 15, "total": 1 }
     * }
     */
    public function listAll(): AnonymousResourceCollection
    {
        $devis = Devis::with(['client', 'project'])
            ->whereHas('client', fn ($query) => $query->where('user_id', auth()->id()))
            ->latest()
            ->paginate(15);

        return DevisResource::collection($devis);
    }

    /**
     * Créer un devis
     *
     * Génère un nouveau devis à partir d'un client et d'un projet existants.
     * Le montant total est calculé automatiquement à partir des estimations des fonctionnalités.
     *
     * @authenticated
     *
     * @bodyParam client_id integer required L'ID du client (appartenant à l'utilisateur). Example: 1
     * @bodyParam project_id integer required L'ID du projet. Example: 1
     * @bodyParam conditions string Les conditions de paiement. Example: Paiement : 50% à la commande, 50% à la livraison
     *
     * @response 201 {
     *   "data": { "id": 1, "total_amount": 3200, "status": "draft", ... }
     * }
     */
    public function store(StoreDevisRequest $request, Project $project): JsonResponse
    {
        $this->authorize('update', $project);

        $devis = $this->devisService->generate(
            $project->client_id,
            $project->id,
            $request->validated()['conditions'] ?? null,
        );

        return response()->json(new DevisResource($devis), 201);
    }

    /**
     * Afficher un devis
     *
     * Retourne les détails complets d'un devis, y compris les fonctionnalités et leurs estimations.
     *
     * @authenticated
     *
     * @urlParam devis integer required L'ID du devis. Example: 1
     *
     * @response 200 {
     *   "data": {
     *     "id": 1,
     *     "client": { "company_name": "Acme Corp", "email": "...", "phone": "..." },
     *     "project": { "name": "Site e-commerce", "description": "..." },
     *     "features": [
     *       { "name": "Page d'accueil", "complexity": "moyen", "hourly_rate": 50, "total_hours": 16, "total_amount": 800 }
     *     ],
     *     "total_amount": 3200, "conditions": "Paiement : 50% à la commande...",
     *     "status": "draft", "pdf_path": null, "created_at": "..."
     *   }
     * }
     */
    public function show(Project $project, Devis $devis): DevisResource
    {
        $this->authorize('view', $devis);

        $devis->load(['client', 'project.features.estimate']);

        return new DevisResource($devis);
    }

    /**
     * Modifier un devis
     *
     * Met à jour le statut ou les conditions d'un devis.
     *
     * @authenticated
     *
     * @urlParam devis integer required L'ID du devis. Example: 1
     *
     * @bodyParam status string Le statut. Possibilités : `draft`, `sent`, `accepted`, `refused`. Example: sent
     * @bodyParam conditions string Les conditions de paiement. Example: Paiement comptant
     *
     * @response 200 {
     *   "data": { "id": 1, "status": "sent", "conditions": "Paiement comptant", ... }
     * }
     */
    public function update(UpdateDevisRequest $request, Project $project, Devis $devis): DevisResource
    {
        $this->authorize('update', $devis);

        $devis = $this->devisService->update($devis, $request->validated());

        return new DevisResource($devis);
    }

    /**
     * Supprimer un devis
     *
     * Supprime un devis et son fichier PDF associé (s'il existe).
     *
     * @authenticated
     *
     * @urlParam devis integer required L'ID du devis. Example: 1
     *
     * @response 204
     */
    public function destroy(Project $project, Devis $devis): JsonResponse
    {
        $this->authorize('delete', $devis);

        $devis->delete();

        return response()->json(null, 204);
    }

    /**
     * Télécharger le PDF d'un devis
     *
     * Génère (si nécessaire) et télécharge le fichier PDF du devis.
     * Le PDF est mis en cache et regénéré automatiquement si le devis a été modifié.
     *
     * @authenticated
     *
     * @urlParam devis integer required L'ID du devis. Example: 1
     *
     * @response 200 (binary PDF stream)
     */
    public function download(Project $project, Devis $devis)
    {
        $this->authorize('view', $devis);

        if ($this->devisService->needsRegeneration($devis)) {
            $this->devisService->generatePdf($devis);
        }

        $path = $this->devisService->getPdfPath($devis);

        abort_if(is_null($path), 404);

        return response()->file($path, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="devis_'.$devis->id.'.pdf"',
        ]);
    }
}
