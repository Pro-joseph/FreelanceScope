<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_name' => $this->company_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'projects_count' => $this->whenCounted('projects'),
            'user' => $this->whenLoaded('user', fn () => [
                'id' => $this->user?->id,
                'nom' => $this->user?->nom,
                'prenom' => $this->user?->prenom,
            ]),
            'created_at' => $this->created_at,
        ];
    }
}
