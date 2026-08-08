<?php

use App\Models\Client;
use App\Models\Devis;
use App\Models\Estimate;
use App\Models\Project;
use App\Models\ProjectFeature;
use App\Services\DevisService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('generates a devis with total from feature estimates', function () {
    $client = Client::factory()->create();
    $project = Project::factory()->for($client)->create();
    $feature = ProjectFeature::factory()->for($project)->create();
    Estimate::factory()->for($feature, 'feature')->create([
        'hourly_rate' => 50,
        'total_hours' => 20,
        'total_amount' => 1000,
    ]);

    $devis = app(DevisService::class)->generate($client->id, $project->id, 'Net 30');

    expect($devis->total_amount)->toEqual(1000);
    expect($devis->client_id)->toBe($client->id);
    expect($devis->project_id)->toBe($project->id);
    expect($devis->conditions)->toBe('Net 30');
    expect($devis->status)->toBe('draft');
});

it('generates a devis with summed totals from multiple features', function () {
    $client = Client::factory()->create();
    $project = Project::factory()->for($client)->create();
    $featureA = ProjectFeature::factory()->for($project)->create();
    $featureB = ProjectFeature::factory()->for($project)->create();
    Estimate::factory()->for($featureA, 'feature')->create(['total_amount' => 400]);
    Estimate::factory()->for($featureB, 'feature')->create(['total_amount' => 600]);

    $devis = app(DevisService::class)->generate($client->id, $project->id, null);

    expect($devis->total_amount)->toEqual(1000);
});

it('needs regeneration when pdf_path is null', function () {
    $devis = Devis::factory()->make(['pdf_path' => null, 'pdf_generated_at' => now()]);

    expect(app(DevisService::class)->needsRegeneration($devis))->toBeTrue();
});

it('needs regeneration when pdf_generated_at is null', function () {
    $devis = Devis::factory()->make(['pdf_path' => 'devis_1.pdf', 'pdf_generated_at' => null]);

    expect(app(DevisService::class)->needsRegeneration($devis))->toBeTrue();
});

it('does not need regeneration when pdf is up to date', function () {
    Storage::fake('devis');
    Storage::disk('devis')->put('devis_1.pdf', 'fake content');

    $devis = Devis::factory()->make([
        'pdf_path' => 'devis_1.pdf',
        'pdf_generated_at' => now(),
    ]);
    $devis->updated_at = now()->subHour();

    expect(app(DevisService::class)->needsRegeneration($devis))->toBeFalse();
});

it('needs regeneration when pdf file is missing on disk', function () {
    Storage::fake('devis');

    $devis = Devis::factory()->make([
        'pdf_path' => 'devis_missing.pdf',
        'pdf_generated_at' => now(),
    ]);
    $devis->updated_at = now()->subHour();

    expect(app(DevisService::class)->needsRegeneration($devis))->toBeTrue();
});

it('needs regeneration when devis was updated after pdf generation', function () {
    $devis = Devis::factory()->make([
        'pdf_path' => 'devis_1.pdf',
        'pdf_generated_at' => now()->subDay(),
    ]);
    $devis->updated_at = now();

    expect(app(DevisService::class)->needsRegeneration($devis))->toBeTrue();
});
