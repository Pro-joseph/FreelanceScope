<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'client_id' => $this->client_id,
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status,
            'features_count' => $this->whenCounted('features'),
            'client' => $this->whenLoaded('client', fn () => new ClientResource($this->client)),
            'features' => ProjectFeatureResource::collection($this->whenLoaded('features')),
            'created_at' => $this->created_at,
        ];
    }
}
