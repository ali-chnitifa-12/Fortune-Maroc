<?php

namespace App\Http\Controllers;

use App\Models\ProductionDowntime;
use App\Models\ProductionLine;
use App\Models\Shift;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MachineStatusController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()?->canViewMachineStatus()) {
            abort(403);
        }

        $filters = [
            'date_from' => $request->get('date_from', now()->toDateString()),
            'date_to' => $request->get('date_to', now()->toDateString()),
            'zone_id' => $request->get('zone_id'),
            'production_line_id' => $request->get('production_line_id'),
            'shift_id' => $request->get('shift_id'),
        ];

        $query = ProductionDowntime::query()
            ->select([
                'machines.id as machine_id',
                'machines.code as machine_code',
                'machines.name as machine_name',
                'zones.code as zone_code',
                'zones.name as zone_name',
                'production_lines.code as line_code',
                'production_lines.name as line_name',
                DB::raw('COUNT(production_downtimes.id) as stops_count'),
                DB::raw('SUM(COALESCE(production_downtimes.duration_min, 0)) as total_downtime_min'),
                DB::raw('MAX(CASE WHEN production_downtimes.ended_at IS NULL THEN 1 ELSE 0 END) as has_open_stop'),
                DB::raw('MAX(production_downtimes.started_at) as last_stop_started_at'),
                DB::raw('MAX(production_downtimes.ended_at) as last_stop_ended_at'),
            ])
            ->join('machines', 'machines.id', '=', 'production_downtimes.machine_id')
            ->leftJoin('production_plans', 'production_plans.id', '=', 'production_downtimes.production_plan_id')
            ->leftJoin('zones', 'zones.id', '=', 'production_plans.zone_id')
            ->leftJoin('production_lines', 'production_lines.id', '=', 'production_plans.production_line_id')
            ->whereNotNull('production_downtimes.production_plan_id')
            ->groupBy([
                'machines.id',
                'machines.code',
                'machines.name',
                'zones.code',
                'zones.name',
                'production_lines.code',
                'production_lines.name',
            ]);

        $this->applyUserScope($query);

        if (!empty($filters['date_from'])) {
            $query->whereDate('production_plans.plan_date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('production_plans.plan_date', '<=', $filters['date_to']);
        }

        if (!empty($filters['zone_id']) && auth()->user()->canAccessZone((int) $filters['zone_id'])) {
            $query->where('production_plans.zone_id', $filters['zone_id']);
        }

        if (!empty($filters['production_line_id']) && auth()->user()->canAccessProductionLine((int) $filters['production_line_id'])) {
            $query->where('production_plans.production_line_id', $filters['production_line_id']);
        }

        if (!empty($filters['shift_id'])) {
            $query->where('production_plans.shift_id', $filters['shift_id']);
        }

        $rows = $query
            ->orderBy('zones.code')
            ->orderBy('production_lines.code')
            ->orderBy('machines.code')
            ->get();

        $openStops = ProductionDowntime::query()
            ->with([
                'machine',
                'productionPlan.zone',
                'productionPlan.productionLine',
                'productionPlan.shift',
                'downtimeCategory',
                'downtimeReason',
            ])
            ->whereNull('ended_at')
            ->whereNotNull('production_plan_id');

        $this->applyUserScope($openStops);

        if (!empty($filters['date_from'])) {
            $openStops->whereHas('productionPlan', function ($query) use ($filters) {
                $query->whereDate('plan_date', '>=', $filters['date_from']);
            });
        }

        if (!empty($filters['date_to'])) {
            $openStops->whereHas('productionPlan', function ($query) use ($filters) {
                $query->whereDate('plan_date', '<=', $filters['date_to']);
            });
        }

        if (!empty($filters['zone_id']) && auth()->user()->canAccessZone((int) $filters['zone_id'])) {
            $openStops->whereHas('productionPlan', function ($query) use ($filters) {
                $query->where('zone_id', $filters['zone_id']);
            });
        }

        if (!empty($filters['production_line_id']) && auth()->user()->canAccessProductionLine((int) $filters['production_line_id'])) {
            $openStops->whereHas('productionPlan', function ($query) use ($filters) {
                $query->where('production_line_id', $filters['production_line_id']);
            });
        }

        if (!empty($filters['shift_id'])) {
            $openStops->whereHas('productionPlan', function ($query) use ($filters) {
                $query->where('shift_id', $filters['shift_id']);
            });
        }

        return view('machine-status.index', [
            'rows' => $rows,
            'openStops' => $openStops->latest('started_at')->get(),
            'zones' => $this->visibleZones(),
            'productionLines' => $this->visibleProductionLines(),
            'shifts' => Shift::where('is_active', true)->orderBy('start_time')->get(),
            'filters' => $filters,
            'summary' => [
                'machines_with_stops' => $rows->count(),
                'total_stops' => (int) $rows->sum('stops_count'),
                'total_downtime_min' => (int) $rows->sum('total_downtime_min'),
                'open_stops' => (int) $rows->sum('has_open_stop'),
            ],
        ]);
    }

    private function applyUserScope($query): void
    {
        $user = auth()->user();

        if (!$user) {
            $query->whereRaw('1 = 0');
            return;
        }

        if ($user->isAdmin() || $user->isResponsableProduction()) {
            return;
        }

        if ($user->isOperator()) {
            if ($query->getModel() instanceof ProductionDowntime) {
                $query->whereHas('productionPlan', function ($subQuery) use ($user) {
                    $subQuery->where('production_line_id', $user->production_line_id ?: 0);
                });
            } else {
                $query->where('production_plans.production_line_id', $user->production_line_id ?: 0);
            }

            return;
        }

        if ($user->isSupervisor()) {
            $zoneIds = $user->assignedZoneIds();

            if (empty($zoneIds)) {
                $query->whereRaw('1 = 0');
                return;
            }

            if ($query->getModel() instanceof ProductionDowntime) {
                $query->whereHas('productionPlan', function ($subQuery) use ($zoneIds) {
                    $subQuery->whereIn('zone_id', $zoneIds);
                });
            } else {
                $query->whereIn('production_plans.zone_id', $zoneIds);
            }

            return;
        }

        $query->whereRaw('1 = 0');
    }

    private function visibleZones()
    {
        $user = auth()->user();

        $query = Zone::where('is_active', true)->orderBy('code');

        if (!$user) {
            return collect();
        }

        if ($user->isAdmin() || $user->isResponsableProduction()) {
            return $query->get();
        }

        if ($user->isOperator() && $user->productionLine) {
            return $query->where('id', $user->productionLine->zone_id)->get();
        }

        if ($user->isSupervisor()) {
            $zoneIds = $user->assignedZoneIds();

            return empty($zoneIds)
                ? collect()
                : $query->whereIn('id', $zoneIds)->get();
        }

        return collect();
    }

    private function visibleProductionLines()
    {
        $user = auth()->user();

        $query = ProductionLine::where('is_active', true)->orderBy('code');

        if (!$user) {
            return collect();
        }

        if ($user->isAdmin() || $user->isResponsableProduction()) {
            return $query->get();
        }

        if ($user->isOperator()) {
            return $query->where('id', $user->production_line_id ?: 0)->get();
        }

        if ($user->isSupervisor()) {
            $zoneIds = $user->assignedZoneIds();

            return empty($zoneIds)
                ? collect()
                : $query->whereIn('zone_id', $zoneIds)->get();
        }

        return collect();
    }
}