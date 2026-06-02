<?php

namespace App\Http\Controllers;

use App\Models\DowntimeReason;
use App\Models\DowntimeCategory;
use Illuminate\Http\Request;

class DowntimeReasonController extends Controller
{
    public function index()
    {
        $reasons = DowntimeReason::with('category')
            ->orderBy('name')
            ->paginate(10);

        return view('downtime-reasons.index', compact('reasons'));
    }

    public function create()
    {
        $categories = DowntimeCategory::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('downtime-reasons.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'downtime_category_id' => ['required', 'exists:downtime_categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->has('is_active');

        DowntimeReason::create($data);

        return redirect()->route('downtime-reasons.index')
            ->with('success', 'Downtime reason created successfully.');
    }

    public function edit(DowntimeReason $downtime_reason)
    {
        $categories = DowntimeCategory::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('downtime-reasons.edit', [
            'reason' => $downtime_reason,
            'categories' => $categories,
        ]);
    }

    public function update(Request $request, DowntimeReason $downtime_reason)
    {
        $data = $request->validate([
            'downtime_category_id' => ['required', 'exists:downtime_categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->has('is_active');

        $downtime_reason->update($data);

        return redirect()->route('downtime-reasons.index')
            ->with('success', 'Downtime reason updated successfully.');
    }

    public function destroy(DowntimeReason $downtime_reason)
    {
        $downtime_reason->delete();

        return redirect()->route('downtime-reasons.index')
            ->with('success', 'Downtime reason deleted successfully.');
    }
}
