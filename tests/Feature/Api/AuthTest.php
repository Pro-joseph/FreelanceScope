<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('users can register via api', function () {
    $response = $this->postJson('/api/auth/register', [
        'nom' => 'Doe',
        'prenom' => 'John',
        'email' => 'john@example.com',
        'password' => 'password',
    ]);

    expect($response->status())->toBe(201);
    expect($response->json())->toHaveKeys(['user', 'token']);
    expect($response->json('user'))->toHaveKeys(['id', 'nom', 'prenom', 'email', 'role']);
});

it('users can login via api', function () {
    $user = User::factory()->create([
        'email' => 'john@example.com',
        'password' => bcrypt('password'),
    ]);

    $response = $this->postJson('/api/auth/login', [
        'email' => 'john@example.com',
        'password' => 'password',
    ]);

    expect($response->status())->toBe(200);
    expect($response->json())->toHaveKeys(['user', 'token']);
});

it('users cannot login with invalid credentials', function () {
    User::factory()->create([
        'email' => 'john@example.com',
        'password' => bcrypt('password'),
    ]);

    $response = $this->postJson('/api/auth/login', [
        'email' => 'john@example.com',
        'password' => 'wrong-password',
    ]);

    expect($response->status())->toBe(422);
    expect($response->json('errors'))->toHaveKeys(['email']);
});

it('authenticated user can get their profile', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->getJson('/api/auth/me');

    expect($response->status())->toBe(200);
    expect($response->json())->toMatchArray([
        'id' => $user->id,
        'email' => $user->email,
    ]);
});

it('unauthenticated user cannot access protected routes', function () {
    expect($this->getJson('/api/auth/me')->status())->toBe(401);
    expect($this->getJson('/api/clients')->status())->toBe(401);
    expect($this->postJson('/api/clients')->status())->toBe(401);
});

it('users can logout via api', function () {
    $user = User::factory()->create();
    $token = $user->createToken('api-token')->plainTextToken;

    $response = $this->withToken($token)->postJson('/api/auth/logout');

    expect($response->status())->toBe(200);
    expect($response->json())->toMatchArray(['message' => 'Déconnecté.']);
    expect($user->tokens()->count())->toBe(0);
});
