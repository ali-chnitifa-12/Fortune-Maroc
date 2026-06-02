<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionDowntime extends Model
{
    protected $fillable = [
        'production_entry_id',
        'machine_id',
        'started_at',
        'ended_at',
        'duration_min',
        'downtime_category_id',
        'downtime_reason_id',
        'comment',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'duration_min' => 'integer',
    ];

    public function productionEntry(): BelongsTo
    {
        return $this->belongsTo(ProductionEntry::class);
    }

    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }

    public function downtimeCategory(): BelongsTo
    {
        return $this->belongsTo(DowntimeCategory::class);
    }

    public function downtimeReason(): BelongsTo
    {
        return $this->belongsTo(DowntimeReason::class);
    }
}