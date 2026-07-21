<?php

namespace Database\Factories;

use App\Models\Estimate;
use App\Models\ProjectFeature;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Estimate> */
class EstimateFactory extends Factory
{
    protected $model = Estimate::class;

    public function definition(): array
    {
        $hours = fake()->randomFloat(2, 2, 80);
        $rate = fake()->randomFloat(2, 30, 150);

        return [
            'feature_id' => ProjectFeature::factory(),
            'hourly_rate' => $rate,
            'total_hours' => $hours,
            'total_amount' => $hours * $rate,
        ];
    }
}
