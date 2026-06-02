<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DowntimeReason extends Model
{
    protected $fillable = [
        'downtime_category_id',
        'name',
        'is_active',
    ];

    public function category()
    {
        return $this->belongsTo(DowntimeCategory::class, 'downtime_category_id');
    }
}
