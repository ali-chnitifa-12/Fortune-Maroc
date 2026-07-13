<?php

namespace App\Http\Controllers;

use App\Models\ProductionEntry;
use App\Models\ProductionLine;
use App\Models\ProductionPlan;
use App\Models\Shift;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LineStatusController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()?->canViewLineKpiBoard()) {
            abort(403);
        }

        $filters = [
            'date_from' => $request->get('date_from', now()->toDateString()),
            'date_to' => $request->get('date_to', now()->toDateString()),
            'zone_id' => $request->get('zone_id'),
            'production_line_id' => $request->get('production_line_id'),
            'shift_id' => $request->get('shift_id'),
        ];

        $query = ProductionPlan::query()
            ->with([
                'zone',
                'productionLine',
                'shift',
                'product',
                'entries',
                'downtimes',
            ])
            ->select('production_plans.*');

        $this->applyUserScope($query);

        if (!empty($filters['date_from'])) {
            $query->whereDate('plan_date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('plan_date', '<=', $filters['date_to']);
        }

        if (!empty($filters['zone_id']) && auth()->user()->canAccessZone((int) $filters['zone_id'])) {
            $query->where('zone_id', $filters['zone_id']);
        }

        if (!empty($filters['production_line_id']) && auth()->user()->canAccessProductionLine((int) $filters['production_line_id'])) {
            $query->where('production_line_id', $filters['production_line_id']);
        }

        if (!empty($filters['shift_id'])) {
            $query->where('shift_id', $filters['shift_id']);
        }

        $plans = $query
            ->orderByDesc('plan_date')
            ->orderBy('zone_id')
            ->orderBy('production_line_id')
            ->orderBy('shift_id')
            ->get();

        $rows = $plans->map(function (ProductionPlan $plan) {
            $entries = $plan->entries;

            $plannedQty = (float) $plan->planned_qty;
            $actualQty = (float) $entries->sum('actual_qty');
            $goodQty = (float) $entries->sum('good_qty');
            $rejectedQty = (float) $entries->sum('rejected_qty');
            $chute1Qty = (float) $entries->sum('chute_1_qty');
            $chute2Qty = (float) $entries->sum('chute_2_qty');
            $chute3Qty = (float) $entries->sum('chute_3_qty');

            $stopsCount = (int) $plan->downtimes->count();
            $downtimeMin = (int) $plan->downtimes->sum('duration_min');
            $shiftMinutes = $this->minutesBetween($plan->hour_start, $plan->hour_end);

            $availability = $shiftMinutes > 0
                ? max(0, min(100, (($shiftMinutes - $downtimeMin) / $shiftMinutes) * 100))
                : 0;

            $performance = $plannedQty > 0
                ? ($actualQty / $plannedQty) * 100
                : 0;

            $quality = $actualQty > 0
                ? ($goodQty / $actualQty) * 100
                : 0;

            $oee = ($availability * $performance * $quality) / 10000;

            $sentCount = $entries->where('entry_status', 'sent_to_thingsboard')->count();
            $finishedCount = $entries->where('entry_status', 'finished')->count();
            $draftCount = $entries->where('entry_status', 'draft')->count();

            return [
                'plan' => $plan,
                'planned_qty' => round($plannedQty, 2),
                'actual_qty' => round($actualQty, 2),
                'good_qty' => round($goodQty, 2),
                'rejected_qty' => round($rejectedQty, 2),
                'chute_1_qty' => round($chute1Qty, 2),
                'chute_2_qty' => round($chute2Qty, 2),
                'chute_3_qty' => round($chute3Qty, 2),
                'stops_count' => $stopsCount,
                'downtime_min' => $downtimeMin,
                'shift_minutes' => $shiftMinutes,
                'availability' => round($availability, 2),
                'performance' => round($performance, 2),
                'quality' => round($quality, 2),
                'oee' => round($oee, 2),
                'entries_count' => $entries->count(),
                'draft_count' => $draftCount,
                'finished_count' => $finishedCount,
                'sent_count' => $sentCount,
                'open_entry' => $entries->sortBy('hour_start')->first(),
            ];
        });

        $summary = [
            'plans' => $rows->count(),
            'entries' => (int) $rows->sum('entries_count'),
            'planned_qty' => round((float) $rows->sum('planned_qty'), 2),
            'actual_qty' => round((float) $rows->sum('actual_qty'), 2),
            'good_qty' => round((float) $rows->sum('good_qty'), 2),
            'rejected_qty' => round((float) $rows->sum('rejected_qty'), 2),
            'chute_1_qty' => round((float) $rows->sum('chute_1_qty'), 2),
            'chute_2_qty' => round((float) $rows->sum('chute_2_qty'), 2),
            'chute_3_qty' => round((float) $rows->sum('chute_3_qty'), 2),
            'stops_count' => (int) $rows->sum('stops_count'),
            'downtime_min' => (int) $rows->sum('downtime_min'),
            'average_oee' => $rows->count() > 0 ? round((float) $rows->avg('oee'), 2) : 0,
        ];

        return view('line-status.index', [
            'rows' => $rows,
            'summary' => $summary,
            'zones' => $this->visibleZones(),
            'productionLines' => $this->visibleProductionLines(),
            'shifts' => Shift::where('is_active', true)->orderBy('start_time')->get(),
            'filters' => $filters,
        ]);
    }

    private function minutesBetween($startTime, $endTime): int
    {
        if (!$startTime || !$endTime) {
            return 0;
        }

        $start = \Carbon\Carbon::createFromFormat('H:i:s', strlen((string) $startTime) === 5 ? $startTime . ':00' : (string) $startTime);
        $end = \Carbon\Carbon::createFromFormat('H:i:s', strlen((string) $endTime) === 5 ? $endTime . ':00' : (string) $endTime);

        if ($end->lessThanOrEqualTo($start)) {
            $end->addDay();
        }

        return max(1, $start->diffInMinutes($end));
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
            $query->where('production_line_id', $user->production_line_id ?: 0);
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