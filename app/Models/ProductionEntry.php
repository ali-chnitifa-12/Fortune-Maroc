<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ProductionEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'entry_code',
        'production_plan_id',
        'zone_id',
        'production_line_id',
        'production_date',
        'shift_id',
        'machine_id',
        'product_id',
        'hour_start',
        'hour_end',
        'planned_qty',
        'actual_qty',
        'rejected_qty',
        'chute_qty',
        'chute_1_qty',
        'chute_2_qty',
        'chute_3_qty',
        'good_qty',
        'machine_status',
        'entry_status',
        'stop_duration_min',
        'stops_count',
        'availability',
        'performance',
        'quality',
        'oee',
        'sent_to_thingsboard',
        'thingsboard_response',
        'comment',
        'current_stop_started_at',
        'stop_started_at',
        'stop_ended_at',
        'completed_at',
        'approved_by',
        'approved_at',
        'created_by',
    ];

    protected $casts = [
        'production_date' => 'date',
        'planned_qty' => 'decimal:2',
        'actual_qty' => 'decimal:2',
        'rejected_qty' => 'decimal:2',
        'chute_qty' => 'decimal:2',
        'chute_1_qty' => 'decimal:2',
        'chute_2_qty' => 'decimal:2',
        'chute_3_qty' => 'decimal:2',
        'good_qty' => 'decimal:2',
        'availability' => 'decimal:2',
        'performance' => 'decimal:2',
        'quality' => 'decimal:2',
        'oee' => 'decimal:2',
        'sent_to_thingsboard' => 'boolean',
        'current_stop_started_at' => 'datetime',
        'stop_started_at' => 'datetime',
        'stop_ended_at' => 'datetime',
        'completed_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (ProductionEntry $entry) {
            if (empty($entry->entry_code)) {
                $entry->entry_code = self::generateNextCode();
            }
        });
    }

    private static function generateNextCode(): string
    {
        $lastCode = DB::table('production_entries')
            ->whereNotNull('entry_code')
            ->where('entry_code', 'like', 'E%')
            ->orderByRaw('CAST(SUBSTRING(entry_code, 2) AS UNSIGNED) DESC')
            ->value('entry_code');

        $nextNumber = 1;

        if ($lastCode) {
            $nextNumber = ((int) substr($lastCode, 1)) + 1;
        }

        return 'E' . str_pad((string) $nextNumber, 9, '0', STR_PAD_LEFT);
    }

    public function productionPlan()
    {
        return $this->belongsTo(ProductionPlan::class, 'production_plan_id');
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

    public function machine()
    {
        return $this->belongsTo(Machine::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function downtimes()
    {
        return $this->hasMany(ProductionDowntime::class, 'production_entry_id');
    }

    public function planDowntimes()
    {
        return $this->hasMany(ProductionDowntime::class, 'production_plan_id', 'production_plan_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isDraft(): bool
    {
        return $this->entry_status === 'draft';
    }

    public function isFinished(): bool
    {
        return $this->entry_status === 'finished';
    }

    public function isSentToThingsBoard(): bool
    {
        return $this->entry_status === 'sent_to_thingsboard';
    }
}