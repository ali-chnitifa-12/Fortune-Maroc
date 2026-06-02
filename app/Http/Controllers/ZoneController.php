<?php

namespace App\Http\Controllers;

use App\Models\Zone;
use Illuminate\Http\Request;

class ZoneController extends Controller
{
    public function index()
    {
        $zones = Zone::withCount('productionLines')
            ->orderBy('code')
            ->paginate(20);

        return view('zones.index', [
            'zones' => $zones,
        ]);
    }

    public function create()
    {
        return view('zones.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:zones,code'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
        ]);

        Zone::create($data);

        return redirect()->route('zones.index')
            ->with('success', 'Zone created successfully.');
    }

    public function edit(Zone $zone)
    {
        return view('zones.edit', [
            'zone' => $zone,
        ]);
    }

    public function update(Request $request, Zone $zone)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:zones,code,' . $zone->id],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
        ]);

        $zone->update($data);

        return redirect()->route('zones.index')
            ->with('success', 'Zone updated successfully.');
    }

    public function destroy(Zone $zone)
    {
        if ($zone->productionLines()->exists()) {
            return back()->withErrors([
                'zone' => 'Cannot delete a zone linked to production lines.',
            ]);
        }

        $zone->delete();

        return redirect()->route('zones.index')
            ->with('success', 'Zone deleted successfully.');
    }
}