<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EstimateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'feature_id' => $this->feature_id,
            'hourly_rate' => $this->hourly_rate,
            'total_hours' => $this->total_hours,
            'total_amount' => $this->total_amount,
            'created_at' => $this->created_at,
        ];
    }
}
