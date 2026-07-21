<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory;
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'zone_id',
        'production_line_id',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }

    public function productionLine()
    {
        return $this->belongsTo(ProductionLine::class);
    }

    public function assignedZones()
    {
        return $this->belongsToMany(Zone::class, 'user_zones')
            ->withTimestamps();
    }

    public function roleValue(): string
    {
        return strtolower(trim((string) $this->role));
    }

    public function isAdmin(): bool
    {
        return $this->roleValue() === 'admin';
    }

    public function isSupervisor(): bool
    {
        return $this->roleValue() === 'supervisor';
    }

    public function isResponsableProduction(): bool
    {
        return $this->roleValue() === 'responsable_production';
    }

    public function isRh(): bool
    {
        return $this->roleValue() === 'rh';
    }

    public function isOperator(): bool
    {
        return $this->roleValue() === 'operator';
    }

    public function canViewAbsences(): bool
    {
        return $this->isRh();
    }

    public function canManageAbsences(): bool
    {
        return $this->isRh();
    }

    public function canViewDashboard(): bool
    {
        return in_array($this->roleValue(), [
            'responsable_production',
            'supervisor',
            'admin',
        ], true);
    }

    public function canViewMachineStatus(): bool
    {
        return in_array($this->roleValue(), [
            'responsable_production',
            'supervisor',
            'admin',
        ], true);
    }

    public function canViewLineKpiBoard(): bool
    {
        return in_array($this->roleValue(), [
            'responsable_production',
            'supervisor',
            'admin',
        ], true);
    }

    public function canViewProductionPlanning(): bool
    {
        return in_array($this->roleValue(), [
            'operator',
            'responsable_production',
            'supervisor',
            'admin',
        ], true);
    }

    public function canManageProductionPlans(): bool
    {
        return in_array($this->roleValue(), [
            'responsable_production',
            'supervisor',
            'admin',
        ], true);
    }

    public function canManageProductionPlanning(): bool
    {
        return $this->canManageProductionPlans();
    }

    public function canCreateProductionEntries(): bool
    {
        return in_array($this->roleValue(), [
            'operator',
            'responsable_production',
            'supervisor',
            'admin',
        ], true);
    }

    public function canEditProductionEntries(): bool
    {
        return in_array($this->roleValue(), [
            'operator',
            'responsable_production',
            'supervisor',
            'admin',
        ], true);
    }

    public function canFinishProductionEntries(): bool
    {
        return in_array($this->roleValue(), [
            'operator',
            'responsable_production',
            'supervisor',
            'admin',
        ], true);
    }

    public function canApproveProductionEntries(): bool
    {
        return in_array($this->roleValue(), [
            'responsable_production',
            'supervisor',
            'admin',
        ], true);
    }

    public function canSendToThingsBoard(): bool
    {
        return in_array($this->roleValue(), [
            'responsable_production',
            'supervisor',
            'admin',
        ], true);
    }

    public function canViewMasterData(): bool
    {
        return in_array($this->roleValue(), [
            'responsable_production',
            'admin',
        ], true);
    }

    public function canManageMasterData(): bool
    {
        return in_array($this->roleValue(), [
            'responsable_production',
            'admin',
        ], true);
    }

    public function canManageUsers(): bool
    {
        return $this->isAdmin();
    }

    public function canDeleteProductionEntries(): bool
    {
        return $this->isAdmin();
    }

    public function assignedZoneIds(): array
    {
        if ($this->isAdmin() || $this->isResponsableProduction() || $this->isRh()) {
            return [];
        }

        if ($this->isOperator()) {
            $line = $this->productionLine;

            return $line && $line->zone_id
                ? [(int) $line->zone_id]
                : [];
        }

        if ($this->isSupervisor()) {
            $zoneIds = $this->assignedZones()
                ->pluck('zones.id')
                ->map(fn ($id) => (int) $id)
                ->toArray();

            if (empty($zoneIds) && $this->zone_id) {
                $zoneIds[] = (int) $this->zone_id;
            }

            return array_values(array_unique($zoneIds));
        }

        return [];
    }

    public function canAccessZone(?int $zoneId): bool
    {
        if ($this->isAdmin() || $this->isResponsableProduction() || $this->isRh()) {
            return true;
        }

        if (!$zoneId) {
            return false;
        }

        return in_array((int) $zoneId, $this->assignedZoneIds(), true);
    }

    public function canAccessProductionLine(?int $lineId): bool
    {
        if ($this->isAdmin() || $this->isResponsableProduction() || $this->isRh()) {
            return true;
        }

        if (!$lineId) {
            return false;
        }

        if ($this->isOperator()) {
            return (int) $this->production_line_id === (int) $lineId;
        }

        if ($this->isSupervisor()) {
            $line = ProductionLine::find($lineId);

            if (!$line) {
                return false;
            }

            return $this->canAccessZone((int) $line->zone_id);
        }

        return false;
    }

    public function scopeLabel(): string
    {
        if ($this->isAdmin()) {
            return 'All zones and lines';
        }

        if ($this->isRh()) {
            return 'Absences only (RH)';
        }

        if ($this->isResponsableProduction()) {
            return 'All zones and lines';
        }

        if ($this->isSupervisor()) {
            $zones = $this->assignedZones()->orderBy('code')->pluck('code')->toArray();

            if (empty($zones) && $this->zone) {
                $zones[] = $this->zone->code;
            }

            return empty($zones) ? 'No zone assigned' : 'Zones: ' . implode(', ', $zones);
        }

        if ($this->isOperator()) {
            return $this->productionLine
                ? 'Line: ' . $this->productionLine->code
                : 'No line assigned';
        }

        return '-';
    }
}
