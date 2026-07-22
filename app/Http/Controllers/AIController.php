<?php

namespace App\Http\Controllers;

use App\Http\Requests\GenerateEstimationRequest;
use App\Jobs\GenerateEstimationJob;
use App\Models\Project;
use Illuminate\Http\JsonResponse;

/**
 * @group AI Estimation
 *
 * Génération automatique d'estimations via intelligence artificielle (Groq API).
 * Le processus est asynchrone : un job est dispatché et les résultats sont disponibles
 * via les endpoints Features et Estimates après exécution du worker.
 */
class AIController extends Controller
{
    /**
     * Générer une estimation par IA
     *
     * Analyse le besoin client via l'IA et génère automatiquement les fonctionnalités et leurs estimations.
     * Nécessite que le worker queue soit actif : `php artisan queue:work`.
     *
     * @authenticated
     *
     * @urlParam project integer required L'ID du projet. Example: 1
     * @bodyParam prompt string required La description détaillée du projet. Example: Je veux un site e-commerce complet avec catalogue produits, panier, paiement Stripe, dashboard admin, et espace client.
     *
     * @response 202 {
     *   "message": "Estimation en cours de génération."
     * }
     */
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
