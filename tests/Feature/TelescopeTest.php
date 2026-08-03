<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    config()->set('telescope.enabled', true);
});

it('denies telescope access to guests', function () {
    $this->get('/telescope')->assertStatus(403);
});

it('denies telescope authorization to non-admins', function () {
    $freelancer = User::factory()->create(['role' => UserRole::Freelance]);

    $this->actingAs($freelancer)
        ->postJson('/api/admin/telescope/authorize')
        ->assertStatus(403);

    $this->get('/telescope')->assertStatus(403);
});

it('allows telescope access to admins who authorized the session', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $this->actingAs($admin)
        ->postJson('/api/admin/telescope/authorize')
        ->assertStatus(200);

    $this->get('/telescope')->assertStatus(200);
});

it('records successful api requests', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->getJson('/api/auth/me')->assertStatus(200);

    expect(DB::table('telescope_entries')->where('type', 'request')->count())->toBeGreaterThan(0);
});
