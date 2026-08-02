<?php

use App\Jobs\GenerateEstimationJob;
use App\Models\AiAnalysis;
use App\Models\Estimate;
use App\Models\Project;
use App\Models\ProjectFeature;
use App\Models\User;
use App\Services\AIEstimationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('creates analysis, features and estimates from AI response', function () {
    $user = User::factory()->create(['taux_horaire' => 75]);
    $project = Project::factory()->create();

    $aiResult = [
        'parsed' => [
            'features' => [
                ['name' => 'Login', 'description' => 'Auth system', 'complexity' => 'simple', 'total_hours' => 8, 'risks' => []],
                ['name' => 'Dashboard', 'description' => 'Admin panel', 'complexity' => 'moyen', 'total_hours' => 24, 'risks' => []],
            ],
        ],
        'raw' => '{"features":[]}',
        'model' => 'llama-3.3-70b-versatile',
        'tokens_used' => 200,
    ];

    $ai = mock(AIEstimationService::class);
    $ai->shouldReceive('generate')->with('Mon projet')->once()->andReturn($aiResult);

    $job = new GenerateEstimationJob($project, $user->id, 'Mon projet');
    $job->handle($ai);

    expect(AiAnalysis::where('project_id', $project->id)->exists())->toBeTrue();

    $features = ProjectFeature::where('project_id', $project->id)->get();
    expect($features)->toHaveCount(2);
    expect($features[0]->name)->toBe('Login');
    expect($features[1]->name)->toBe('Dashboard');

    $estimates = Estimate::whereIn('feature_id', $features->pluck('id'))->get();
    expect($estimates)->toHaveCount(2);

    $estimate1 = $estimates->firstWhere('feature_id', $features[0]->id);
    expect($estimate1->hourly_rate)->toEqual(75);
    expect($estimate1->total_hours)->toEqual(8);
    expect($estimate1->total_amount)->toEqual(600);

    $estimate2 = $estimates->firstWhere('feature_id', $features[1]->id);
    expect($estimate2->hourly_rate)->toEqual(75);
    expect($estimate2->total_hours)->toEqual(24);
    expect($estimate2->total_amount)->toEqual(1800);
});

it('uses default taux_horaire of 50 when user has none', function () {
    $user = User::factory()->create(['taux_horaire' => null]);
    $project = Project::factory()->create();

    $aiResult = [
        'parsed' => [
            'features' => [
                ['name' => 'Contact', 'description' => 'Form', 'complexity' => 'simple', 'total_hours' => 10, 'risks' => []],
            ],
        ],
        'raw' => '{}',
        'model' => 'llama-3.3-70b-versatile',
        'tokens_used' => 50,
    ];

    $ai = mock(AIEstimationService::class);
    $ai->shouldReceive('generate')->with('test')->once()->andReturn($aiResult);

    $job = new GenerateEstimationJob($project, $user->id, 'test');
    $job->handle($ai);

    $feature = ProjectFeature::where('project_id', $project->id)->first();
    $estimate = Estimate::where('feature_id', $feature->id)->first();

    expect($estimate->hourly_rate)->toEqual(50);
    expect($estimate->total_hours)->toEqual(10);
    expect($estimate->total_amount)->toEqual(500);
});

it('does not duplicate features when run twice with the same prompt', function () {
    $user = User::factory()->create(['taux_horaire' => 75]);
    $project = Project::factory()->create();

    $aiResult = [
        'parsed' => [
            'features' => [
                ['name' => 'Login', 'description' => 'Auth system', 'complexity' => 'simple', 'total_hours' => 8, 'risks' => []],
                ['name' => 'Dashboard', 'description' => 'Admin panel', 'complexity' => 'moyen', 'total_hours' => 24, 'risks' => []],
            ],
        ],
        'raw' => '{"features":[]}',
        'model' => 'llama-3.3-70b-versatile',
        'tokens_used' => 200,
    ];

    $ai = mock(AIEstimationService::class);
    $ai->shouldReceive('generate')->with('Mon projet')->twice()->andReturn($aiResult);

    $job = new GenerateEstimationJob($project, $user->id, 'Mon projet');
    $job->handle($ai);
    $job->handle($ai);

    $features = ProjectFeature::where('project_id', $project->id)->get();
    expect($features)->toHaveCount(2);
    expect($features->pluck('name')->all())->toEqual(['Login', 'Dashboard']);

    $estimates = Estimate::whereIn('feature_id', $features->pluck('id'))->get();
    expect($estimates)->toHaveCount(2);
});
