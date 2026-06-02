<?php

namespace App\Http\Controllers;

use App\Models\ProductionDowntime;
use Illuminate\Http\Request;

class ProductionDowntimeController extends Controller
{
    public function update(Request $request, ProductionDowntime $production_downtime)
    {
        $data = $request->validate([
            'downtime_category_id' => ['required', 'exists:downtime_categories,id'],
            'downtime_reason_id' => ['required', 'exists:downtime_reasons,id'],
            'comment' => ['nullable', 'string'],
        ]);

        $entry = $production_downtime->productionEntry;

        if ($entry->entry_status === 'finished' || $entry->sent_to_thingsboard) {
            return back()->withErrors([
                'downtime' => 'You cannot update downtime after entry is finished or sent.',
            ]);
        }

        $production_downtime->update($data);

        return back()->with('success', 'Downtime line updated successfully.');
    }
}