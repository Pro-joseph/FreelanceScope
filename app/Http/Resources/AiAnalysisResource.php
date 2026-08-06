<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AiAnalysisResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'project_id' => $this->project_id,
            'prompt' => $this->prompt,
            'response' => $this->response,
            'model' => $this->model,
            'tokens_used' => $this->tokens_used,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
