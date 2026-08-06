<?php

use App\Http\Resources\AiEstimationResource;
use Illuminate\Http\Request as HttpRequest;

it('exposes the canonical shape contract', function () {
    $shape = AiEstimationResource::shape();

    expect($shape)->toHaveKeys(['features', 'missing_info', 'scope_creep_risks']);
    expect($shape['features'])->toHaveCount(1);
    expect($shape['features'][0])->toHaveKeys([
        'name', 'description', 'complexity', 'total_hours', 'risks',
    ]);
});

it('normalizes a full parsed response', function () {
    $parsed = [
        'features' => [
            [
                'name' => 'Login',
                'description' => 'Auth system',
                'complexity' => 'simple',
                'total_hours' => 8,
                'risks' => ['mot de passe oublié'],
            ],
        ],
        'missing_info' => ['design'],
        'scope_creep_risks' => ['paiement'],
    ];

    $result = (new AiEstimationResource($parsed))->toArray(new HttpRequest);

    expect($result)->toBe($parsed);
});

it('fills defaults when keys are missing', function () {
    $result = (new AiEstimationResource(['features' => []]))->toArray(new HttpRequest);

    expect($result)->toMatchArray([
        'features' => [],
        'missing_info' => [],
        'scope_creep_risks' => [],
    ]);
});

it('fills feature defaults when feature fields are missing', function () {
    $result = (new AiEstimationResource([
        'features' => [['name' => 'Paiement']],
    ]))->toArray(new HttpRequest);

    expect($result['features'][0])->toMatchArray([
        'name' => 'Paiement',
        'description' => null,
        'complexity' => null,
        'total_hours' => null,
        'risks' => [],
    ]);
});
