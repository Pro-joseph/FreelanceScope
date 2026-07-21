<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Devis;
use App\Models\Estimate;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Devis> */
class DevisFactory extends Factory
{
    protected $model = Devis::class;

    public function definition(): array
    {
        return [
            'estimate_id' => Estimate::factory(),
            'client_id' => Client::factory(),
            'project_id' => Project::factory(),
            'total_amount' => fake()->randomFloat(2, 500, 10000),
            'conditions' => fake()->sentence(),
            'status' => 'draft',
        ];
    }
}
