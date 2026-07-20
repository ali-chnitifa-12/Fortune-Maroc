<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Absence extends Model
{
    protected $fillable = [
        'user_id',
        'date',
        'type',
        'motif',
        'statut',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public static array $types = [
        'absence'  => 'Absence',
        'retard'   => 'Retard',
        'conge'    => 'Congé',
        'maladie'  => 'Maladie',
    ];

    public static array $statuts = [
        'pending'  => 'En attente',
        'approved' => 'Approuvé',
        'rejected' => 'Refusé',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function typeLabel(): string
    {
        return self::$types[$this->type] ?? ucfirst($this->type);
    }

    public function statutLabel(): string
    {
        return self::$statuts[$this->statut] ?? ucfirst($this->statut);
    }
}
