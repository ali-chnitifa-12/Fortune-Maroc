<x-app-layout>
    <x-slot name="header">
        <div class="erp-page-head">
            <div>
                <h2 class="erp-page-title">Production Planning</h2>
                <div class="erp-page-subtitle">
                    Plan production by date, shift, zone, line, product and hour.
                </div>
            </div>

            @if(auth()->user()->canManageProductionPlanning())
                <a href="{{ route('production-plans.create') }}" class="erp-btn erp-btn-primary">
                    Add Production Plan
                </a>
            @endif
        </div>
    </x-slot>

    <div class="erp-page-wrap">
        @if(session('success'))
            <div class="fortune-success">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="fortune-error">
                <ul style="list-style:disc;margin-left:20px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="erp-card erp-filter-card">
            <form method="GET" action="{{ route('production-plans.index') }}">
                <div class="erp-filter-grid">
                    <div>
                        <label>Date From</label>
                        <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}">
                    </div>

                    <div>
                        <label>Date To</label>
                        <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}">
                    </div>

                    <div>
                        <label>Zone</label>
                        <select name="zone_id">
                            <option value="">All Zones</option>
                            @foreach($zones as $zone)
                                <option value="{{ $zone->id }}" {{ ($filters['zone_id'] ?? '') == $zone->id ? 'selected' : '' }}>
                                    {{ $zone->code }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label>Line</label>
                        <select name="production_line_id">
                            <option value="">All Lines</option>
                            @foreach($productionLines as $line)
                                <option value="{{ $line->id }}" {{ ($filters['production_line_id'] ?? '') == $line->id ? 'selected' : '' }}>
                                    {{ $line->code }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label>Product</label>
                        <select name="product_id">
                            <option value="">All Products</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" {{ ($filters['product_id'] ?? '') == $product->id ? 'selected' : '' }}>
                                    {{ $product->code }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label>Shift</label>
                        <select name="shift_id">
                            <option value="">All Shifts</option>
                            @foreach($shifts as $shift)
                                <option value="{{ $shift->id }}" {{ ($filters['shift_id'] ?? '') == $shift->id ? 'selected' : '' }}>
                                    {{ $shift->code }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label>Status</label>
                        <select name="status">
                            <option value="">All</option>
                            <option value="planned" {{ ($filters['status'] ?? '') === 'planned' ? 'selected' : '' }}>Planned</option>
                            <option value="in_progress" {{ ($filters['status'] ?? '') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="completed" {{ ($filters['status'] ?? '') === 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ ($filters['status'] ?? '') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                </div>

                <div class="erp-filter-actions">
                    <button type="submit" class="erp-btn erp-btn-primary">Apply Filters</button>
                    <a href="{{ route('production-plans.index') }}" class="erp-btn erp-btn-secondary">Reset</a>
                </div>
            </form>
        </div>

        <div class="erp-card">
            <div class="erp-result-title">Results: {{ $plans->total() }}</div>

            <div class="erp-responsive-table">
                <table class="erp-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Hour</th>
                            <th>Shift</th>
                            <th>Zone</th>
                            <th>Line</th>
                            <th>Product</th>
                            <th class="text-right">Planned Qty</th>
                            <th class="text-right">Target OEE</th>
                            <th>Status</th>
                            <th>Entry</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($plans as $plan)
                            <tr>
                                <td>{{ $plan->plan_date?->format('Y-m-d') }}</td>

                                <td>
                                    {{ $plan->hour_start ? substr($plan->hour_start, 0, 5) : '-' }}
                                    -
                                    {{ $plan->hour_end ? substr($plan->hour_end, 0, 5) : '-' }}
                                </td>

                                <td>{{ $plan->shift?->code }}</td>
                                <td>{{ $plan->zone?->code ?? '-' }}</td>
                                <td>{{ $plan->productionLine?->code ?? '-' }}</td>

                                <td>
                                    <strong>{{ $plan->product?->code }}</strong>
                                    <div class="erp-muted-small">{{ $plan->product?->name }}</div>
                                </td>

                                <td class="text-right">{{ number_format((float) $plan->planned_qty, 2) }}</td>

                                <td class="text-right">
                                    {{ $plan->target_oee !== null ? number_format((float) $plan->target_oee, 2) . '%' : '-' }}
                                </td>

                                <td>
                                    <span class="erp-pill erp-plan-{{ $plan->status }}">
                                        {{ ucwords(str_replace('_', ' ', $plan->status)) }}
                                    </span>
                                </td>

                                <td>
                                    @if($plan->production_entries_count > 0)
                                        <span class="erp-pill erp-pill-success">Entry Created</span>
                                    @elseif($plan->status !== 'cancelled' && $plan->status !== 'completed')
                                        <a href="{{ route('production-plans.create-entry', $plan) }}" class="erp-btn erp-btn-small erp-btn-primary">
                                            Create Entry
                                        </a>
                                    @else
                                        <span class="erp-muted-small">-</span>
                                    @endif
                                </td>

                                <td class="text-right">
                                    @if($plan->production_entries_count == 0 && auth()->user()->canManageProductionPlanning())
                                        <a href="{{ route('production-plans.edit', $plan) }}" class="erp-link">Edit</a>

                                        <form method="POST"
                                              action="{{ route('production-plans.destroy', $plan) }}"
                                              style="display:inline;"
                                              onsubmit="return confirm('Delete this production plan?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="erp-delete-link">Delete</button>
                                        </form>
                                    @else
                                        <span class="erp-muted-small">Locked</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="erp-empty">
                                    No production plans found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="erp-pagination">{{ $plans->links() }}</div>
        </div>
    </div>

    @include('components.erp-page-style')

    <style>
        .erp-filter-grid {
            display: grid;
            grid-template-columns: repeat(7, minmax(120px, 1fr));
            gap: 12px;
        }

        .erp-filter-grid label {
            display: block;
            margin-bottom: 5px;
            font-size: 12px;
            font-weight: 800;
            color: #334155;
        }

        .erp-filter-grid input,
        .erp-filter-grid select {
            width: 100%;
            height: 36px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 6px 9px;
            font-size: 13px;
            color: #0f172a;
            background: #ffffff;
        }

        .erp-filter-actions {
            display: flex;
            gap: 8px;
            margin-top: 12px;
        }

        .erp-plan-planned {
            background: #eff6ff;
            color: #1d4ed8;
        }

        .erp-plan-in_progress {
            background: #fef3c7;
            color: #92400e;
        }

        .erp-plan-completed {
            background: #dcfce7;
            color: #166534;
        }

        .erp-plan-cancelled {
            background: #fee2e2;
            color: #991b1b;
        }

        @media (max-width: 1280px) {
            .erp-filter-grid {
                grid-template-columns: repeat(3, minmax(120px, 1fr));
            }
        }

        @media (max-width: 768px) {
            .erp-filter-grid {
                grid-template-columns: 1fr;
            }

            .erp-filter-actions {
                flex-direction: column;
            }
        }
    </style>
</x-app-layout>