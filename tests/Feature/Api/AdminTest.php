<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => UserRole::Admin]);
});

it('admin can view dashboard', function () {
    $response = $this->actingAs($this->admin)->getJson('/api/admin/dashboard');

    expect($response->status())->toBe(200);
    expect($response->json())->toHaveKeys(['freelances_count', 'clients_count', 'projects_count', 'devis_count']);
});

it('admin can list freelances', function () {
    User::factory()->count(3)->create(['role' => UserRole::Freelance]);

    $response = $this->actingAs($this->admin)->getJson('/api/admin/freelances');

    expect($response->status())->toBe(200);
});

it('admin can create a freelance', function () {
    $response = $this->actingAs($this->admin)->postJson('/api/admin/freelances', [
        'nom' => 'Jane',
        'prenom' => 'Doe',
        'email' => 'jane@example.com',
        'password' => 'password123',
        'telephone' => '+212600000001',
        'taux_horaire' => 75,
    ]);

    expect($response->status())->toBe(201);
    expect($response->json())->toMatchArray(['role' => 'freelance']);
});

it('admin can update a freelance', function () {
    $freelance = User::factory()->create(['role' => UserRole::Freelance, 'taux_horaire' => 50]);

    $response = $this->actingAs($this->admin)->putJson("/api/admin/freelances/{$freelance->id}", [
        'taux_horaire' => 80,
    ]);

    expect($response->status())->toBe(200);
    expect((float) $freelance->fresh()->taux_horaire)->toBe(80.0);
});

it('admin can toggle freelance status', function () {
    $freelance = User::factory()->create(['role' => UserRole::Freelance, 'statut' => 'actif']);

    $response = $this->actingAs($this->admin)->patchJson("/api/admin/freelances/{$freelance->id}/statut");

    expect($response->status())->toBe(200);
    expect($response->json())->toMatchArray(['statut' => 'inactif']);
});

it('admin can delete a freelance', function () {
    $freelance = User::factory()->create(['role' => UserRole::Freelance]);

    $response = $this->actingAs($this->admin)->deleteJson("/api/admin/freelances/{$freelance->id}");

    expect($response->status())->toBe(204);
    expect(User::find($freelance->id))->toBeNull();
});

it('non-admin cannot access admin endpoints', function () {
    $user = User::factory()->create(['role' => UserRole::Freelance]);

    expect($this->actingAs($user)->getJson('/api/admin/dashboard')->status())->toBe(403);
    expect($this->actingAs($user)->getJson('/api/admin/freelances')->status())->toBe(403);
});
