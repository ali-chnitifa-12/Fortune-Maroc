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

        if ($request->filled('date_from')) {
            $query->whereDate('production_entries.production_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('production_entries.production_date', '<=', $request->date_to);
        }

        if ($request->filled('zone_id') && auth()->user()->canAccessZone((int) $request->zone_id)) {
            $query->where('production_entries.zone_id', $request->zone_id);
        }

        if ($request->filled('production_line_id') && auth()->user()->canAccessProductionLine((int) $request->production_line_id)) {
            $query->where('production_entries.production_line_id', $request->production_line_id);
        }

        if ($request->filled('product_id')) {
            $query->where('production_entries.product_id', $request->product_id);
        }

        if ($request->filled('shift_id')) {
            $query->where('production_entries.shift_id', $request->shift_id);
        }

        if ($request->filled('entry_status')) {
            $query->where('production_entries.entry_status', $request->entry_status);
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
            'filters' => $request->only([
                'date_from',
                'date_to',
                'zone_id',
                'production_line_id',
                'product_id',
                'shift_id',
                'entry_status',
                'sort',
                'direction',
            ]),
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
            'chute_qty' => 'production_entries.chute_qty',
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
                'entry' => 'Create production entries from production plans.',
            ]);
    }

    public function store(Request $request)
    {
        return redirect()->route('production-plans.index')
            ->withErrors([
                'entry' => 'Create production entries from production plans.',
            ]);
    }

    public function createFromPlan(ProductionPlan $production_plan)
    {
        if (!auth()->user()?->canCreateProductionEntries()) {
            abort(403, 'You are not allowed to create production entries.');
        }

        if (!auth()->user()->canAccessProductionLine((int) $production_plan->production_line_id)) {
            abort(403, 'You cannot create an entry from this production plan.');
        }

        if ($production_plan->status === 'cancelled') {
            return redirect()->route('production-plans.index')
                ->withErrors([
                    'plan' => 'You cannot create an entry from a cancelled plan.',
                ]);
        }

        $existingEntry = ProductionEntry::where('production_plan_id', $production_plan->id)->first();

        if ($existingEntry) {
            return redirect()->route('production-entries.edit', $existingEntry)
                ->withErrors([
                    'plan' => 'A production entry already exists for this production plan.',
                ]);
        }

        $entry = DB::transaction(function () use ($production_plan) {
            $entry = ProductionEntry::create([
                'production_plan_id' => $production_plan->id,
                'zone_id' => $production_plan->zone_id,
                'production_line_id' => $production_plan->production_line_id,
                'production_date' => $production_plan->plan_date,
                'shift_id' => $production_plan->shift_id,
                'machine_id' => null,
                'product_id' => $production_plan->product_id,
                'hour_start' => $production_plan->hour_start,
                'hour_end' => $production_plan->hour_end,
                'planned_qty' => $production_plan->planned_qty,
                'actual_qty' => 0,
                'rejected_qty' => 0,
                'chute_qty' => 0,
                'good_qty' => 0,
                'machine_status' => 'active',
                'entry_status' => 'draft',
                'stop_duration_min' => 0,
                'stops_count' => 0,
                'availability' => 100,
                'performance' => 0,
                'quality' => 0,
                'oee' => 0,
                'sent_to_thingsboard' => false,
                'created_by' => auth()->id(),
            ]);

            $production_plan->update([
                'status' => 'in_progress',
            ]);

            return $entry;
        });

        return redirect()->route('production-entries.edit', $entry)
            ->with('success', 'Production entry created from plan.');
    }

    public function edit(ProductionEntry $production_entry)
    {
        $this->ensureUserCanAccessEntry($production_entry);

        $production_entry->load([
            'zone',
            'productionLine.machines',
            'shift',
            'product',
            'productionPlan',
            'downtimes.machine',
            'downtimes.downtimeCategory',
            'downtimes.downtimeReason',
        ]);

        return view('production-entries.edit', [
            'entry' => $production_entry,
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
            'chute_qty' => ['nullable', 'numeric', 'min:0'],
            'comment' => ['nullable', 'string'],
        ]);

        if ((float) $data['rejected_qty'] > (float) $data['actual_qty']) {
            return back()->withInput()->withErrors([
                'rejected_qty' => 'Rejected Qty cannot be greater than Actual Qty.',
            ]);
        }

        $data['chute_qty'] = $data['chute_qty'] ?? 0;

        $production_entry->fill($data);

        $this->calculateKpis($production_entry);
        $production_entry->save();

        return back()->with('success', 'Production entry updated successfully.');
    }

    public function stopMachine(Request $request, ProductionEntry $production_entry)
    {
        $this->ensureUserCanAccessEntry($production_entry);

        if (!auth()->user()?->canEditProductionEntries()) {
            abort(403, 'You are not allowed to declare machine stops.');
        }

        if ($production_entry->entry_status !== 'draft') {
            return back()->withErrors([
                'entry' => 'Only draft entries can declare machine stops.',
            ]);
        }

        $data = $request->validate([
            'machine_id' => ['required', 'exists:machines,id'],
            'actual_qty' => ['nullable', 'numeric', 'min:0'],
            'rejected_qty' => ['nullable', 'numeric', 'min:0'],
            'chute_qty' => ['nullable', 'numeric', 'min:0'],
        ]);

        $actualQty = $request->filled('actual_qty')
            ? (float) $data['actual_qty']
            : (float) $production_entry->actual_qty;

        $rejectedQty = $request->filled('rejected_qty')
            ? (float) $data['rejected_qty']
            : (float) $production_entry->rejected_qty;

        $chuteQty = $request->filled('chute_qty')
            ? (float) $data['chute_qty']
            : (float) $production_entry->chute_qty;

        if ($actualQty <= 0 && $rejectedQty > 0) {
            return back()
                ->withInput()
                ->withErrors([
                    'rejected_qty' => 'Rejected Qty cannot be entered before Actual Qty.',
                ]);
        }

        if ($actualQty > 0 && $rejectedQty > $actualQty) {
            return back()
                ->withInput()
                ->withErrors([
                    'rejected_qty' => 'Rejected Qty cannot be greater than Actual Qty.',
                ]);
        }

        $machineBelongsToLine = Machine::where('id', $data['machine_id'])
            ->where('production_line_id', $production_entry->production_line_id)
            ->exists();

        if (!$machineBelongsToLine) {
            return back()->withErrors([
                'machine_id' => 'Selected machine does not belong to this production line.',
            ]);
        }

        $openDowntime = ProductionDowntime::where('production_entry_id', $production_entry->id)
            ->whereNull('ended_at')
            ->first();

        if ($openDowntime) {
            return back()->withErrors([
                'machine_id' => 'There is already an open machine stop. Fix it first.',
            ]);
        }

        $downtime = DB::transaction(function () use ($production_entry, $data, $actualQty, $rejectedQty, $chuteQty) {
            $production_entry->actual_qty = $actualQty;
            $production_entry->rejected_qty = $rejectedQty;
            $production_entry->chute_qty = $chuteQty;

            $this->calculateKpis($production_entry);

            $production_entry->machine_id = $data['machine_id'];
            $production_entry->machine_status = 'in_repair';
            $production_entry->current_stop_started_at = now();
            $production_entry->stop_started_at = now();
            $production_entry->stops_count = (int) $production_entry->stops_count + 1;
            $production_entry->save();

            return ProductionDowntime::create([
                'production_entry_id' => $production_entry->id,
                'machine_id' => $data['machine_id'],
                'started_at' => now(),
                'ended_at' => null,
                'duration_min' => 0,
            ]);
        });

        $this->sendMachineStopTelemetry($production_entry->fresh(), $downtime->fresh());

        return redirect()
            ->route('production-entries.edit', ['production_entry' => $production_entry->id, 'tab' => 'downtime'])
            ->with('success', 'Machine stop declared.');
    }

    public function fixedMachine(ProductionEntry $production_entry)
    {
        $this->ensureUserCanAccessEntry($production_entry);

        if (!auth()->user()?->canEditProductionEntries()) {
            abort(403, 'You are not allowed to fix machine stops.');
        }

        if ($production_entry->entry_status !== 'draft') {
            return back()->withErrors([
                'entry' => 'Only draft entries can be fixed.',
            ]);
        }

        $openDowntime = ProductionDowntime::where('production_entry_id', $production_entry->id)
            ->whereNull('ended_at')
            ->latest('started_at')
            ->first();

        if (!$openDowntime) {
            return back()->withErrors([
                'downtime' => 'No open machine stop found.',
            ]);
        }

        $downtime = DB::transaction(function () use ($production_entry, $openDowntime) {
            $endedAt = now();
            $duration = max(1, $openDowntime->started_at->diffInMinutes($endedAt));

            $openDowntime->update([
                'ended_at' => $endedAt,
                'duration_min' => $duration,
            ]);

            $totalStop = ProductionDowntime::where('production_entry_id', $production_entry->id)
                ->sum('duration_min');

            $production_entry->stop_duration_min = $totalStop;
            $production_entry->machine_status = 'active';
            $production_entry->current_stop_started_at = null;
            $production_entry->stop_ended_at = $endedAt;

            $this->calculateKpis($production_entry);
            $production_entry->save();

            return $openDowntime->fresh();
        });

        $this->sendMachineFixedTelemetry($production_entry->fresh(), $downtime);

        return redirect()
            ->route('production-entries.edit', ['production_entry' => $production_entry->id, 'tab' => 'downtime'])
            ->with('success', 'Machine fixed.');
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

        $openDowntime = ProductionDowntime::where('production_entry_id', $production_entry->id)
            ->whereNull('ended_at')
            ->exists();

        if ($openDowntime) {
            return redirect()
                ->route('production-entries.edit', ['production_entry' => $production_entry->id, 'tab' => 'downtime'])
                ->withErrors([
                    'downtime' => 'You must fix the stopped machine before finishing the entry.',
                ]);
        }

        $invalidDowntime = ProductionDowntime::where('production_entry_id', $production_entry->id)
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
                    'downtime' => 'Downtime category and reason are mandatory before finishing the entry.',
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
            'downtimes.machine',
            'downtimes.downtimeCategory',
            'downtimes.downtimeReason',
            'productionPlan',
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

        $payload = $this->buildLineTelemetryPayload($production_entry);

        $tbResult = $this->sendTelemetryToThingsBoard($mapping->access_token, $payload);

        if (!$tbResult['success']) {
            return back()->withErrors([
                'thingsboard' => 'Approval blocked. ThingsBoard sending failed: ' . $tbResult['message'],
            ]);
        }

        DB::transaction(function () use ($production_entry, $mapping, $payload, $tbResult) {
            $production_entry->update([
                'entry_status' => 'sent_to_thingsboard',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'sent_to_thingsboard' => true,
                'thingsboard_response' => json_encode([
                    'mapping_id' => $mapping->id,
                    'device_name' => $mapping->device_name,
                    'payload' => $payload,
                    'response' => $tbResult,
                ]),
            ]);

            if ($production_entry->productionPlan) {
                $production_entry->productionPlan->update([
                    'status' => 'completed',
                ]);
            }
        });

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

            $production_entry->downtimes()->delete();
            $production_entry->delete();

            if ($plan) {
                $plan->update([
                    'status' => 'planned',
                ]);
            }
        });

        return redirect()->route('production-entries.index')
            ->with('success', 'Production entry deleted successfully.');
    }

    private function calculateKpis(ProductionEntry $entry): void
    {
        $plannedQty = (float) $entry->planned_qty;
        $actualQty = (float) $entry->actual_qty;
        $rejectedQty = (float) $entry->rejected_qty;
        $stopDuration = (int) $entry->stop_duration_min;

        $goodQty = max(0, $actualQty - $rejectedQty);
        $availability = max(0, min(100, ((60 - $stopDuration) / 60) * 100));
        $performance = $plannedQty > 0 ? ($actualQty / $plannedQty) * 100 : 0;
        $quality = $actualQty > 0 ? ($goodQty / $actualQty) * 100 : 0;
        $oee = ($availability * $performance * $quality) / 10000;

        $entry->good_qty = round($goodQty, 2);
        $entry->availability = round($availability, 2);
        $entry->performance = round($performance, 2);
        $entry->quality = round($quality, 2);
        $entry->oee = round($oee, 2);
    }

    private function buildLineTelemetryPayload(ProductionEntry $entry): array
    {
        $entry->loadMissing([
            'zone',
            'productionLine',
            'shift',
            'product',
            'approver',
            'productionPlan',
        ]);

        return [
            'source' => 'production_web_app',
            'event_type' => 'production_entry',
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
            'chute_qty' => (float) $entry->chute_qty,
            'stop_duration_min' => (int) $entry->stop_duration_min,
            'stops_count' => (int) $entry->stops_count,
            'availability' => (float) $entry->availability,
            'performance' => (float) $entry->performance,
            'quality' => (float) $entry->quality,
            'oee' => (float) $entry->oee,
            'entry_status' => 'sent_to_thingsboard',
            'machine_status' => $entry->machine_status,
            'approved_by' => auth()->user()?->name,
            'approved_at' => now()->format('Y-m-d H:i:s'),
        ];
    }

    private function sendMachineStopTelemetry(ProductionEntry $entry, ProductionDowntime $downtime): array
    {
        $entry->load(['zone', 'productionLine', 'shift', 'product']);
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
            'entry_id' => $entry->id,
            'entry_code' => $entry->entry_code,
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
            'shift' => $entry->shift?->code,
            'production_date' => $entry->production_date?->format('Y-m-d'),
            'hour_start' => $entry->hour_start ? substr($entry->hour_start, 0, 5) : null,
            'hour_end' => $entry->hour_end ? substr($entry->hour_end, 0, 5) : null,
            'planned_qty' => (float) $entry->planned_qty,
            'actual_qty' => (float) $entry->actual_qty,
            'good_qty' => (float) $entry->good_qty,
            'rejected_qty' => (float) $entry->rejected_qty,
            'chute_qty' => (float) $entry->chute_qty,
            'oee' => (float) $entry->oee,
            'stop_started_at' => $downtime->started_at?->format('Y-m-d H:i:s'),
            'stop_duration_min' => 0,
        ];

        return $this->sendTelemetryToThingsBoard($mapping->access_token, $payload);
    }

    private function sendMachineFixedTelemetry(ProductionEntry $entry, ProductionDowntime $downtime): array
    {
        $entry->load(['zone', 'productionLine', 'shift', 'product']);
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
            'entry_id' => $entry->id,
            'entry_code' => $entry->entry_code,
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
            'shift' => $entry->shift?->code,
            'production_date' => $entry->production_date?->format('Y-m-d'),
            'hour_start' => $entry->hour_start ? substr($entry->hour_start, 0, 5) : null,
            'hour_end' => $entry->hour_end ? substr($entry->hour_end, 0, 5) : null,
            'planned_qty' => (float) $entry->planned_qty,
            'actual_qty' => (float) $entry->actual_qty,
            'good_qty' => (float) $entry->good_qty,
            'rejected_qty' => (float) $entry->rejected_qty,
            'chute_qty' => (float) $entry->chute_qty,
            'oee' => (float) $entry->oee,
            'stop_started_at' => $downtime->started_at?->format('Y-m-d H:i:s'),
            'stop_ended_at' => $downtime->ended_at?->format('Y-m-d H:i:s'),
            'stop_duration_min' => (int) $downtime->duration_min,
            'total_entry_stop_duration_min' => (int) $entry->stop_duration_min,
            'stops_count' => (int) $entry->stops_count,
            'downtime_category' => $downtime->downtimeCategory?->name,
            'downtime_reason' => $downtime->downtimeReason?->name,
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