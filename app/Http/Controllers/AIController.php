<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateEstimationJob;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
     *
     * @bodyParam prompt string required La description détaillée du projet. Example: Je veux un site e-commerce complet avec catalogue produits, panier, paiement Stripe, dashboard admin, et espace client.
     *
     * @response 202 {
     *   "message": "Estimation en cours de génération."
     * }
     */
    public function __invoke(Request $request, Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $validated = $request->validate([
            'prompt' => ['required', 'string', 'max:5000'],
        ]);

        GenerateEstimationJob::dispatch(
            $project,
            auth()->id(),
            $validated['prompt'],
        );

        return response()->json([
            'message' => 'Estimation en cours de génération.',
        ], 202);
    }

    /**
     * Historique des analyses IA
     *
     * Retourne toutes les analyses IA générées pour un projet donné.
     *
     * @authenticated
     *
     * @urlParam project integer required L'ID du projet. Example: 1
     *
     * @response 200 [
     *   {
     *     "id": 1,
     *     "project_id": 1,
     *     "prompt": "Je veux un site e-commerce...",
     *     "response": "...",
     *     "model": "llama-3.3-70b-versatile",
     *     "tokens_used": 1500,
     *     "created_at": "2026-07-21T12:00:00.000000Z"
     *   }
     * ]
     */
    public function analyses(Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $analyses = $project->aiAnalyses()->latest()->get();

        return response()->json($analyses);
    }
}
