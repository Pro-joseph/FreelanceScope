<?php

namespace App\Services;

use App\Http\Resources\AiEstimationResource;
use Illuminate\Http\Request as HttpRequest;
use OpenAI\Laravel\Facades\OpenAI;

class AIEstimationService
{
    private const MODEL = 'llama-3.3-70b-versatile';

    public function generate(string $prompt): array
    {
        $shapeJson = json_encode(
            AiEstimationResource::shape(),
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );

        $systemPrompt = <<<PROMPT
Tu es un expert en estimation de projets web. Analyse le besoin client et retourne UNIQUEMENT un JSON valide (sans markdown, sans ```) avec cette structure exacte :

{$shapeJson}

Règles :
- Propose entre 3 et 12 fonctionnalités
- complexité : simple (2-8h), moyen (8-24h), complexe (24-80h)
- Sois réaliste sur les heures
- Détecte les lacunes dans le besoin
PROMPT;

        $response = OpenAI::chat()->create([
            'model' => self::MODEL,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $prompt],
            ],
            'temperature' => 0.3,
            'max_tokens' => 4000,
        ]);

        $content = $response->choices[0]->message->content;
        $tokensUsed = $response->usage?->totalTokens ?? 0;

        return [
            'parsed' => $this->parseResponse($content),
            'raw' => $content,
            'model' => self::MODEL,
            'tokens_used' => $tokensUsed,
        ];
    }

    private function parseResponse(string $content): array
    {
        $cleaned = trim($content);

        if (str_starts_with($cleaned, '```')) {
            $cleaned = preg_replace('/^```(?:json)?\s*\n?/', '', $cleaned);
            $cleaned = preg_replace('/\n?```\s*$/', '', $cleaned);
        }

        $decoded = json_decode($cleaned, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Failed to parse AI response: '.json_last_error_msg());
        }

        return (new AiEstimationResource($decoded))->toArray(new HttpRequest);
    }
}
