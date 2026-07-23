<?php

use App\Services\AIEstimationService;
use Illuminate\Support\Facades\Facade;

afterEach(function () {
    Mockery::close();
    app()->forgetInstance('openai');
    Facade::clearResolvedInstance('openai');
});

it('parses a clean JSON response', function () {
    $data = [
        'features' => [
            ['name' => 'Login', 'description' => 'Auth system', 'complexity' => 'simple', 'total_hours' => 8, 'risks' => []],
        ],
        'missing_info' => [],
        'scope_creep_risks' => [],
    ];
    $json = json_encode($data);

    $chat = Mockery::mock('OpenAI\Contracts\Resources\Chat');
    $chat->shouldReceive('create')->once()->andReturn((object) [
        'choices' => [(object) ['message' => (object) ['content' => $json]]],
        'usage' => (object) ['totalTokens' => 100],
    ]);

    $client = Mockery::mock('OpenAI\Contracts\Client');
    $client->shouldReceive('chat')->once()->andReturn($chat);

    app()->instance('openai', $client);

    $result = app(AIEstimationService::class)->generate('Creer un site vitrine');

    expect($result['parsed'])->toBe($data);
    expect($result['raw'])->toBe($json);
    expect($result['model'])->toBe('llama-3.3-70b-versatile');
    expect($result['tokens_used'])->toBe(100);
});

it('strips markdown fences from AI response', function () {
    $data = [
        'features' => [
            ['name' => 'Dashboard', 'description' => 'Admin panel', 'complexity' => 'moyen', 'total_hours' => 24, 'risks' => []],
        ],
        'missing_info' => [],
        'scope_creep_risks' => [],
    ];
    $markdownWrapped = "```json\n" . json_encode($data) . "\n```";

    $chat = Mockery::mock('OpenAI\Contracts\Resources\Chat');
    $chat->shouldReceive('create')->once()->andReturn((object) [
        'choices' => [(object) ['message' => (object) ['content' => $markdownWrapped]]],
        'usage' => (object) ['totalTokens' => 50],
    ]);

    $client = Mockery::mock('OpenAI\Contracts\Client');
    $client->shouldReceive('chat')->once()->andReturn($chat);

    app()->instance('openai', $client);

    $result = app(AIEstimationService::class)->generate('test');

    expect($result['parsed'])->toBe($data);
});

it('throws when AI returns unparseable content', function () {
    $chat = Mockery::mock('OpenAI\Contracts\Resources\Chat');
    $chat->shouldReceive('create')->once()->andReturn((object) [
        'choices' => [(object) ['message' => (object) ['content' => 'not valid json at all']]],
        'usage' => (object) ['totalTokens' => 10],
    ]);

    $client = Mockery::mock('OpenAI\Contracts\Client');
    $client->shouldReceive('chat')->once()->andReturn($chat);

    app()->instance('openai', $client);

    app(AIEstimationService::class)->generate('test');
})->throws(RuntimeException::class, 'Failed to parse AI response');
