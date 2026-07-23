<?php

use App\Models\Estimate;
use App\Models\ProjectFeature;
use App\Models\User;
use App\Models\Client;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can view estimate for feature', function () {
    $user = User::factory()
        ->has(Client::factory()->has(Project::factory()->hasFeatures()))
        ->create();
    $feature = $user->clients()->first()->projects()->first()->features()->first();
    $estimate = Estimate::factory()->for($feature, 'feature')->create();

    $response = $this->actingAs($user)->getJson("/api/features/{$feature->id}/estimate");

    expect($response->status())->toBe(200);
    expect($response->json('data.id'))->toBe($estimate->id);
});

it('returns 404 json when no estimate exists', function () {
    $user = User::factory()
        ->has(Client::factory()->has(Project::factory()->hasFeatures()))
        ->create();
    $feature = $user->clients()->first()->projects()->first()->features()->first();

    $response = $this->actingAs($user)->getJson("/api/features/{$feature->id}/estimate");

    expect($response->status())->toBe(404);
    expect($response->json())->toMatchArray([
        'message' => 'Aucune estimation générée pour cette fonctionnalité.',
        'feature_id' => $feature->id,
    ]);
});

it('can update an estimate', function () {
    $user = User::factory()
        ->has(Client::factory()->has(Project::factory()->hasFeatures()))
        ->create();
    $feature = $user->clients()->first()->projects()->first()->features()->first();
    $estimate = Estimate::factory()->for($feature, 'feature')->create([
        'hourly_rate' => 50,
        'total_hours' => 10,
    ]);

    $response = $this->actingAs($user)->putJson("/api/estimates/{$estimate->id}", [
        'hourly_rate' => 65,
        'total_hours' => 20,
    ]);

    expect($response->status())->toBe(200);
    expect($estimate->fresh())
        ->hourly_rate->toEqual(65.0)
        ->total_hours->toEqual(20.0)
        ->total_amount->toEqual(1300.0);
});
