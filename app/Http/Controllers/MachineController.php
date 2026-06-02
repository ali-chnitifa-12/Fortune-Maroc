<?php

namespace App\Http\Controllers;

use App\Models\Machine;
use App\Models\ProductionEntry;
use App\Models\ProductionLine;
use App\Models\ProductionPlan;
use Illuminate\Http\Request;

class MachineController extends Controller
{
    public function index()
    {
        $machines = Machine::with('productionLine.zone')
            ->orderBy('code')
            ->paginate(20);

        return view('machines.index', [
            'machines' => $machines,
        ]);
    }

    public function create()
    {
        return view('machines.create', [
            'machine' => null,
            'productionLines' => ProductionLine::with('zone')
                ->where('is_active', true)
                ->orderBy('code')
                ->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'production_line_id' => ['nullable', 'exists:production_lines,id'],
            'code' => ['required', 'string', 'max:50', 'unique:machines,code'],
            'name' => ['required', 'string', 'max:255'],
            'line' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
        ]);

        if (!empty($data['production_line_id'])) {
            $productionLine = ProductionLine::find($data['production_line_id']);
            $data['line'] = $productionLine?->code;
        }

        $data['is_active'] = (bool) $data['is_active'];

        Machine::create($data);

        return redirect()->route('machines.index')
            ->with('success', 'Machine created successfully.');
    }

    public function edit(Machine $machine)
    {
        return view('machines.edit', [
            'machine' => $machine,
            'productionLines' => ProductionLine::with('zone')
                ->where('is_active', true)
                ->orderBy('code')
                ->get(),
        ]);
    }

    public function update(Request $request, Machine $machine)
    {
        $data = $request->validate([
            'production_line_id' => ['nullable', 'exists:production_lines,id'],
            'code' => ['required', 'string', 'max:50', 'unique:machines,code,' . $machine->id],
            'name' => ['required', 'string', 'max:255'],
            'line' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
        ]);

        if (!empty($data['production_line_id'])) {
            $productionLine = ProductionLine::find($data['production_line_id']);
            $data['line'] = $productionLine?->code;
        }

        $data['is_active'] = (bool) $data['is_active'];

        $machine->update($data);

        return redirect()->route('machines.index')
            ->with('success', 'Machine updated successfully.');
    }

    public function destroy(Machine $machine)
    {
        $hasProductionEntries = ProductionEntry::where('machine_id', $machine->id)->exists();
        $hasProductionPlans = ProductionPlan::where('machine_id', $machine->id)->exists();

        if ($hasProductionEntries || $hasProductionPlans) {
            $machine->update([
                'is_active' => false,
            ]);

            return redirect()->route('machines.index')
                ->with('success', 'Machine is used in production history, so it was deactivated instead of deleted.');
        }

        $machine->delete();

        return redirect()->route('machines.index')
            ->with('success', 'Machine deleted successfully.');
    }
}