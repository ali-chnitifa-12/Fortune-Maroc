<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $fillable = [
        'full_name',
        'matricule',
        'department',
        'position',
        'production_line_id',
        'is_active',
        'departure_date',
        'departure_reason',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'departure_date' => 'date',
        ];
    }

    public function productionLine()
    {
        return $this->belongsTo(ProductionLine::class, 'production_line_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}