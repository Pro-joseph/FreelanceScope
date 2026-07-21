<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiAnalysis extends Model
{
    protected $fillable = [
        'project_id',
        'prompt',
        'response',
        'model',
        'tokens_used',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
