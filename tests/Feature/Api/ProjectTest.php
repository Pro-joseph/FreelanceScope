<?php

use App\Models\Client;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can list projects', function () {
    $user = User::factory()
        ->has(Client::factory()->hasProjects(2))
        ->create();

    $response = $this->actingAs($user)->getJson('/api/projects');

    expect($response->status())->toBe(200);
    expect($response->json())->toHaveKey('data');
    expect($response->json('data'))->toHaveCount(2);
    expect($response->json('meta.total'))->toBe(2);
});

it('can create a project', function () {
    $user = User::factory()->hasClients()->create();
    $client = $user->clients()->first();

    $response = $this->actingAs($user)->postJson('/api/projects', [
        'client_id' => $client->id,
        'name' => 'Site e-commerce',
        'description' => 'Site de vente en ligne',
    ]);

    expect($response->status())->toBe(201);
    expect($response->json('data.name'))->toBe('Site e-commerce');
});

it('can view a project', function () {
    $user = User::factory()
        ->has(Client::factory()->hasProjects())
        ->create();
    $project = $user->clients()->first()->projects()->first();

    $response = $this->actingAs($user)->getJson("/api/projects/{$project->id}");

    expect($response->status())->toBe(200);
    expect($response->json('data.id'))->toBe($project->id);
});

it('can update a project', function () {
    $user = User::factory()
        ->has(Client::factory()->hasProjects())
        ->create();
    $project = $user->clients()->first()->projects()->first();

    $response = $this->actingAs($user)->putJson("/api/projects/{$project->id}", [
        'name' => 'Projet mis à jour',
        'status' => 'in_progress',
    ]);

    expect($response->status())->toBe(200);
    expect($response->json('data.name'))->toBe('Projet mis à jour');
});

it('can delete a project', function () {
    $user = User::factory()
        ->has(Client::factory()->hasProjects())
        ->create();
    $project = $user->clients()->first()->projects()->first();

    $response = $this->actingAs($user)->deleteJson("/api/projects/{$project->id}");

    expect($response->status())->toBe(204);
    expect(Project::find($project->id))->toBeNull();
});

it('cannot create project for other users client', function () {
    $user1 = User::factory()->hasClients()->create();
    $user2 = User::factory()->create();
    $client = $user1->clients()->first();

    expect($this->actingAs($user2)
        ->postJson('/api/projects', ['client_id' => $client->id, 'name' => 'Test'])
        ->status())->toBe(422);
});
