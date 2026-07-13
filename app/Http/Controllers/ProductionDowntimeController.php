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

        $production_downtime->load([
            'productionPlan.entries',
            'productionEntry',
        ]);

        $plan = $production_downtime->productionPlan;
        $entry = $production_downtime->productionEntry;

        if (!$plan && !$entry) {
            return back()->withErrors([
                'downtime' => 'This downtime is not linked to a production plan or production entry.',
            ]);
        }

        if (!$production_downtime->ended_at) {
            return back()->withErrors([
                'downtime' => 'You cannot update category/reason while the machine stop is still open. Fix the machine first.',
            ]);
        }

        if ($entry && ($entry->entry_status === 'sent_to_thingsboard' || $entry->sent_to_thingsboard)) {
            return back()->withErrors([
                'downtime' => 'This downtime cannot be updated because the related entry was already sent to ThingsBoard.',
            ]);
        }

        if ($plan) {
            $hasSentEntries = $plan->entries()
                ->where(function ($query) {
                    $query->where('entry_status', 'sent_to_thingsboard')
                        ->orWhere('sent_to_thingsboard', true);
                })
                ->exists();

            if ($hasSentEntries) {
                return back()->withErrors([
                    'downtime' => 'This downtime cannot be updated because at least one entry of this shift was already sent to ThingsBoard.',
                ]);
            }
        }

        $production_downtime->update([
            'downtime_category_id' => $data['downtime_category_id'],
            'downtime_reason_id' => $data['downtime_reason_id'],
            'comment' => $data['comment'] ?? null,
        ]);

        return back()->with('success', 'Downtime category and reason saved successfully.');
    }
}