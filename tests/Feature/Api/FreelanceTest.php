<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('freelance can get profile', function () {
    $user = User::factory()->create([
        'taux_horaire' => 50,
        'telephone' => '+212600000000',
    ]);

    $response = $this->actingAs($user)->getJson('/api/freelance/profile');

    expect($response->status())->toBe(200);
    expect($response->json())->toMatchArray([
        'id' => $user->id,
        'nom' => $user->nom,
        'prenom' => $user->prenom,
        'email' => $user->email,
        'taux_horaire' => 50,
        'telephone' => '+212600000000',
    ]);
});

it('freelance can update profile', function () {
    $user = User::factory()->create(['taux_horaire' => 50]);

    $response = $this->actingAs($user)->putJson('/api/freelance/profile', [
        'taux_horaire' => 75,
        'telephone' => '+212600000001',
    ]);

    expect($response->status())->toBe(200);
    expect($response->json())->toMatchArray([
        'taux_horaire' => 75,
        'telephone' => '+212600000001',
    ]);
    expect((float) $user->fresh()->taux_horaire)->toBe(75.0);
});

it('freelance can view dashboard', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->getJson('/api/dashboard/stats');

    expect($response->status())->toBe(200);
    expect($response->json())->toHaveKeys(['clients_count', 'projects_count', 'devis_count']);
});
