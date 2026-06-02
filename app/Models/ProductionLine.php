<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProductionLine extends Model
{
    protected $fillable = [
        'zone_id',
        'code',
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function machines(): HasMany
    {
        return $this->hasMany(Machine::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'line_product')
            ->withPivot([
                'standard_qty_per_hour',
                'is_active',
            ])
            ->withTimestamps();
    }

    public function activeProducts(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'line_product')
            ->withPivot([
                'standard_qty_per_hour',
                'is_active',
            ])
            ->wherePivot('is_active', true)
            ->where('products.is_active', true)
            ->withTimestamps();
    }
}