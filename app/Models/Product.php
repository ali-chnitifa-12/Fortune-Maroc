<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
        'standard_qty_per_hour',
        'is_active',
    ];

    protected $casts = [
        'standard_qty_per_hour' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function productionLines(): BelongsToMany
    {
        return $this->belongsToMany(ProductionLine::class, 'line_product')
            ->withPivot([
                'standard_qty_per_hour',
                'is_active',
            ])
            ->withTimestamps();
    }
}