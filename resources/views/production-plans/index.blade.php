<x-app-layout>
    <x-slot name="header">
        <div class="erp-page-head">
            <div>
                <h2 class="erp-page-title">Production Planning</h2>
                <div class="erp-page-subtitle">
                    Plan production by date, shift, zone, line, product and hour.
                </div>
            </div>

            @if(auth()->user()?->canManageProductionPlans())
                <a href="{{ route('production-plans.create') }}" class="erp-btn erp-btn-primary">
                    Add Production Plan
                </a>
            @endif
        </div>
    </x-slot>

    @php
        $sortField = request('sort', 'plan_date');
        $sortDirection = request('direction', 'desc');

        $sortUrl = function ($field) use ($sortField, $sortDirection) {
            $nextDirection = ($sortField === $field && $sortDirection === 'asc') ? 'desc' : 'asc';

            return request()->fullUrlWithQuery([
                'sort' => $field,
                'direction' => $nextDirection,
                'page' => 1,
            ]);
        };

        $sortIcon = function ($field) use ($sortField, $sortDirection) {
            if ($sortField !== $field) {
                return '↕';
            }

            return $sortDirection === 'asc' ? '↑' : '↓';
        };
    @endphp

    <div class="erp-page-wrap">
        @if(session('success'))
            <div class="fortune-success">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="fortune-error">
                <ul style="list-style:disc;margin-left:20px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="erp-card">
            <form method="GET" action="{{ route('production-plans.index') }}">
                <div class="plan-filter-grid">
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
                        <select name="zone_id" id="zone_filter">
                            <option value="">All Zones</option>
                            @foreach($zones as $zone)
                                <option value="{{ $zone->id }}" {{ ($filters['zone_id'] ?? '') == $zone->id ? 'selected' : '' }}>
                                    {{ $zone->code }} - {{ $zone->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label>Line</label>
                        <select name="production_line_id" id="line_filter">
                            <option value="">All Lines</option>
                            @foreach($productionLines as $line)
                                <option value="{{ $line->id }}"
                                        data-zone-id="{{ $line->zone_id }}"
                                    {{ ($filters['production_line_id'] ?? '') == $line->id ? 'selected' : '' }}>
                                    {{ $line->code }} - {{ $line->name }}
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
                                    {{ $product->code }} - {{ $product->name }}
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
                                    {{ $shift->code }} - {{ $shift->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label>Status</label>
                        <select name="status">
                            <option value="">All</option>
                            @foreach($statuses ?? [] as $value => $label)
                                <option value="{{ $value }}" {{ ($filters['status'] ?? '') === $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <input type="hidden" name="sort" value="{{ $sortField }}">
                <input type="hidden" name="direction" value="{{ $sortDirection }}">

                <div class="plan-filter-actions">
                    <button type="submit" class="erp-btn erp-btn-primary">
                        Apply Filters
                    </button>

                    <a href="{{ route('production-plans.index') }}" class="erp-btn erp-btn-secondary">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <div class="erp-card">
            <div class="erp-result-title">
                Results: {{ $plans->total() }}
            </div>

            <div class="erp-responsive-table">
                <table class="erp-table">
                    <thead>
                        <tr>
                            <th><a class="sort-link" href="{{ $sortUrl('plan_code') }}">Plan Code {{ $sortIcon('plan_code') }}</a></th>
                            <th><a class="sort-link" href="{{ $sortUrl('plan_date') }}">Date {{ $sortIcon('plan_date') }}</a></th>
                            <th><a class="sort-link" href="{{ $sortUrl('hour') }}">Hour {{ $sortIcon('hour') }}</a></th>
                            <th><a class="sort-link" href="{{ $sortUrl('shift') }}">Shift {{ $sortIcon('shift') }}</a></th>
                            <th><a class="sort-link" href="{{ $sortUrl('zone') }}">Zone {{ $sortIcon('zone') }}</a></th>
                            <th><a class="sort-link" href="{{ $sortUrl('line') }}">Line {{ $sortIcon('line') }}</a></th>
                            <th><a class="sort-link" href="{{ $sortUrl('product') }}">Product {{ $sortIcon('product') }}</a></th>
                            <th><a class="sort-link" href="{{ $sortUrl('planned_qty') }}">Planned Qty {{ $sortIcon('planned_qty') }}</a></th>
                            <th><a class="sort-link" href="{{ $sortUrl('target_oee') }}">Target OEE {{ $sortIcon('target_oee') }}</a></th>
                            <th><a class="sort-link" href="{{ $sortUrl('status') }}">Status {{ $sortIcon('status') }}</a></th>
                            <th>Entry</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($plans as $plan)
                            @php
                                $hasEntry = $plan->entries && $plan->entries->count() > 0;
                                $firstEntry = $hasEntry ? $plan->entries->first() : null;
                            @endphp

                            <tr>
                                <td><strong>{{ $plan->plan_code ?? '-' }}</strong></td>
                                <td>{{ $plan->plan_date?->format('Y-m-d') }}</td>

                                <td>
                                    {{ $plan->hour_start ? substr($plan->hour_start, 0, 5) : '-' }}
                                    -
                                    {{ $plan->hour_end ? substr($plan->hour_end, 0, 5) : '-' }}
                                </td>

                                <td>{{ $plan->shift?->code ?? '-' }}</td>
                                <td>{{ $plan->zone?->code ?? '-' }}</td>
                                <td>{{ $plan->productionLine?->code ?? '-' }}</td>

                                <td>
                                    <strong>{{ $plan->product?->code ?? '-' }}</strong>
                                    <div class="erp-muted-small">{{ $plan->product?->name }}</div>
                                </td>

                                <td>{{ number_format((float) $plan->planned_qty, 2) }}</td>

                                <td>
                                    @if($plan->target_oee !== null)
                                        {{ number_format((float) $plan->target_oee, 2) }}%
                                    @else
                                        -
                                    @endif
                                </td>

                                <td>
                                    @if($plan->status === 'planned')
                                        <span class="erp-pill erp-pill-info">Planned</span>
                                    @elseif($plan->status === 'in_progress')
                                        <span class="erp-pill erp-pill-warning">In Progress</span>
                                    @elseif($plan->status === 'completed')
                                        <span class="erp-pill erp-pill-success">Completed</span>
                                    @elseif($plan->status === 'cancelled')
                                        <span class="erp-pill erp-pill-danger">Cancelled</span>
                                    @else
                                        <span class="erp-pill">{{ ucwords(str_replace('_', ' ', $plan->status)) }}</span>
                                    @endif
                                </td>

                                <td>
                                    @if($hasEntry)
                                        <span class="erp-pill erp-pill-success">Entry Created</span>
                                    @else
                                        <span class="erp-pill erp-pill-neutral">No Entry</span>
                                    @endif
                                </td>

                                <td class="text-right">
                                    @if($hasEntry && $firstEntry)
                                        <a href="{{ route('production-entries.edit', $firstEntry) }}" class="erp-link">
                                            Open Entry
                                        </a>
                                    @else
                                        @if(auth()->user()?->canCreateProductionEntries() && $plan->status !== 'cancelled')
                                            <form method="POST"
                                                  action="{{ route('production-entries.create-from-plan', $plan) }}"
                                                  style="display:inline;">
                                                @csrf
                                                <button type="submit" class="erp-action-button">
                                                    Create Entry
                                                </button>
                                            </form>
                                        @endif

                                        @if(auth()->user()?->canManageProductionPlans())
                                            <a href="{{ route('production-plans.edit', $plan) }}" class="erp-link">
                                                Edit
                                            </a>

                                            <form method="POST"
                                                  action="{{ route('production-plans.destroy', $plan) }}"
                                                  style="display:inline;"
                                                  onsubmit="return confirm('Delete this production plan?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="erp-delete-link">
                                                    Delete
                                                </button>
                                            </form>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="erp-empty">
                                    No production plans found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="erp-pagination">
                {{ $plans->links() }}
            </div>
        </div>
    </div>

    @include('components.erp-page-style')

    <style>
        .plan-filter-grid {
            display: grid;
            grid-template-columns: repeat(7, minmax(0, 1fr));
            gap: 12px;
        }

        .plan-filter-grid label {
            display: block;
            margin-bottom: 5px;
            font-size: 12px;
            font-weight: 800;
            color: #334155;
        }

        .plan-filter-grid input,
        .plan-filter-grid select {
            width: 100%;
            height: 36px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 6px 9px;
            font-size: 13px;
            color: #0f172a;
            background: #ffffff;
        }

        .plan-filter-actions {
            display: flex;
            gap: 8px;
            margin-top: 12px;
        }

        .sort-link {
            color: #0f172a;
            text-decoration: none;
            font-weight: 900;
            white-space: nowrap;
        }

        .sort-link:hover {
            color: #2563eb;
            text-decoration: underline;
        }

        .erp-action-button {
            margin-left: 8px;
            border: 0;
            background: transparent;
            color: #2563eb;
            font-size: 13px;
            font-weight: 900;
            cursor: pointer;
        }

        .erp-action-button:hover {
            text-decoration: underline;
        }

        .erp-pill-info {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .erp-pill-neutral {
            background: #f1f5f9;
            color: #475569;
        }

        .erp-pill-danger {
            background: #fee2e2;
            color: #991b1b;
        }

        @media (max-width: 1400px) {
            .plan-filter-grid {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }
        }

        @media (max-width: 900px) {
            .plan-filter-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 640px) {
            .plan-filter-grid {
                grid-template-columns: 1fr;
            }

            .plan-filter-actions {
                flex-direction: column;
            }
        }
    </style>

    <script>
        const zoneFilter = document.getElementById('zone_filter');
        const lineFilter = document.getElementById('line_filter');

        function filterLinesByZone() {
            if (!zoneFilter || !lineFilter) {
                return;
            }

            const zoneId = zoneFilter.value;

            Array.from(lineFilter.options).forEach(option => {
                if (!option.value) {
                    option.hidden = false;
                    return;
                }

                const optionZoneId = option.getAttribute('data-zone-id');
                option.hidden = zoneId && optionZoneId !== zoneId;
            });

            const selectedOption = lineFilter.options[lineFilter.selectedIndex];

            if (selectedOption && selectedOption.hidden) {
                lineFilter.value = '';
            }
        }

        if (zoneFilter) {
            zoneFilter.addEventListener('change', filterLinesByZone);
            filterLinesByZone();
        }
    </script>
</x-app-layout>