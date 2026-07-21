<?php

namespace App\Models;

use Database\Factories\EstimateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Estimate extends Model
{
    /** @use HasFactory<EstimateFactory> */
    use HasFactory;

    protected $fillable = [
        'feature_id',
        'hourly_rate',
        'total_hours',
        'total_amount',
    ];

    protected function casts(): array
    {
        return [
            'hourly_rate' => 'decimal:2',
            'total_hours' => 'decimal:2',
            'total_amount' => 'decimal:2',
        ];
    }

    public function feature(): BelongsTo
    {
        return $this->belongsTo(ProjectFeature::class, 'feature_id');
    }
}
