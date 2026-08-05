<?php

use App\Models\Client;
use App\Models\Project;
use App\Models\ProjectFeature;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can list features for a project', function () {
    $user = User::factory()
        ->has(Client::factory()->has(Project::factory()->hasFeatures(3)))
        ->create();
    $project = $user->clients()->first()->projects()->first();

    $response = $this->actingAs($user)->getJson("/api/projects/{$project->id}/features");

    expect($response->status())->toBe(200);
    expect($response->json('data'))->toHaveCount(3);
});

it('can create a feature', function () {
    $user = User::factory()
        ->has(Client::factory()->hasProjects())
        ->create();
    $project = $user->clients()->first()->projects()->first();

    $response = $this->actingAs($user)->postJson("/api/projects/{$project->id}/features", [
        'name' => 'Page d\'accueil',
        'description' => 'Page d\'accueil avec présentation',
        'complexity' => 'moyen',
    ]);

    expect($response->status())->toBe(201);
    expect($response->json('data.name'))->toBe("Page d'accueil");
});

it('can view a feature', function () {
    $user = User::factory()
        ->has(Client::factory()->has(Project::factory()->hasFeatures()))
        ->create();
    $feature = $user->clients()->first()->projects()->first()->features()->first();

    $response = $this->actingAs($user)->getJson("/api/features/{$feature->id}");

    expect($response->status())->toBe(200);
    expect($response->json('data'))->toMatchArray(['id' => $feature->id]);
});

it('can update a feature', function () {
    $user = User::factory()
        ->has(Client::factory()->has(Project::factory()->hasFeatures()))
        ->create();
    $project = $user->clients()->first()->projects()->first();
    $feature = $project->features()->first();

    $response = $this->actingAs($user)->putJson("/api/projects/{$project->id}/features/{$feature->id}", [
        'name' => 'Feature modifiée',
        'complexity' => 'simple',
    ]);

    expect($response->status())->toBe(200);
    expect($response->json('data'))->toMatchArray([
        'name' => 'Feature modifiée',
        'complexity' => 'simple',
    ]);
});

it('can delete a feature', function () {
    $user = User::factory()
        ->has(Client::factory()->has(Project::factory()->hasFeatures()))
        ->create();
    $feature = $user->clients()->first()->projects()->first()->features()->first();

    $response = $this->actingAs($user)->deleteJson("/api/features/{$feature->id}");

    expect($response->status())->toBe(204);
    expect(ProjectFeature::find($feature->id))->toBeNull();
});

it('cannot create a feature on a foreign project', function () {
    $owner = User::factory()
        ->has(Client::factory()->hasProjects())
        ->create();
    $project = $owner->clients()->first()->projects()->first();
    $attacker = User::factory()->create();

    $response = $this->actingAs($attacker)->postJson("/api/projects/{$project->id}/features", [
        'name' => 'Intrus',
    ]);

    expect($response->status())->toBe(403);
});
