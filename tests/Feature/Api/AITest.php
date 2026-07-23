<?php

use App\Jobs\GenerateEstimationJob;
use App\Models\Client;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;

uses(RefreshDatabase::class);

it('can dispatch estimation job', function () {
    Bus::fake();

    $user = User::factory()->create(['taux_horaire' => 50]);
    $client = Client::factory()->for($user)->create();
    $project = Project::factory()->for($client)->create();

    $response = $this->actingAs($user)->postJson("/api/projects/{$project->id}/generate-estimate", [
        'prompt' => 'Je veux un site e-commerce avec catalogue et paiement.',
    ]);

    expect($response->status())->toBe(202);
    expect($response->json())->toMatchArray(['message' => 'Estimation en cours de génération.']);
    Bus::assertDispatched(GenerateEstimationJob::class);
});

it('cannot dispatch estimation without prompt', function () {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create();
    $project = Project::factory()->for($client)->create();

    $response = $this->actingAs($user)
        ->postJson("/api/projects/{$project->id}/generate-estimate", []);

    expect($response->status())->toBe(422);
    expect($response->json('errors'))->toHaveKeys(['prompt']);
});
