<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AiEstimationResource extends JsonResource
{
    /**
     * Squelette canonique de la réponse d'estimation IA.
     *
     * Utilisé pour générer le template JSON injecté dans le prompt (contrat unique de forme).
     */
    public static function shape(): array
    {
        return [
            'features' => [
                [
                    'name' => 'Nom de la fonctionnalité',
                    'description' => 'Description concise',
                    'complexity' => 'simple|moyen|complexe',
                    'total_hours' => 8,
                    'risks' => ['risque éventuel'],
                ],
            ],
            'missing_info' => ['information manquante'],
            'scope_creep_risks' => ['risque de dérive'],
        ];
    }

    /**
     * Normalise une réponse IA parsée pour garantir la forme canonique.
     */
    public function toArray(Request $request): array
    {
        $parsed = $this->resource;

        return [
            'features' => array_map(fn ($feature) => [
                'name' => $feature['name'] ?? null,
                'description' => $feature['description'] ?? null,
                'complexity' => $feature['complexity'] ?? null,
                'total_hours' => $feature['total_hours'] ?? null,
                'risks' => $feature['risks'] ?? [],
            ], $parsed['features'] ?? []),
            'missing_info' => $parsed['missing_info'] ?? [],
            'scope_creep_risks' => $parsed['scope_creep_risks'] ?? [],
        ];
    }
}
