<?php

namespace App\Http\Controllers;

use App\Models\Machine;
use App\Models\ProductionLine;
use App\Models\ThingsboardDevice;
use App\Models\Zone;
use Illuminate\Http\Request;

class ThingsboardDeviceController extends Controller
{
    public function index()
    {
        $devices = ThingsboardDevice::with([
                'zone',
                'productionLine',
                'machine',
            ])
            ->orderBy('mapping_type')
            ->orderBy('device_name')
            ->paginate(20);

        return view('thingsboard-devices.index', [
            'devices' => $devices,
        ]);
    }

    public function create()
    {
        return view('thingsboard-devices.create', [
            'device' => null,
            'zones' => Zone::where('is_active', true)->orderBy('code')->get(),
            'productionLines' => ProductionLine::where('is_active', true)->orderBy('code')->get(),
            'machines' => Machine::where('is_active', true)->orderBy('code')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data = $this->prepareMappingData($data);

        ThingsboardDevice::create([
            'mapping_type' => $data['mapping_type'],
            'zone_id' => $data['zone_id'],
            'production_line_id' => $data['production_line_id'],
            'machine_id' => $data['machine_id'],
            'device_name' => $data['device_name'],
            'access_token' => $data['access_token'],
            'is_active' => $data['is_active'],
        ]);

        return redirect()->route('thingsboard-devices.index')
            ->with('success', 'ThingsBoard mapping created successfully.');
    }

    public function edit(ThingsboardDevice $thingsboard_device)
    {
        return view('thingsboard-devices.edit', [
            'device' => $thingsboard_device,
            'zones' => Zone::where('is_active', true)->orderBy('code')->get(),
            'productionLines' => ProductionLine::where('is_active', true)->orderBy('code')->get(),
            'machines' => Machine::where('is_active', true)->orderBy('code')->get(),
        ]);
    }

    public function update(Request $request, ThingsboardDevice $thingsboard_device)
    {
        $data = $this->validateData($request);
        $data = $this->prepareMappingData($data);

        $thingsboard_device->update([
            'mapping_type' => $data['mapping_type'],
            'zone_id' => $data['zone_id'],
            'production_line_id' => $data['production_line_id'],
            'machine_id' => $data['machine_id'],
            'device_name' => $data['device_name'],
            'access_token' => $data['access_token'],
            'is_active' => $data['is_active'],
        ]);

        return redirect()->route('thingsboard-devices.index')
            ->with('success', 'ThingsBoard mapping updated successfully.');
    }

    public function destroy(ThingsboardDevice $thingsboard_device)
    {
        $thingsboard_device->delete();

        return redirect()->route('thingsboard-devices.index')
            ->with('success', 'ThingsBoard mapping deleted successfully.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'mapping_type' => ['required', 'in:line,machine'],
            'zone_id' => ['nullable', 'exists:zones,id'],
            'production_line_id' => ['nullable', 'exists:production_lines,id'],
            'machine_id' => ['nullable', 'exists:machines,id'],
            'device_name' => ['required', 'string', 'max:255'],
            'access_token' => ['required', 'string', 'max:500'],
            'is_active' => ['required', 'boolean'],
        ]);
    }

    private function prepareMappingData(array $data): array
    {
        $data['zone_id'] = $data['zone_id'] ?? null;
        $data['production_line_id'] = $data['production_line_id'] ?? null;
        $data['machine_id'] = $data['machine_id'] ?? null;

        if ($data['mapping_type'] === 'line') {
            if (empty($data['production_line_id'])) {
                abort(422, 'Production line is required for line mapping.');
            }

            $productionLine = ProductionLine::findOrFail($data['production_line_id']);

            $data['zone_id'] = $data['zone_id'] ?: $productionLine->zone_id;
            $data['machine_id'] = null;
        }

        if ($data['mapping_type'] === 'machine') {
            if (empty($data['machine_id'])) {
                abort(422, 'Machine is required for machine mapping.');
            }

            $machine = Machine::with('productionLine')->findOrFail($data['machine_id']);

            $data['production_line_id'] = $data['production_line_id'] ?: $machine->production_line_id;
            $data['zone_id'] = $data['zone_id'] ?: $machine->productionLine?->zone_id;
        }

        $data['is_active'] = (bool) $data['is_active'];

        return $data;
    }
}