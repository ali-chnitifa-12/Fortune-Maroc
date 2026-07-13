<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductionEntry;
use App\Models\ProductionLine;
use App\Models\ProductionPlan;
use App\Models\Shift;
use App\Models\Zone;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductionPlanController extends Controller
{
    private array $statuses = [
        'planned' => 'Planned',
        'in_progress' => 'In Progress',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ];

    public function index(Request $request)
{
    if (!auth()->user()?->canViewProductionPlanning()) {
        abort(403);
    }

    $today = now()->toDateString();

    $filters = [
        'date_from' => $request->get('date_from', $today),
        'date_to' => $request->get('date_to', $today),
        'zone_id' => $request->get('zone_id'),
        'production_line_id' => $request->get('production_line_id'),
        'product_id' => $request->get('product_id'),
        'shift_id' => $request->get('shift_id'),
        'status' => $request->get('status'),
        'sort' => $request->get('sort'),
        'direction' => $request->get('direction'),
    ];

    $query = ProductionPlan::query()
        ->select('production_plans.*')
        ->with([
            'zone',
            'productionLine',
            'shift',
            'product',
            'entries',
        ])
        ->leftJoin('zones', 'zones.id', '=', 'production_plans.zone_id')
        ->leftJoin('production_lines', 'production_lines.id', '=', 'production_plans.production_line_id')
        ->leftJoin('shifts', 'shifts.id', '=', 'production_plans.shift_id')
        ->leftJoin('products', 'products.id', '=', 'production_plans.product_id');

    $this->applyUserScope($query);

    $query->whereDate('production_plans.plan_date', '>=', $filters['date_from']);
    $query->whereDate('production_plans.plan_date', '<=', $filters['date_to']);

    if (!empty($filters['zone_id']) && auth()->user()->canAccessZone((int) $filters['zone_id'])) {
        $query->where('production_plans.zone_id', $filters['zone_id']);
    }

    if (!empty($filters['production_line_id']) && auth()->user()->canAccessProductionLine((int) $filters['production_line_id'])) {
        $query->where('production_plans.production_line_id', $filters['production_line_id']);
    }

    if (!empty($filters['product_id'])) {
        $query->where('production_plans.product_id', $filters['product_id']);
    }

    if (!empty($filters['shift_id'])) {
        $query->where('production_plans.shift_id', $filters['shift_id']);
    }

    if (!empty($filters['status'])) {
        $query->where('production_plans.status', $filters['status']);
    }

    $this->applySorting($query, $request);

    $plans = $query
        ->paginate(20)
        ->withQueryString();

    return view('production-plans.index', [
        'plans' => $plans,
        'zones' => $this->visibleZones(),
        'productionLines' => $this->visibleProductionLines(),
        'products' => Product::where('is_active', true)->orderBy('code')->get(),
        'shifts' => Shift::where('is_active', true)->orderBy('start_time')->get(),
        'statuses' => $this->statuses,
        'filters' => $filters,
    ]);
}

    public function create()
    {
        if (!auth()->user()?->canManageProductionPlans()) {
            abort(403);
        }

        return view('production-plans.create', [
            'zones' => $this->visibleZones(),
            'productionLines' => $this->visibleProductionLines(),
            'products' => Product::where('is_active', true)->orderBy('code')->get(),
            'shifts' => Shift::where('is_active', true)->orderBy('start_time')->get(),
            'statuses' => $this->statuses,
        ]);
    }

    public function store(Request $request)
    {
        if (!auth()->user()?->canManageProductionPlans()) {
            abort(403);
        }

        $data = $request->validate([
            'plan_date' => ['required', 'date'],
            'zone_id' => ['required', 'exists:zones,id'],
            'production_line_id' => ['required', 'exists:production_lines,id'],
            'shift_id' => ['required', 'exists:shifts,id'],
            'product_id' => ['required', 'exists:products,id'],
            'planned_qty' => ['required', 'numeric', 'min:0.01'],
            'target_oee' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'responsible' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'status' => ['nullable', 'string'],
        ]);

        if (!array_key_exists($data['status'] ?? 'planned', $this->statuses)) {
            return back()->withInput()->withErrors([
                'status' => 'Invalid production plan status.',
            ]);
        }

        if (!auth()->user()->canAccessZone((int) $data['zone_id'])) {
            abort(403, 'You cannot create a plan for this zone.');
        }

        if (!auth()->user()->canAccessProductionLine((int) $data['production_line_id'])) {
            abort(403, 'You cannot create a plan for this production line.');
        }

        $line = ProductionLine::findOrFail($data['production_line_id']);

        if ((int) $line->zone_id !== (int) $data['zone_id']) {
            return back()->withInput()->withErrors([
                'production_line_id' => 'Selected line does not belong to selected zone.',
            ]);
        }

        $lineHasProduct = $line->activeProducts()
            ->where('products.id', $data['product_id'])
            ->exists();

        if (!$lineHasProduct) {
            return back()->withInput()->withErrors([
                'product_id' => 'Selected product is not assigned to selected production line.',
            ]);
        }

        $shift = Shift::findOrFail($data['shift_id']);

        $hourSlots = $this->buildHourlySlotsFromShift($shift);

        if (count($hourSlots) === 0) {
            return back()->withInput()->withErrors([
                'shift_id' => 'Selected shift has invalid start/end time. Cannot generate hourly entries.',
            ]);
        }

        $plan = DB::transaction(function () use ($data, $shift, $hourSlots) {
            $firstSlot = $hourSlots[0];
            $lastSlot = $hourSlots[count($hourSlots) - 1];

            $plan = ProductionPlan::create([
                'plan_date' => $data['plan_date'],
                'zone_id' => $data['zone_id'],
                'production_line_id' => $data['production_line_id'],
                'shift_id' => $data['shift_id'],
                'product_id' => $data['product_id'],
                'hour_start' => $firstSlot['hour_start'],
                'hour_end' => $lastSlot['hour_end'],
                'planned_qty' => $data['planned_qty'],
                'target_oee' => $data['target_oee'] ?? null,
                'responsible' => $data['responsible'] ?? null,
                'notes' => $data['notes'] ?? null,
                'status' => 'in_progress',
                'entries_generated_at' => now(),
                'created_by' => auth()->id(),
            ]);

            $this->generateEntriesForPlan($plan, $hourSlots);

            return $plan;
        });

        return redirect()->route('production-plans.index')
            ->with('success', 'Production plan created and hourly production entries generated successfully.');
    }

    public function edit(ProductionPlan $production_plan)
    {
        if (!auth()->user()?->canManageProductionPlans()) {
            abort(403);
        }

        if (!auth()->user()->canAccessProductionLine((int) $production_plan->production_line_id)) {
            abort(403, 'You cannot access this production plan.');
        }

        if ($production_plan->entries()->exists()) {
            return redirect()->route('production-plans.index')
                ->withErrors([
                    'plan' => 'This production plan already has generated production entries and cannot be edited.',
                ]);
        }

        return view('production-plans.edit', [
            'plan' => $production_plan,
            'zones' => $this->visibleZones(),
            'productionLines' => $this->visibleProductionLines(),
            'products' => Product::where('is_active', true)->orderBy('code')->get(),
            'shifts' => Shift::where('is_active', true)->orderBy('start_time')->get(),
            'statuses' => $this->statuses,
        ]);
    }

    public function update(Request $request, ProductionPlan $production_plan)
    {
        if (!auth()->user()?->canManageProductionPlans()) {
            abort(403);
        }

        if (!auth()->user()->canAccessProductionLine((int) $production_plan->production_line_id)) {
            abort(403, 'You cannot access this production plan.');
        }

        if ($production_plan->entries()->exists()) {
            return redirect()->route('production-plans.index')
                ->withErrors([
                    'plan' => 'This production plan already has generated production entries and cannot be updated.',
                ]);
        }

        if (in_array($production_plan->status, ['completed', 'cancelled'], true)) {
            return back()->withErrors([
                'plan' => 'Completed or cancelled plans cannot be updated.',
            ]);
        }

        $data = $request->validate([
            'plan_date' => ['required', 'date'],
            'zone_id' => ['required', 'exists:zones,id'],
            'production_line_id' => ['required', 'exists:production_lines,id'],
            'shift_id' => ['required', 'exists:shifts,id'],
            'product_id' => ['required', 'exists:products,id'],
            'planned_qty' => ['required', 'numeric', 'min:0.01'],
            'target_oee' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'responsible' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'status' => ['nullable', 'string'],
        ]);

        if (!array_key_exists($data['status'] ?? 'planned', $this->statuses)) {
            return back()->withInput()->withErrors([
                'status' => 'Invalid production plan status.',
            ]);
        }

        if (!auth()->user()->canAccessZone((int) $data['zone_id'])) {
            abort(403, 'You cannot update a plan for this zone.');
        }

        if (!auth()->user()->canAccessProductionLine((int) $data['production_line_id'])) {
            abort(403, 'You cannot update a plan for this production line.');
        }

        $line = ProductionLine::findOrFail($data['production_line_id']);

        if ((int) $line->zone_id !== (int) $data['zone_id']) {
            return back()->withInput()->withErrors([
                'production_line_id' => 'Selected line does not belong to selected zone.',
            ]);
        }

        $lineHasProduct = $line->activeProducts()
            ->where('products.id', $data['product_id'])
            ->exists();

        if (!$lineHasProduct) {
            return back()->withInput()->withErrors([
                'product_id' => 'Selected product is not assigned to selected production line.',
            ]);
        }

        $shift = Shift::findOrFail($data['shift_id']);
        $hourSlots = $this->buildHourlySlotsFromShift($shift);

        if (count($hourSlots) === 0) {
            return back()->withInput()->withErrors([
                'shift_id' => 'Selected shift has invalid start/end time.',
            ]);
        }

        $firstSlot = $hourSlots[0];
        $lastSlot = $hourSlots[count($hourSlots) - 1];

        $production_plan->update([
            'plan_date' => $data['plan_date'],
            'zone_id' => $data['zone_id'],
            'production_line_id' => $data['production_line_id'],
            'shift_id' => $data['shift_id'],
            'product_id' => $data['product_id'],
            'hour_start' => $firstSlot['hour_start'],
            'hour_end' => $lastSlot['hour_end'],
            'planned_qty' => $data['planned_qty'],
            'target_oee' => $data['target_oee'] ?? null,
            'responsible' => $data['responsible'] ?? null,
            'notes' => $data['notes'] ?? null,
            'status' => $data['status'] ?? 'planned',
        ]);

        return redirect()->route('production-plans.index')
            ->with('success', 'Production plan updated successfully.');
    }

    public function destroy(ProductionPlan $production_plan)
    {
        if (!auth()->user()?->canManageProductionPlans()) {
            abort(403);
        }

        if (!auth()->user()->canAccessProductionLine((int) $production_plan->production_line_id)) {
            abort(403, 'You cannot access this production plan.');
        }

        $hasStartedEntries = $production_plan->entries()
            ->where(function ($query) {
                $query->where('actual_qty', '>', 0)
                    ->orWhere('rejected_qty', '>', 0)
                    ->orWhere('entry_status', '!=', 'draft')
                    ->orWhere('sent_to_thingsboard', true);
            })
            ->exists();

        if ($hasStartedEntries) {
            return back()->withErrors([
                'plan' => 'This production plan has started/finished/sent entries and cannot be deleted.',
            ]);
        }

        DB::transaction(function () use ($production_plan) {
            $production_plan->downtimes()->delete();
            $production_plan->entries()->delete();
            $production_plan->delete();
        });

        return redirect()->route('production-plans.index')
            ->with('success', 'Production plan and generated entries deleted successfully.');
    }

    public function linesByZone(Zone $zone)
    {
        if (!auth()->user()->canAccessZone((int) $zone->id)) {
            abort(403);
        }

        return ProductionLine::where('zone_id', $zone->id)
            ->where('is_active', true)
            ->orderBy('code')
            ->get(['id', 'code', 'name', 'zone_id']);
    }

    public function productsByLine(ProductionLine $production_line)
    {
        if (!auth()->user()->canAccessProductionLine((int) $production_line->id)) {
            abort(403);
        }

        return $production_line->activeProducts()
            ->orderBy('products.code')
            ->get([
                'products.id',
                'products.code',
                'products.name',
                'line_product.standard_qty_per_hour',
            ]);
    }

    public function machinesByLine(ProductionLine $production_line)
    {
        if (!auth()->user()->canAccessProductionLine((int) $production_line->id)) {
            abort(403);
        }

        return $production_line->machines()
            ->where('is_active', true)
            ->orderBy('code')
            ->get(['id', 'code', 'name', 'production_line_id']);
    }

    private function generateEntriesForPlan(ProductionPlan $plan, array $hourSlots): void
    {
        $slotsCount = count($hourSlots);

        if ($slotsCount === 0) {
            return;
        }

        $plannedQtyPerEntry = round(((float) $plan->planned_qty) / $slotsCount, 2);
        $remainingQty = (float) $plan->planned_qty;

        foreach ($hourSlots as $index => $slot) {
            $qty = $plannedQtyPerEntry;

            if ($index === $slotsCount - 1) {
                $qty = round($remainingQty, 2);
            }

            ProductionEntry::create([
                'production_plan_id' => $plan->id,
                'zone_id' => $plan->zone_id,
                'production_line_id' => $plan->production_line_id,
                'production_date' => $plan->plan_date,
                'shift_id' => $plan->shift_id,
                'machine_id' => null,
                'product_id' => $plan->product_id,
                'hour_start' => $slot['hour_start'],
                'hour_end' => $slot['hour_end'],
                'planned_qty' => $qty,
                'actual_qty' => 0,
                'rejected_qty' => 0,
                'chute_qty' => 0,
                'chute_1_qty' => 0,
                'chute_2_qty' => 0,
                'chute_3_qty' => 0,
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

            $remainingQty -= $qty;
        }
    }

    private function buildHourlySlotsFromShift(Shift $shift): array
    {
        if (!$shift->start_time || !$shift->end_time) {
            return [];
        }

        $start = Carbon::createFromFormat('H:i:s', $this->normalizeTime($shift->start_time));
        $end = Carbon::createFromFormat('H:i:s', $this->normalizeTime($shift->end_time));

        if ($end->lessThanOrEqualTo($start)) {
            $end->addDay();
        }

        $slots = [];
        $cursor = $start->copy();

        while ($cursor->lessThan($end)) {
            $next = $cursor->copy()->addHour();

            if ($next->greaterThan($end)) {
                $next = $end->copy();
            }

            $slots[] = [
                'hour_start' => $cursor->format('H:i:s'),
                'hour_end' => $next->format('H:i:s'),
            ];

            $cursor = $next->copy();
        }

        return $slots;
    }

    private function normalizeTime($time): string
    {
        if ($time instanceof Carbon) {
            return $time->format('H:i:s');
        }

        $time = (string) $time;

        if (strlen($time) === 5) {
            return $time . ':00';
        }

        return $time;
    }

    private function applySorting($query, Request $request): void
    {
        $allowedSorts = [
            'plan_code' => 'production_plans.plan_code',
            'plan_date' => 'production_plans.plan_date',
            'hour' => 'production_plans.hour_start',
            'shift' => 'shifts.code',
            'zone' => 'zones.code',
            'line' => 'production_lines.code',
            'product' => 'products.code',
            'planned_qty' => 'production_plans.planned_qty',
            'target_oee' => 'production_plans.target_oee',
            'status' => 'production_plans.status',
            'created_at' => 'production_plans.created_at',
        ];

        $sort = $request->get('sort', 'plan_date');
        $direction = strtolower($request->get('direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        if (!array_key_exists($sort, $allowedSorts)) {
            $sort = 'plan_date';
            $direction = 'desc';
        }

        $query->orderBy($allowedSorts[$sort], $direction);

        if ($sort !== 'hour') {
            $query->orderBy('production_plans.hour_start', 'desc');
        }

        $query->orderBy('production_plans.id', 'desc');
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
            $query->where('production_plans.production_line_id', $user->production_line_id ?: 0);
            return;
        }

        if ($user->isSupervisor()) {
            $zoneIds = $user->assignedZoneIds();

            if (empty($zoneIds)) {
                $query->whereRaw('1 = 0');
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