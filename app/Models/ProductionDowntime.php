<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionDowntime extends Model
{
    use HasFactory;

    protected $fillable = [
        'production_plan_id',
        'production_entry_id',
        'machine_id',
        'started_at',
        'ended_at',
        'duration_min',
        'downtime_category_id',
        'downtime_reason_id',
        'comment',
        'created_by',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'duration_min' => 'integer',
    ];

    public function productionPlan()
    {
        return $this->belongsTo(ProductionPlan::class, 'production_plan_id');
    }

    public function productionEntry()
    {
        return $this->belongsTo(ProductionEntry::class, 'production_entry_id');
    }

    public function machine()
    {
        return $this->belongsTo(Machine::class);
    }

    public function downtimeCategory()
    {
        return $this->belongsTo(DowntimeCategory::class, 'downtime_category_id');
    }

    public function downtimeReason()
    {
        return $this->belongsTo(DowntimeReason::class, 'downtime_reason_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isOpen(): bool
    {
        return $this->ended_at === null;
    }

    public function isClosed(): bool
    {
        return $this->ended_at !== null;
    }
}