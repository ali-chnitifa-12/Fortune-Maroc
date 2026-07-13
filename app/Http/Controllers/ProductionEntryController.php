<?php

namespace App\Http\Controllers;

use App\Models\DowntimeCategory;
use App\Models\DowntimeReason;
use App\Models\Machine;
use App\Models\Product;
use App\Models\ProductionDowntime;
use App\Models\ProductionEntry;
use App\Models\ProductionLine;
use App\Models\ProductionPlan;
use App\Models\Shift;
use App\Models\ThingsboardDevice;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProductionEntryController extends Controller
{
    public function index(Request $request)
{
    $today = now()->toDateString();

    $filters = [
        'date_from' => $request->get('date_from', $today),
        'date_to' => $request->get('date_to', $today),
        'zone_id' => $request->get('zone_id'),
        'production_line_id' => $request->get('production_line_id'),
        'product_id' => $request->get('product_id'),
        'shift_id' => $request->get('shift_id'),
        'entry_status' => $request->get('entry_status'),
        'sort' => $request->get('sort'),
        'direction' => $request->get('direction'),
    ];

    $query = ProductionEntry::query()
        ->select('production_entries.*')
        ->with([
            'zone',
            'productionLine',
            'shift',
            'product',
            'approver',
            'productionPlan',
        ])
        ->leftJoin('zones', 'zones.id', '=', 'production_entries.zone_id')
        ->leftJoin('production_lines', 'production_lines.id', '=', 'production_entries.production_line_id')
        ->leftJoin('shifts', 'shifts.id', '=', 'production_entries.shift_id')
        ->leftJoin('products', 'products.id', '=', 'production_entries.product_id')
        ->leftJoin('production_plans', 'production_plans.id', '=', 'production_entries.production_plan_id');

    $this->applyUserScope($query);

    $query->whereDate('production_entries.production_date', '>=', $filters['date_from']);
    $query->whereDate('production_entries.production_date', '<=', $filters['date_to']);

    if (!empty($filters['zone_id']) && auth()->user()->canAccessZone((int) $filters['zone_id'])) {
        $query->where('production_entries.zone_id', $filters['zone_id']);
    }

    if (!empty($filters['production_line_id']) && auth()->user()->canAccessProductionLine((int) $filters['production_line_id'])) {
        $query->where('production_entries.production_line_id', $filters['production_line_id']);
    }

    if (!empty($filters['product_id'])) {
        $query->where('production_entries.product_id', $filters['product_id']);
    }

    if (!empty($filters['shift_id'])) {
        $query->where('production_entries.shift_id', $filters['shift_id']);
    }

    if (!empty($filters['entry_status'])) {
        $query->where('production_entries.entry_status', $filters['entry_status']);
    }

    $this->applySorting($query, $request);

    $entries = $query
        ->paginate(20)
        ->withQueryString();

    return view('production-entries.index', [
        'entries' => $entries,
        'zones' => $this->visibleZones(),
        'productionLines' => $this->visibleProductionLines(),
        'products' => Product::where('is_active', true)->orderBy('code')->get(),
        'shifts' => Shift::where('is_active', true)->orderBy('start_time')->get(),
        'filters' => $filters,
    ]);
}

    private function applySorting($query, Request $request): void
    {
        $allowedSorts = [
            'entry_code' => 'production_entries.entry_code',
            'plan_code' => 'production_plans.plan_code',
            'production_date' => 'production_entries.production_date',
            'hour' => 'production_entries.hour_start',
            'shift' => 'shifts.code',
            'zone' => 'zones.code',
            'line' => 'production_lines.code',
            'product' => 'products.code',
            'planned_qty' => 'production_entries.planned_qty',
            'actual_qty' => 'production_entries.actual_qty',
            'good_qty' => 'production_entries.good_qty',
            'rejected_qty' => 'production_entries.rejected_qty',
            'chute_1_qty' => 'production_entries.chute_1_qty',
            'chute_2_qty' => 'production_entries.chute_2_qty',
            'chute_3_qty' => 'production_entries.chute_3_qty',
            'stop_duration_min' => 'production_entries.stop_duration_min',
            'oee' => 'production_entries.oee',
            'entry_status' => 'production_entries.entry_status',
            'created_at' => 'production_entries.created_at',
        ];

        $sort = $request->get('sort', 'production_date');
        $direction = strtolower($request->get('direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        if (!array_key_exists($sort, $allowedSorts)) {
            $sort = 'production_date';
            $direction = 'desc';
        }

        $query->orderBy($allowedSorts[$sort], $direction);

        if ($sort !== 'hour') {
            $query->orderBy('production_entries.hour_start', 'desc');
        }

        $query->orderBy('production_entries.id', 'desc');
    }

    public function create()
    {
        return redirect()->route('production-plans.index')
            ->withErrors([
                'entry' => 'Production entries are generated automatically from production plans.',
            ]);
    }

    public function store(Request $request)
    {
        return redirect()->route('production-plans.index')
            ->withErrors([
                'entry' => 'Production entries are generated automatically from production plans.',
            ]);
    }

    public function createFromPlan(ProductionPlan $production_plan)
    {
        return redirect()->route('production-plans.index')
            ->withErrors([
                'entry' => 'Entries are generated automatically when creating a production plan.',
            ]);
    }

    public function edit(ProductionEntry $production_entry)
    {
        $this->ensureUserCanAccessEntry($production_entry);

        $production_entry->load([
            'zone',
            'productionLine.machines',
            'shift',
            'product',
            'productionPlan.downtimes.machine',
            'productionPlan.downtimes.downtimeCategory',
            'productionPlan.downtimes.downtimeReason',
            'downtimes.machine',
            'downtimes.downtimeCategory',
            'downtimes.downtimeReason',
        ]);

        return view('production-entries.edit', [
            'entry' => $production_entry,
            'plan' => $production_entry->productionPlan,
            'lineMachines' => $production_entry->productionLine
                ? $production_entry->productionLine->machines()->where('is_active', true)->orderBy('code')->get()
                : collect(),
            'downtimeCategories' => DowntimeCategory::where('is_active', true)->orderBy('name')->get(),
            'downtimeReasons' => DowntimeReason::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, ProductionEntry $production_entry)
    {
        $this->ensureUserCanAccessEntry($production_entry);

        if (!auth()->user()?->canEditProductionEntries()) {
            abort(403, 'You are not allowed to update production entries.');
        }

        if ($production_entry->entry_status !== 'draft') {
            return back()->withErrors([
                'entry' => 'Only draft entries can be updated.',
            ]);
        }

        $data = $request->validate([
            'actual_qty' => ['required', 'numeric', 'min:0.01'],
            'rejected_qty' => ['required', 'numeric', 'min:0'],
            'chute_1_qty' => ['nullable', 'numeric', 'min:0'],
            'chute_2_qty' => ['nullable', 'numeric', 'min:0'],
            'chute_3_qty' => ['nullable', 'numeric', 'min:0'],
            'comment' => ['nullable', 'string'],
        ]);

        if ((float) $data['rejected_qty'] > (float) $data['actual_qty']) {
            return back()->withInput()->withErrors([
                'rejected_qty' => 'Rejected Qty cannot be greater than Actual Qty.',
            ]);
        }

        $data['chute_1_qty'] = $data['chute_1_qty'] ?? 0;
        $data['chute_2_qty'] = $data['chute_2_qty'] ?? 0;
        $data['chute_3_qty'] = $data['chute_3_qty'] ?? 0;

        $production_entry->fill($data);

        $this->calculateKpis($production_entry);
        $production_entry->save();

        $this->syncAllPlanEntriesKpis($production_entry->productionPlan);
        $this->syncPlanStatusFromEntries($production_entry->productionPlan);

        return back()->with('success', 'Production entry updated successfully.');
    }

    public function stopMachine(Request $request, ProductionEntry $production_entry)
    {
        $this->ensureUserCanAccessEntry($production_entry);

        if (!auth()->user()?->canEditProductionEntries()) {
            abort(403, 'You are not allowed to declare machine stops.');
        }

        if (!$production_entry->production_plan_id) {
            return back()->withErrors([
                'downtime' => 'Production entry is not linked to a production plan.',
            ]);
        }

        $data = $request->validate([
            'machine_id' => ['required', 'exists:machines,id'],
        ]);

        $machineBelongsToLine = Machine::where('id', $data['machine_id'])
            ->where('production_line_id', $production_entry->production_line_id)
            ->exists();

        if (!$machineBelongsToLine) {
            return back()->withErrors([
                'machine_id' => 'Selected machine does not belong to this production line.',
            ]);
        }

        $openDowntime = ProductionDowntime::where('production_plan_id', $production_entry->production_plan_id)
            ->whereNull('ended_at')
            ->first();

        if ($openDowntime) {
            return back()->withErrors([
                'machine_id' => 'There is already an open machine stop for this shift. Fix it first.',
            ]);
        }

        $downtime = DB::transaction(function () use ($production_entry, $data) {
            $production_entry->productionPlan?->update([
                'status' => 'in_progress',
            ]);

            return ProductionDowntime::create([
                'production_plan_id' => $production_entry->production_plan_id,
                'production_entry_id' => null,
                'machine_id' => $data['machine_id'],
                'started_at' => now(),
                'ended_at' => null,
                'duration_min' => 0,
                'created_by' => auth()->id(),
            ]);
        });

        $this->sendMachineStopTelemetry($production_entry->fresh(), $downtime->fresh());

        return redirect()
            ->route('production-entries.edit', ['production_entry' => $production_entry->id, 'tab' => 'downtime'])
            ->with('success', 'Machine stop declared for the shift.');
    }

    public function fixedMachine(ProductionEntry $production_entry)
    {
        $this->ensureUserCanAccessEntry($production_entry);

        if (!auth()->user()?->canEditProductionEntries()) {
            abort(403, 'You are not allowed to fix machine stops.');
        }

        if (!$production_entry->production_plan_id) {
            return back()->withErrors([
                'downtime' => 'Production entry is not linked to a production plan.',
            ]);
        }

        $openDowntime = ProductionDowntime::where('production_plan_id', $production_entry->production_plan_id)
            ->whereNull('ended_at')
            ->latest('started_at')
            ->first();

        if (!$openDowntime) {
            return back()->withErrors([
                'downtime' => 'No open machine stop found for this shift.',
            ]);
        }

        $downtime = DB::transaction(function () use ($openDowntime) {
            $endedAt = now();
            $duration = max(1, $openDowntime->started_at->diffInMinutes($endedAt));

            $openDowntime->update([
                'ended_at' => $endedAt,
                'duration_min' => $duration,
            ]);

            return $openDowntime->fresh();
        });

        $freshEntry = $production_entry->fresh();
        $this->syncAllPlanEntriesKpis($freshEntry->productionPlan);
        $this->sendMachineFixedTelemetry($freshEntry, $downtime);

        return redirect()
            ->route('production-entries.edit', ['production_entry' => $production_entry->id, 'tab' => 'downtime'])
            ->with('success', 'Machine fixed for the shift.');
    }

    public function finishEntry(ProductionEntry $production_entry)
    {
        $this->ensureUserCanAccessEntry($production_entry);

        if (!auth()->user()?->canFinishProductionEntries()) {
            abort(403, 'You are not allowed to finish production entries.');
        }

        if ($production_entry->entry_status !== 'draft') {
            return back()->withErrors([
                'entry' => 'Only draft entries can be finished.',
            ]);
        }

        $openDowntime = ProductionDowntime::where('production_plan_id', $production_entry->production_plan_id)
            ->whereNull('ended_at')
            ->exists();

        if ($openDowntime) {
            return redirect()
                ->route('production-entries.edit', ['production_entry' => $production_entry->id, 'tab' => 'downtime'])
                ->withErrors([
                    'downtime' => 'You must fix the stopped machine before finishing the entry.',
                ]);
        }

        $invalidDowntime = ProductionDowntime::where('production_plan_id', $production_entry->production_plan_id)
            ->whereNotNull('ended_at')
            ->where(function ($query) {
                $query->whereNull('downtime_category_id')
                    ->orWhereNull('downtime_reason_id');
            })
            ->exists();

        if ($invalidDowntime) {
            return redirect()
                ->route('production-entries.edit', ['production_entry' => $production_entry->id, 'tab' => 'downtime'])
                ->withErrors([
                    'downtime' => 'Downtime category and reason are mandatory before finishing entries.',
                ]);
        }

        if ((float) $production_entry->actual_qty <= 0) {
            return redirect()
                ->route('production-entries.edit', ['production_entry' => $production_entry->id, 'tab' => 'production'])
                ->withErrors([
                    'actual_qty' => 'Actual Qty is required before finishing the entry.',
                ]);
        }

        if ((float) $production_entry->rejected_qty > (float) $production_entry->actual_qty) {
            return redirect()
                ->route('production-entries.edit', ['production_entry' => $production_entry->id, 'tab' => 'production'])
                ->withErrors([
                    'rejected_qty' => 'Rejected Qty cannot be greater than Actual Qty.',
                ]);
        }

        $this->calculateKpis($production_entry);

        $production_entry->update([
            'entry_status' => 'finished',
            'completed_at' => now(),
            'machine_status' => 'active',
            'availability' => $production_entry->availability,
            'performance' => $production_entry->performance,
            'quality' => $production_entry->quality,
            'oee' => $production_entry->oee,
            'good_qty' => $production_entry->good_qty,
        ]);

        $this->syncAllPlanEntriesKpis($production_entry->productionPlan);
        $this->syncPlanStatusFromEntries($production_entry->productionPlan);

        return redirect()->route('production-entries.index')
            ->with('success', 'Production entry finished successfully. Waiting for Responsable Production approval.');
    }

    public function approveEntry(ProductionEntry $production_entry)
    {
        $this->ensureUserCanAccessEntry($production_entry);

        if (!auth()->user()?->canApproveProductionEntries()) {
            abort(403, 'You are not allowed to approve production entries.');
        }

        if ($production_entry->entry_status !== 'finished') {
            return back()->withErrors([
                'entry' => 'Only finished entries can be approved and sent to ThingsBoard.',
            ]);
        }

        $production_entry->load([
            'zone',
            'productionLine',
            'shift',
            'product',
            'approver',
            'productionPlan.downtimes.machine',
            'productionPlan.downtimes.downtimeCategory',
            'productionPlan.downtimes.downtimeReason',
        ]);

        $mapping = ThingsboardDevice::where('mapping_type', 'line')
            ->where('production_line_id', $production_entry->production_line_id)
            ->where('is_active', true)
            ->first();

        if (!$mapping) {
            return back()->withErrors([
                'thingsboard' => 'No active ThingsBoard line mapping found for this production line.',
            ]);
        }

        $this->calculateKpis($production_entry);
        $production_entry->save();

        $entryPayload = $this->buildLineTelemetryPayload($production_entry);
        $entryTbResult = $this->sendTelemetryToThingsBoard($mapping->access_token, $entryPayload);

        if (!$entryTbResult['success']) {
            return back()->withErrors([
                'thingsboard' => 'Approval blocked. ThingsBoard sending failed: ' . $entryTbResult['message'],
            ]);
        }

        DB::transaction(function () use ($production_entry, $mapping, $entryPayload, $entryTbResult) {
            $production_entry->update([
                'entry_status' => 'sent_to_thingsboard',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'sent_to_thingsboard' => true,
                'thingsboard_response' => json_encode([
                    'mapping_id' => $mapping->id,
                    'device_name' => $mapping->device_name,
                    'payload' => $entryPayload,
                    'response' => $entryTbResult,
                ]),
            ]);
        });

        $freshEntry = $production_entry->fresh();
        $plan = $freshEntry->productionPlan;

        $this->syncAllPlanEntriesKpis($plan);
        $this->syncPlanStatusFromEntries($plan);

        if ($this->isPlanFullySent($plan)) {
            $summaryPayload = $this->buildShiftSummaryTelemetryPayload($plan);
            $summaryResult = $this->sendTelemetryToThingsBoard($mapping->access_token, $summaryPayload);

            if (!$summaryResult['success']) {
                Log::error('ThingsBoard shift summary telemetry failed', [
                    'plan_id' => $plan?->id,
                    'plan_code' => $plan?->plan_code,
                    'result' => $summaryResult,
                    'payload' => $summaryPayload,
                ]);
            }
        }

        return redirect()->route('production-entries.index')
            ->with('success', 'Production entry approved and sent to ThingsBoard successfully.');
    }

    public function destroy(ProductionEntry $production_entry)
    {
        $this->ensureUserCanAccessEntry($production_entry);

        if (!auth()->user()?->canDeleteProductionEntries()) {
            abort(403, 'You are not allowed to delete production entries.');
        }

        if ($production_entry->sent_to_thingsboard) {
            return back()->withErrors([
                'entry' => 'Sent entries cannot be deleted.',
            ]);
        }

        DB::transaction(function () use ($production_entry) {
            $plan = $production_entry->productionPlan;

            $production_entry->delete();

            $this->syncPlanStatusFromEntries($plan);
        });

        return redirect()->route('production-entries.index')
            ->with('success', 'Production entry deleted successfully.');
    }

    private function calculateKpis(ProductionEntry $entry): void
    {
        $plannedQty = (float) $entry->planned_qty;
        $actualQty = (float) $entry->actual_qty;
        $rejectedQty = (float) $entry->rejected_qty;

        $planDowntimeMin = 0;
        $shiftDurationMin = 60;

        if ($entry->production_plan_id) {
            $plan = $entry->productionPlan;

            if ($plan && $plan->hour_start && $plan->hour_end) {
                $shiftDurationMin = $this->minutesBetween($plan->hour_start, $plan->hour_end);
            }

            $planDowntimeMin = ProductionDowntime::where('production_plan_id', $entry->production_plan_id)
                ->whereNotNull('ended_at')
                ->sum('duration_min');
        }

        $goodQty = max(0, $actualQty - $rejectedQty);

        $availability = $shiftDurationMin > 0
            ? max(0, min(100, (($shiftDurationMin - $planDowntimeMin) / $shiftDurationMin) * 100))
            : 100;

        $performance = $plannedQty > 0 ? ($actualQty / $plannedQty) * 100 : 0;
        $quality = $actualQty > 0 ? ($goodQty / $actualQty) * 100 : 0;
        $oee = ($availability * $performance * $quality) / 10000;

        $entry->good_qty = round($goodQty, 2);
        $entry->stop_duration_min = (int) $planDowntimeMin;
        $entry->stops_count = (int) ProductionDowntime::where('production_plan_id', $entry->production_plan_id)->count();
        $entry->availability = round($availability, 2);
        $entry->performance = round($performance, 2);
        $entry->quality = round($quality, 2);
        $entry->oee = round($oee, 2);
    }

    private function syncAllPlanEntriesKpis(?ProductionPlan $plan): void
    {
        if (!$plan) {
            return;
        }

        $entries = $plan->entries()->get();

        foreach ($entries as $entry) {
            $this->calculateKpis($entry);
            $entry->save();
        }
    }

    private function minutesBetween($startTime, $endTime): int
    {
        $start = \Carbon\Carbon::createFromFormat('H:i:s', strlen((string) $startTime) === 5 ? $startTime . ':00' : (string) $startTime);
        $end = \Carbon\Carbon::createFromFormat('H:i:s', strlen((string) $endTime) === 5 ? $endTime . ':00' : (string) $endTime);

        if ($end->lessThanOrEqualTo($start)) {
            $end->addDay();
        }

        return max(1, $start->diffInMinutes($end));
    }

    private function buildLineTelemetryPayload(ProductionEntry $entry): array
    {
        $entry->loadMissing([
            'zone',
            'productionLine',
            'shift',
            'product',
            'approver',
            'productionPlan.downtimes.machine',
            'productionPlan.downtimes.downtimeCategory',
            'productionPlan.downtimes.downtimeReason',
        ]);

        return [
            'source' => 'production_web_app',
            'event_type' => 'hourly_production_entry',
            'entry_id' => $entry->id,
            'entry_code' => $entry->entry_code,
            'production_plan_id' => $entry->production_plan_id,
            'plan_code' => $entry->productionPlan?->plan_code,
            'production_date' => $entry->production_date?->format('Y-m-d'),
            'zone' => $entry->zone?->code,
            'zone_name' => $entry->zone?->name,
            'line' => $entry->productionLine?->code,
            'line_name' => $entry->productionLine?->name,
            'product_code' => $entry->product?->code,
            'product_name' => $entry->product?->name,
            'shift' => $entry->shift?->code,
            'shift_name' => $entry->shift?->name,
            'hour_start' => $entry->hour_start ? substr($entry->hour_start, 0, 5) : null,
            'hour_end' => $entry->hour_end ? substr($entry->hour_end, 0, 5) : null,
            'planned_qty' => (float) $entry->planned_qty,
            'actual_qty' => (float) $entry->actual_qty,
            'good_qty' => (float) $entry->good_qty,
            'rejected_qty' => (float) $entry->rejected_qty,
            'chute_1_qty' => (float) $entry->chute_1_qty,
            'chute_2_qty' => (float) $entry->chute_2_qty,
            'chute_3_qty' => (float) $entry->chute_3_qty,
            'stop_duration_min' => (int) $entry->stop_duration_min,
            'stops_count' => (int) $entry->stops_count,
            'availability' => (float) $entry->availability,
            'performance' => (float) $entry->performance,
            'quality' => (float) $entry->quality,
            'oee' => (float) $entry->oee,
            'entry_status' => 'sent_to_thingsboard',
            'approved_by' => auth()->user()?->name,
            'approved_at' => now()->format('Y-m-d H:i:s'),
        ];
    }

    private function buildShiftSummaryTelemetryPayload(?ProductionPlan $plan): array
    {
        if (!$plan) {
            return [];
        }

        $plan->loadMissing([
            'zone',
            'productionLine',
            'shift',
            'product',
            'entries',
            'downtimes.machine',
            'downtimes.downtimeCategory',
            'downtimes.downtimeReason',
        ]);

        $entries = $plan->entries;
        $downtimes = $plan->downtimes;

        $plannedQty = (float) $plan->planned_qty;
        $actualQty = (float) $entries->sum('actual_qty');
        $goodQty = (float) $entries->sum('good_qty');
        $rejectedQty = (float) $entries->sum('rejected_qty');
        $chute1Qty = (float) $entries->sum('chute_1_qty');
        $chute2Qty = (float) $entries->sum('chute_2_qty');
        $chute3Qty = (float) $entries->sum('chute_3_qty');
        $downtimeMin = (int) $downtimes->sum('duration_min');
        $stopsCount = (int) $downtimes->count();
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

        return [
            'source' => 'production_web_app',
            'event_type' => 'shift_production_summary',
            'production_plan_id' => $plan->id,
            'plan_code' => $plan->plan_code,
            'production_date' => $plan->plan_date?->format('Y-m-d'),
            'zone' => $plan->zone?->code,
            'zone_name' => $plan->zone?->name,
            'line' => $plan->productionLine?->code,
            'line_name' => $plan->productionLine?->name,
            'product_code' => $plan->product?->code,
            'product_name' => $plan->product?->name,
            'shift' => $plan->shift?->code,
            'shift_name' => $plan->shift?->name,
            'shift_start' => $plan->hour_start ? substr($plan->hour_start, 0, 5) : null,
            'shift_end' => $plan->hour_end ? substr($plan->hour_end, 0, 5) : null,
            'shift_duration_min' => $shiftMinutes,
            'entries_count' => $entries->count(),
            'planned_qty_total' => round($plannedQty, 2),
            'actual_qty_total' => round($actualQty, 2),
            'good_qty_total' => round($goodQty, 2),
            'rejected_qty_total' => round($rejectedQty, 2),
            'chute_1_qty_total' => round($chute1Qty, 2),
            'chute_2_qty_total' => round($chute2Qty, 2),
            'chute_3_qty_total' => round($chute3Qty, 2),
            'stops_count' => $stopsCount,
            'total_downtime_min' => $downtimeMin,
            'availability' => round($availability, 2),
            'performance' => round($performance, 2),
            'quality' => round($quality, 2),
            'oee' => round($oee, 2),
            'plan_status' => 'completed',
            'sent_at' => now()->format('Y-m-d H:i:s'),
        ];
    }

    private function sendMachineStopTelemetry(ProductionEntry $entry, ProductionDowntime $downtime): array
    {
        $entry->load(['zone', 'productionLine', 'shift', 'product', 'productionPlan']);
        $downtime->load(['machine']);

        $mapping = ThingsboardDevice::where('mapping_type', 'machine')
            ->where('machine_id', $downtime->machine_id)
            ->where('is_active', true)
            ->first();

        if (!$mapping) {
            return [
                'success' => false,
                'message' => 'No active ThingsBoard machine mapping found for this machine.',
            ];
        }

        $payload = [
            'source' => 'production_web_app',
            'event_type' => 'machine_stop',
            'production_plan_id' => $entry->production_plan_id,
            'plan_code' => $entry->productionPlan?->plan_code,
            'downtime_id' => $downtime->id,
            'machine_status' => 'in_repair',
            'active_stop' => 1,
            'machine_code' => $downtime->machine?->code,
            'machine_name' => $downtime->machine?->name,
            'zone' => $entry->zone?->code,
            'zone_name' => $entry->zone?->name,
            'line' => $entry->productionLine?->code,
            'line_name' => $entry->productionLine?->name,
            'product_code' => $entry->product?->code,
            'product_name' => $entry->product?->name,
            'shift' => $entry->shift?->code,
            'shift_name' => $entry->shift?->name,
            'production_date' => $entry->production_date?->format('Y-m-d'),
            'stop_started_at' => $downtime->started_at?->format('Y-m-d H:i:s'),
            'stop_duration_min' => 0,
        ];

        return $this->sendTelemetryToThingsBoard($mapping->access_token, $payload);
    }

    private function sendMachineFixedTelemetry(ProductionEntry $entry, ProductionDowntime $downtime): array
    {
        $entry->load(['zone', 'productionLine', 'shift', 'product', 'productionPlan']);
        $downtime->load(['machine', 'downtimeCategory', 'downtimeReason']);

        $mapping = ThingsboardDevice::where('mapping_type', 'machine')
            ->where('machine_id', $downtime->machine_id)
            ->where('is_active', true)
            ->first();

        if (!$mapping) {
            return [
                'success' => false,
                'message' => 'No active ThingsBoard machine mapping found for this machine.',
            ];
        }

        $payload = [
            'source' => 'production_web_app',
            'event_type' => 'machine_fixed',
            'production_plan_id' => $entry->production_plan_id,
            'plan_code' => $entry->productionPlan?->plan_code,
            'downtime_id' => $downtime->id,
            'machine_status' => 'active',
            'active_stop' => 0,
            'machine_code' => $downtime->machine?->code,
            'machine_name' => $downtime->machine?->name,
            'zone' => $entry->zone?->code,
            'zone_name' => $entry->zone?->name,
            'line' => $entry->productionLine?->code,
            'line_name' => $entry->productionLine?->name,
            'product_code' => $entry->product?->code,
            'product_name' => $entry->product?->name,
            'shift' => $entry->shift?->code,
            'shift_name' => $entry->shift?->name,
            'production_date' => $entry->production_date?->format('Y-m-d'),
            'stop_started_at' => $downtime->started_at?->format('Y-m-d H:i:s'),
            'stop_ended_at' => $downtime->ended_at?->format('Y-m-d H:i:s'),
            'stop_duration_min' => (int) $downtime->duration_min,
            'downtime_category' => $downtime->downtimeCategory?->name,
            'downtime_reason' => $downtime->downtimeReason?->name,
            'comment' => $downtime->comment,
        ];

        return $this->sendTelemetryToThingsBoard($mapping->access_token, $payload);
    }

    private function sendTelemetryToThingsBoard(string $accessToken, array $payload): array
    {
        $baseUrl = rtrim(env('THINGSBOARD_BASE_URL', 'http://187.77.175.169:8080'), '/');
        $url = $baseUrl . '/api/v1/' . $accessToken . '/telemetry';

        try {
            $response = Http::timeout(10)
                ->acceptJson()
                ->asJson()
                ->post($url, $payload);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'status' => $response->status(),
                    'message' => 'Telemetry sent successfully.',
                    'body' => $response->body(),
                ];
            }

            Log::error('ThingsBoard telemetry failed', [
                'url' => $url,
                'status' => $response->status(),
                'body' => $response->body(),
                'payload' => $payload,
            ]);

            return [
                'success' => false,
                'status' => $response->status(),
                'message' => $response->body() ?: 'HTTP error from ThingsBoard.',
            ];
        } catch (\Throwable $e) {
            Log::error('ThingsBoard telemetry exception', [
                'url' => $url,
                'message' => $e->getMessage(),
                'payload' => $payload,
            ]);

            return [
                'success' => false,
                'status' => null,
                'message' => $e->getMessage(),
            ];
        }
    }

    private function isPlanFullySent(?ProductionPlan $plan): bool
    {
        if (!$plan) {
            return false;
        }

        $totalEntries = $plan->entries()->count();

        if ($totalEntries === 0) {
            return false;
        }

        $sentEntries = $plan->entries()
            ->where('entry_status', 'sent_to_thingsboard')
            ->where('sent_to_thingsboard', true)
            ->count();

        return $totalEntries === $sentEntries;
    }

    private function syncPlanStatusFromEntries(?ProductionPlan $plan): void
    {
        if (!$plan) {
            return;
        }

        $total = $plan->entries()->count();

        if ($total === 0) {
            $plan->update(['status' => 'planned']);
            return;
        }

        $sent = $plan->entries()->where('entry_status', 'sent_to_thingsboard')->count();

        if ($sent === $total) {
            $plan->update(['status' => 'completed']);
            return;
        }

        $plan->update(['status' => 'in_progress']);
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
            $query->where('production_entries.production_line_id', $user->production_line_id ?: 0);
            return;
        }

        if ($user->isSupervisor()) {
            $zoneIds = $user->assignedZoneIds();

            if (empty($zoneIds)) {
                $query->whereRaw('1 = 0');
            } else {
                $query->whereIn('production_entries.zone_id', $zoneIds);
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

    private function ensureUserCanAccessEntry(ProductionEntry $entry): void
    {
        $user = auth()->user();

        if (!$user) {
            abort(403, 'You cannot access this production entry.');
        }

        if (!$user->canAccessProductionLine((int) $entry->production_line_id)) {
            abort(403, 'You cannot access this production entry.');
        }
    }
}