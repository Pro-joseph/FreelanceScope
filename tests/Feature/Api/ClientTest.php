<?php

use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can list clients', function () {
    $user = User::factory()->hasClients(3)->create();

    $response = $this->actingAs($user)->getJson('/api/clients');

    expect($response->status())->toBe(200);
    expect($response->json())->toHaveKey('data');
    expect($response->json('data'))->toHaveCount(3);
    expect($response->json('total'))->toBe(3);
});

it('can create a client', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/api/clients', [
        'company_name' => 'Acme Corp',
        'email' => 'contact@acme.com',
        'phone' => '+212600000000',
    ]);

    expect($response->status())->toBe(201);
    expect($response->json('data.company_name'))->toBe('Acme Corp');
});

it('can view a client', function () {
    $user = User::factory()->hasClients()->create();
    $client = $user->clients()->first();

    $response = $this->actingAs($user)->getJson("/api/clients/{$client->id}");

    expect($response->status())->toBe(200);
    expect($response->json('data.id'))->toBe($client->id);
});

it('can update a client', function () {
    $user = User::factory()->hasClients()->create();
    $client = $user->clients()->first();

    $response = $this->actingAs($user)->putJson("/api/clients/{$client->id}", [
        'company_name' => 'Updated Corp',
    ]);

    expect($response->status())->toBe(200);
    expect($response->json('data.company_name'))->toBe('Updated Corp');
});

it('can delete a client', function () {
    $user = User::factory()->hasClients()->create();
    $client = $user->clients()->first();

    $response = $this->actingAs($user)->deleteJson("/api/clients/{$client->id}");

    expect($response->status())->toBe(204);
    expect(Client::find($client->id))->toBeNull();
});

it('cannot view other users client', function () {
    $user1 = User::factory()->hasClients()->create();
    $user2 = User::factory()->create();
    $client = $user1->clients()->first();

    expect($this->actingAs($user2)->getJson("/api/clients/{$client->id}")->status())->toBe(403);
});

it('cannot create client without company_name', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/api/clients', [
        'email' => 'contact@acme.com',
    ]);

    expect($response->status())->toBe(422);
    expect($response->json('errors'))->toHaveKeys(['company_name']);
});

it('rejects a phone containing text', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/api/clients', [
        'company_name' => 'Acme Corp',
        'phone' => 'call me 0612345678',
    ]);

    expect($response->status())->toBe(422);
    expect($response->json('errors'))->toHaveKeys(['phone']);
});
