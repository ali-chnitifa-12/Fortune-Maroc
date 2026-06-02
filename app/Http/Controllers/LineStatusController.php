<?php

namespace App\Http\Controllers;

use App\Models\ProductionEntry;
use App\Models\ProductionLine;
use App\Models\Zone;
use Illuminate\Http\Request;

class LineStatusController extends Controller
{
    public function index(Request $request)
    {
        $selectedDate = $request->input('date', now()->toDateString());
        $selectedZoneId = $request->input('zone_id');

        $linesQuery = ProductionLine::with([
                'zone',
                'machines',
                'products',
            ])
            ->where('is_active', true);

        if ($selectedZoneId) {
            $linesQuery->where('zone_id', $selectedZoneId);
        }

        $lines = $linesQuery
            ->orderBy('code')
            ->get();

        $lineCards = $lines->map(function (ProductionLine $line) use ($selectedDate) {
            $entries = ProductionEntry::with([
                    'shift',
                    'product',
                    'downtimes',
                ])
                ->where('production_line_id', $line->id)
                ->whereDate('production_date', $selectedDate)
                ->orderBy('hour_start')
                ->get();

            $plannedQty = (float) $entries->sum('planned_qty');
            $actualQty = (float) $entries->sum('actual_qty');
            $goodQty = (float) $entries->sum('good_qty');
            $rejectedQty = (float) $entries->sum('rejected_qty');
            $stopMin = (int) $entries->sum('stop_duration_min');
            $stopsCount = (int) $entries->sum('stops_count');

            $availability = round((float) ($entries->avg('availability') ?? 0), 2);
            $performance = round((float) ($entries->avg('performance') ?? 0), 2);
            $quality = round((float) ($entries->avg('quality') ?? 0), 2);
            $oee = round((float) ($entries->avg('oee') ?? 0), 2);

            return [
                'line' => $line,
                'zone' => $line->zone,
                'entries' => $entries,
                'plannedQty' => round($plannedQty, 2),
                'actualQty' => round($actualQty, 2),
                'goodQty' => round($goodQty, 2),
                'rejectedQty' => round($rejectedQty, 2),
                'stopMin' => $stopMin,
                'stopsCount' => $stopsCount,
                'availability' => $availability,
                'performance' => $performance,
                'quality' => $quality,
                'oee' => $oee,
            ];
        });

        $stats = [
            'lines' => $lineCards->count(),
            'plannedQty' => round((float) $lineCards->sum('plannedQty'), 2),
            'actualQty' => round((float) $lineCards->sum('actualQty'), 2),
            'goodQty' => round((float) $lineCards->sum('goodQty'), 2),
            'rejectedQty' => round((float) $lineCards->sum('rejectedQty'), 2),
            'stopMin' => (int) $lineCards->sum('stopMin'),
            'stopsCount' => (int) $lineCards->sum('stopsCount'),
            'oee' => round((float) $lineCards->avg('oee'), 2),
        ];

        return view('line-status.index', [
            'selectedDate' => $selectedDate,
            'selectedZoneId' => $selectedZoneId,
            'zones' => Zone::where('is_active', true)->orderBy('code')->get(),
            'lineCards' => $lineCards,
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
            $query->whereIn('zone_id', $zoneIds);
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
}