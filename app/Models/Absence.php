<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Absence extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'employee_name',
        'absence_date',
        'shift',
        'reason',
        'hours',
        'comment',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'absence_date' => 'date',
            'hours' => 'decimal:2',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}