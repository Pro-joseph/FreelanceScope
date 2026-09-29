<?php

return [

    /*
    |--------------------------------------------------------------------------
    | AI Estimation Model
    |--------------------------------------------------------------------------
    |
    | Modèle utilisé par AIEstimationService pour générer les fonctionnalités
    | d'un projet. Par défaut on cible Groq (voir OPENAI_BASE_URL), dont les
    | modèles de chat sont openai/gpt-oss-120b, openai/gpt-oss-20b et
    | qwen/qwen3.8-27b.
    |
    */

    'model' => env('AI_MODEL', 'openai/gpt-oss-120b'),

    /*
    |--------------------------------------------------------------------------
    | AI Reasoning Effort
    |--------------------------------------------------------------------------
    |
    | Les modèles gpt-oss sont des modèles de raisonnement. Groq les accepte
    | avec un niveau d'effort (low, medium, high). "low" réduit fortement
    | le nombre de tokens consommés tout en conservant un JSON exploitable.
    |
    */

    'reasoning_effort' => env('AI_REASONING_EFFORT', 'low'),

];
