<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductionLine;
use App\Models\ProductionPlan;
use App\Models\Shift;
use App\Models\Zone;
use Illuminate\Http\Request;

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

        if ($request->filled('date_from')) {
            $query->whereDate('production_plans.plan_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('production_plans.plan_date', '<=', $request->date_to);
        }

        if ($request->filled('zone_id') && auth()->user()->canAccessZone((int) $request->zone_id)) {
            $query->where('production_plans.zone_id', $request->zone_id);
        }

        if ($request->filled('production_line_id') && auth()->user()->canAccessProductionLine((int) $request->production_line_id)) {
            $query->where('production_plans.production_line_id', $request->production_line_id);
        }

        if ($request->filled('product_id')) {
            $query->where('production_plans.product_id', $request->product_id);
        }

        if ($request->filled('shift_id')) {
            $query->where('production_plans.shift_id', $request->shift_id);
        }

        if ($request->filled('status')) {
            $query->where('production_plans.status', $request->status);
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
            'filters' => $request->only([
                'date_from',
                'date_to',
                'zone_id',
                'production_line_id',
                'product_id',
                'shift_id',
                'status',
                'sort',
                'direction',
            ]),
        ]);
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
            'hour_start' => ['required'],
            'hour_end' => ['required'],
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

        $data['status'] = $data['status'] ?? 'planned';
        $data['created_by'] = auth()->id();

        ProductionPlan::create($data);

        return redirect()->route('production-plans.index')
            ->with('success', 'Production plan created successfully.');
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
                    'plan' => 'This production plan already has a production entry and cannot be edited.',
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
                    'plan' => 'This production plan already has a production entry and cannot be updated.',
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
            'hour_start' => ['required'],
            'hour_end' => ['required'],
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

        $production_plan->update($data);

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

        if ($production_plan->entries()->exists()) {
            return back()->withErrors([
                'plan' => 'This production plan already has an entry and cannot be deleted.',
            ]);
        }

        $production_plan->delete();

        return redirect()->route('production-plans.index')
            ->with('success', 'Production plan deleted successfully.');
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