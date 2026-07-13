<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ProductionPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'plan_code',
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
        'entries_generated_at',
        'created_by',
    ];

    protected $casts = [
        'plan_date' => 'date',
        'planned_qty' => 'decimal:2',
        'target_oee' => 'decimal:2',
        'entries_generated_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (ProductionPlan $plan) {
            if (empty($plan->plan_code)) {
                $plan->plan_code = self::generateNextCode();
            }
        });
    }

    private static function generateNextCode(): string
    {
        $lastCode = DB::table('production_plans')
            ->whereNotNull('plan_code')
            ->where('plan_code', 'like', 'P%')
            ->orderByRaw('CAST(SUBSTRING(plan_code, 2) AS UNSIGNED) DESC')
            ->value('plan_code');

        $nextNumber = 1;

        if ($lastCode) {
            $nextNumber = ((int) substr($lastCode, 1)) + 1;
        }

        return 'P' . str_pad((string) $nextNumber, 9, '0', STR_PAD_LEFT);
    }

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

    public function downtimes()
    {
        return $this->hasMany(ProductionDowntime::class, 'production_plan_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function hasEntries(): bool
    {
        return $this->entries()->exists();
    }

    public function hasEntry(): bool
    {
        return $this->hasEntries();
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

    public function generatedEntriesCount(): int
    {
        return $this->entries()->count();
    }

    public function totalDowntimeMinutes(): int
    {
        return (int) $this->downtimes()
            ->whereNotNull('ended_at')
            ->sum('duration_min');
    }

    public function stopsCount(): int
    {
        return $this->downtimes()->count();
    }
}