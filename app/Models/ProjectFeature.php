<?php

namespace App\Models;

use Database\Factories\ProjectFeatureFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ProjectFeature extends Model
{
    /** @use HasFactory<ProjectFeatureFactory> */
    use HasFactory;

    protected $fillable = [
        'project_id',
        'name',
        'description',
        'complexity',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function estimate(): HasOne
    {
        return $this->hasOne(Estimate::class, 'feature_id');
    }
}
