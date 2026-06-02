<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DowntimeCategory extends Model
{
    protected $fillable = [
        'name',
        'is_active',
    ];
}
