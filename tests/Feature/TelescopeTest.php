<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    config()->set('telescope.enabled', true);
    config()->set('services.telescope_key', 'secret-telescope-key');
});

it('denies telescope access without a valid key', function () {
    $response = $this->get('/telescope');

    $response->assertStatus(403);
});

it('allows telescope access with a valid query key', function () {
    $response = $this->get('/telescope?key=secret-telescope-key');

    $response->assertStatus(200);
});

it('keeps telescope access within the session after a valid key', function () {
    $this->get('/telescope?key=secret-telescope-key')->assertStatus(200);

    $response = $this->get('/telescope');

    $response->assertStatus(200);
});
