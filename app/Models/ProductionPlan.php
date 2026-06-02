<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'plan_date',
        'zone_id',
        'production_line_id',
        'shift_id',
        'product_id',
        'hour_start',
        'hour_end',
        'planned_qty',
        'target_oee',
        'responsible',
        'notes',
        'status',
        'created_by',
    ];

    protected $casts = [
        'plan_date' => 'date',
        'planned_qty' => 'decimal:2',
        'target_oee' => 'decimal:2',
    ];

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }

    public function productionLine()
    {
        return $this->belongsTo(ProductionLine::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function entries()
    {
        return $this->hasMany(ProductionEntry::class, 'production_plan_id');
    }

    public function productionEntries()
    {
        return $this->hasMany(ProductionEntry::class, 'production_plan_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function hasEntry(): bool
    {
        return $this->entries()->exists();
    }

    public function isPlanned(): bool
    {
        return $this->status === 'planned';
    }

    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }
}