<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\ProjectFeature;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ProjectFeature> */
class ProjectFeatureFactory extends Factory
{
    protected $model = ProjectFeature::class;

    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'name' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'complexity' => fake()->randomElement(['simple', 'moyen', 'complexe']),
        ];
    }
}
