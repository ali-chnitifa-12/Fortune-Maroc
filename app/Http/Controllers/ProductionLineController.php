<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductionLine;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductionLineController extends Controller
{
    public function index()
    {
        $productionLines = ProductionLine::with('zone')
            ->withCount(['machines', 'products'])
            ->orderBy('code')
            ->paginate(20);

        return view('production-lines.index', [
            'productionLines' => $productionLines,
        ]);
    }

    public function create()
    {
        return view('production-lines.create', [
            'zones' => Zone::where('is_active', true)->orderBy('code')->get(),
            'products' => Product::where('is_active', true)->orderBy('code')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'zone_id' => ['required', 'exists:zones,id'],
            'code' => ['required', 'string', 'max:50', 'unique:production_lines,code'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
            'products' => ['nullable', 'array'],
            'products.*.selected' => ['nullable'],
            'products.*.standard_qty_per_hour' => ['nullable', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($request, $data) {
            $productionLine = ProductionLine::create([
                'zone_id' => $data['zone_id'],
                'code' => $data['code'],
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'is_active' => (bool) $data['is_active'],
            ]);

            $productionLine->products()->sync($this->buildProductSyncData($request));
        });

        return redirect()->route('production-lines.index')
            ->with('success', 'Production line created successfully.');
    }

    public function edit(ProductionLine $production_line)
    {
        $production_line->load(['zone', 'products']);

        return view('production-lines.edit', [
            'productionLine' => $production_line,
            'zones' => Zone::where('is_active', true)->orderBy('code')->get(),
            'products' => Product::where('is_active', true)->orderBy('code')->get(),
            'assignedProducts' => $production_line->products->keyBy('id'),
        ]);
    }

    public function update(Request $request, ProductionLine $production_line)
    {
        $data = $request->validate([
            'zone_id' => ['required', 'exists:zones,id'],
            'code' => ['required', 'string', 'max:50', 'unique:production_lines,code,' . $production_line->id],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
            'products' => ['nullable', 'array'],
            'products.*.selected' => ['nullable'],
            'products.*.standard_qty_per_hour' => ['nullable', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($request, $data, $production_line) {
            $production_line->update([
                'zone_id' => $data['zone_id'],
                'code' => $data['code'],
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'is_active' => (bool) $data['is_active'],
            ]);

            $production_line->products()->sync($this->buildProductSyncData($request));
        });

        return redirect()->route('production-lines.index')
            ->with('success', 'Production line updated successfully.');
    }

    public function destroy(ProductionLine $production_line)
    {
        if ($production_line->machines()->exists()) {
            return back()->withErrors([
                'production_line' => 'Cannot delete a line linked to machines.',
            ]);
        }

        $production_line->products()->detach();
        $production_line->delete();

        return redirect()->route('production-lines.index')
            ->with('success', 'Production line deleted successfully.');
    }

    private function buildProductSyncData(Request $request): array
    {
        $syncData = [];

        foreach ($request->input('products', []) as $productId => $productData) {
            if (!array_key_exists('selected', $productData)) {
                continue;
            }

            $syncData[$productId] = [
                'standard_qty_per_hour' => $productData['standard_qty_per_hour'] ?? null,
                'is_active' => true,
            ];
        }

        return $syncData;
    }
}