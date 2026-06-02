<?php

namespace App\Http\Controllers;

use App\Models\Machine;
use App\Models\ProductionDowntime;
use App\Models\ProductionLine;
use App\Models\Zone;
use Illuminate\Http\Request;

class MachineStatusController extends Controller
{
    public function index(Request $request)
    {
        $selectedDate = $request->input('date', now()->toDateString());
        $selectedZoneId = $request->input('zone_id');
        $selectedLineId = $request->input('production_line_id');

        $machinesQuery = Machine::with([
                'productionLine.zone',
            ])
            ->where('is_active', true)
            ->whereNotNull('production_line_id');

        if ($selectedLineId) {
            $machinesQuery->where('production_line_id', $selectedLineId);
        }

        if ($selectedZoneId) {
            $machinesQuery->whereHas('productionLine', function ($query) use ($selectedZoneId) {
                $query->where('zone_id', $selectedZoneId);
            });
        }

        $machines = $machinesQuery
            ->orderBy('code')
            ->get();

        $machineCards = $machines->map(function (Machine $machine) use ($selectedDate) {
            $downtimes = ProductionDowntime::with([
                    'productionEntry',
                    'machine',
                    'downtimeCategory',
                    'downtimeReason',
                ])
                ->where('machine_id', $machine->id)
                ->whereHas('productionEntry', function ($query) use ($selectedDate) {
                    $query->whereDate('production_date', $selectedDate);
                })
                ->orderByDesc('started_at')
                ->get();

            $openDowntime = $downtimes
                ->whereNull('ended_at')
                ->first();

            $totalStopMin = (int) $downtimes->sum('duration_min');
            $stopsCount = (int) $downtimes->count();

            $categories = $downtimes
                ->filter(fn ($downtime) => $downtime->downtimeCategory)
                ->groupBy(fn ($downtime) => $downtime->downtimeCategory->name)
                ->map(fn ($items, $name) => [
                    'name' => $name,
                    'count' => $items->count(),
                    'duration' => (int) $items->sum('duration_min'),
                ])
                ->sortByDesc('duration')
                ->values();

            $reasons = $downtimes
                ->filter(fn ($downtime) => $downtime->downtimeReason)
                ->groupBy(fn ($downtime) => $downtime->downtimeReason->name)
                ->map(fn ($items, $name) => [
                    'name' => $name,
                    'count' => $items->count(),
                    'duration' => (int) $items->sum('duration_min'),
                ])
                ->sortByDesc('duration')
                ->values();

            return [
                'machine' => $machine,
                'zone' => $machine->productionLine?->zone,
                'line' => $machine->productionLine,
                'status' => $openDowntime ? 'in_repair' : 'active',
                'openDowntime' => $openDowntime,
                'downtimes' => $downtimes,
                'stopsCount' => $stopsCount,
                'totalStopMin' => $totalStopMin,
                'categories' => $categories,
                'reasons' => $reasons,
            ];
        });

        $stats = [
            'machines' => $machineCards->count(),
            'active' => $machineCards->where('status', 'active')->count(),
            'in_repair' => $machineCards->where('status', 'in_repair')->count(),
            'total_stops' => $machineCards->sum('stopsCount'),
            'total_stop_min' => $machineCards->sum('totalStopMin'),
        ];

        return view('machine-status.index', [
            'selectedDate' => $selectedDate,
            'selectedZoneId' => $selectedZoneId,
            'selectedLineId' => $selectedLineId,
            'zones' => Zone::where('is_active', true)->orderBy('code')->get(),
            'productionLines' => ProductionLine::with('zone')
                ->where('is_active', true)
                ->orderBy('code')
                ->get(),
            'machineCards' => $machineCards,
            'stats' => $stats,
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
        $query->whereRaw('1 = 0');
        return;
    }

    if ($user->isSupervisor()) {
        $zoneIds = $user->assignedZoneIds();

        if (empty($zoneIds)) {
            $query->whereRaw('1 = 0');
        } else {
            $query->whereHas('productionLine', function ($lineQuery) use ($zoneIds) {
                $lineQuery->whereIn('zone_id', $zoneIds);
            });
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

    if ($user->isSupervisor()) {
        $zoneIds = $user->assignedZoneIds();

        return empty($zoneIds)
            ? collect()
            : $query->whereIn('zone_id', $zoneIds)->get();
    }

    return collect();
}
}