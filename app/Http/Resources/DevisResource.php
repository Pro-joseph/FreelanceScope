<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DevisResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'client' => [
                'company_name' => $this->client->company_name,
                'email' => $this->client->email,
                'phone' => $this->client->phone,
            ],
            'project' => [
                'name' => $this->project->name,
                'description' => $this->project->description,
            ],
            'features' => $this->when(
                $this->relationLoaded('project') && $this->project->relationLoaded('features'),
                fn () => $this->project->features->map(fn ($feature) => [
                    'name' => $feature->name,
                    'description' => $feature->description,
                    'complexity' => $feature->complexity,
                    'hourly_rate' => $feature->estimate?->hourly_rate,
                    'total_hours' => $feature->estimate?->total_hours,
                    'total_amount' => $feature->estimate?->total_amount,
                ]),
            ),
            'total_amount' => $this->total_amount,
            'conditions' => $this->conditions,
            'status' => $this->status,
            'pdf_path' => $this->pdf_path,
            'created_at' => $this->created_at,
        ];
    }
}
