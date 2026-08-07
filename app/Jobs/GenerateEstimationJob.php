<?php

namespace App\Jobs;

use App\Models\AiAnalysis;
use App\Models\Estimate;
use App\Models\Project;
use App\Models\ProjectFeature;
use App\Models\User;
use App\Services\AIEstimationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

class GenerateEstimationJob implements ShouldQueue
{
    use Queueable;

    public $tries = 3;

    public $timeout = 300;

    public function __construct(
        private readonly Project $project,
        private readonly int $userId,
        private readonly string $prompt,
    ) {}

    public function handle(AIEstimationService $ai): void
    {
        $user = User::findOrFail($this->userId);
        $result = $ai->generate($this->prompt);

        DB::transaction(function () use ($user, $result) {
            AiAnalysis::create([
                'project_id' => $this->project->id,
                'prompt' => $this->prompt,
                'response' => $result['raw'],
                'model' => $result['model'],
                'tokens_used' => $result['tokens_used'],
            ]);

            $hourlyRate = $user->taux_horaire ?? 50;

            foreach ($result['parsed']['features'] as $featureData) {
                $exists = ProjectFeature::where('project_id', $this->project->id)
                    ->where('name', $featureData['name'])
                    ->exists();

                if ($exists) {
                    continue;
                }

                $feature = ProjectFeature::create([
                    'project_id' => $this->project->id,
                    'name' => $featureData['name'],
                    'description' => $featureData['description'] ?? null,
                    'complexity' => $featureData['complexity'] ?? 'moyen',
                ]);

                Estimate::create([
                    'feature_id' => $feature->id,
                    'hourly_rate' => $hourlyRate,
                    'total_hours' => $featureData['total_hours'],
                    'total_amount' => $hourlyRate * $featureData['total_hours'],
                ]);
            }
        });
    }
}
