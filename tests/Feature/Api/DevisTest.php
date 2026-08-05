<?php

use App\Models\Client;
use App\Models\Devis;
use App\Models\Estimate;
use App\Models\Project;
use App\Models\ProjectFeature;
use App\Models\User;
use App\Services\DevisService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can list devis', function () {
    $user = User::factory()
        ->has(Client::factory()->has(Project::factory()))
        ->create();
    $client = $user->clients()->first();
    $project = $client->projects()->first();
    $feature = ProjectFeature::factory()->for($project)->create();
    $estimate = Estimate::factory()->for($feature, 'feature')->create();
    Devis::factory()->create([
        'estimate_id' => $estimate->id,
        'client_id' => $client->id,
        'project_id' => $project->id,
    ]);

    $response = $this->actingAs($user)->getJson("/api/projects/{$project->id}/devis");

    expect($response->status())->toBe(200);
    expect($response->json())->toHaveKey('data');
    expect($response->json('data'))->toHaveCount(1);
});

it('can create a devis', function () {
    $user = User::factory()->create(['taux_horaire' => 50]);
    $client = Client::factory()->for($user)->create();
    $project = Project::factory()->for($client)->create();
    $feature = ProjectFeature::factory()->for($project)->create();
    Estimate::factory()->for($feature, 'feature')->create([
        'hourly_rate' => 50,
        'total_hours' => 16,
        'total_amount' => 800,
    ]);

    $response = $this->actingAs($user)->postJson("/api/projects/{$project->id}/devis", [
        'conditions' => '50% à la commande',
    ]);

    expect($response->status())->toBe(201);
    expect($response->json('total_amount'))->toEqual(800);
});

it('can generate and view a devis', function () {
    $user = User::factory()->create(['taux_horaire' => 50]);
    $client = Client::factory()->for($user)->create();
    $project = Project::factory()->for($client)->create();
    $feature = ProjectFeature::factory()->for($project)->create();
    Estimate::factory()->for($feature, 'feature')->create([
        'hourly_rate' => 50,
        'total_hours' => 16,
        'total_amount' => 800,
    ]);

    $service = app(DevisService::class);
    $devis = $service->generate($client->id, $project->id, '50% à la commande');

    expect($devis->id)->not->toBeNull();
    expect($devis->total_amount)->toEqual(800);
    expect($devis->status)->toBe('draft');

    $found = Devis::find($devis->id);
    expect($found->id)->toBe($devis->id);
});

it('can update a devis status', function () {
    $user = User::factory()->create(['taux_horaire' => 50]);
    $client = Client::factory()->for($user)->create();
    $project = Project::factory()->for($client)->create();
    $feature = ProjectFeature::factory()->for($project)->create();
    Estimate::factory()->for($feature, 'feature')->create([
        'hourly_rate' => 50,
        'total_hours' => 10,
        'total_amount' => 500,
    ]);

    $service = app(DevisService::class);
    $devis = $service->generate($client->id, $project->id, null);

    $service->update($devis, ['status' => 'sent']);

    expect($devis->fresh()->status)->toBe('sent');
});

it('can delete a devis', function () {
    $user = User::factory()->create(['taux_horaire' => 50]);
    $client = Client::factory()->for($user)->create();
    $project = Project::factory()->for($client)->create();
    $feature = ProjectFeature::factory()->for($project)->create();
    Estimate::factory()->for($feature, 'feature')->create([
        'hourly_rate' => 50,
        'total_hours' => 10,
        'total_amount' => 500,
    ]);

    $service = app(DevisService::class);
    $devis = $service->generate($client->id, $project->id, null);

    $service->delete($devis);

    expect(Devis::find($devis->id))->toBeNull();
});

it('cannot create a devis on a foreign project', function () {
    $owner = User::factory()->create(['taux_horaire' => 50]);
    $client = Client::factory()->for($owner)->create();
    $project = Project::factory()->for($client)->create();
    $attacker = User::factory()->create();

    $response = $this->actingAs($attacker)->postJson("/api/projects/{$project->id}/devis", [
        'conditions' => '50% à la commande',
    ]);

    expect($response->status())->toBe(403);
    expect(Devis::count())->toBe(0);
});
