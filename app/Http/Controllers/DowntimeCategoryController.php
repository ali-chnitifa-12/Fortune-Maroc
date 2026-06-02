<?php

namespace App\Http\Controllers;

use App\Models\DowntimeCategory;
use Illuminate\Http\Request;

class DowntimeCategoryController extends Controller
{
    public function index()
    {
        $categories = DowntimeCategory::orderBy('name')->paginate(10);

        return view('downtime-categories.index', compact('categories'));
    }

    public function create()
    {
        return view('downtime-categories.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:downtime_categories,name'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->has('is_active');

        DowntimeCategory::create($data);

        return redirect()->route('downtime-categories.index')
            ->with('success', 'Downtime category created successfully.');
    }

    public function edit(DowntimeCategory $downtime_category)
    {
        return view('downtime-categories.edit', [
            'category' => $downtime_category,
        ]);
    }

    public function update(Request $request, DowntimeCategory $downtime_category)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:downtime_categories,name,' . $downtime_category->id],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->has('is_active');

        $downtime_category->update($data);

        return redirect()->route('downtime-categories.index')
            ->with('success', 'Downtime category updated successfully.');
    }

    public function destroy(DowntimeCategory $downtime_category)
    {
        $downtime_category->delete();

        return redirect()->route('downtime-categories.index')
            ->with('success', 'Downtime category deleted successfully.');
    }
}
