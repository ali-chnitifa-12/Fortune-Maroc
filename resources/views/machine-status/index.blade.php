<x-app-layout>
    <x-slot name="header">
        <div class="erp-page-head">
            <div>
                <h2 class="erp-page-title">Machine Status</h2>
                <div class="erp-page-subtitle">
                    Shift-level machine downtime monitoring by zone, line, shift and date.
                </div>
            </div>
        </div>
    </x-slot>

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

        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-label">Machines With Stops</div>
                <div class="kpi-value">{{ $summary['machines_with_stops'] }}</div>
            </div>

            <div class="kpi-card">
                <div class="kpi-label">Total Stops</div>
                <div class="kpi-value">{{ $summary['total_stops'] }}</div>
            </div>

            <div class="kpi-card">
                <div class="kpi-label">Total Downtime</div>
                <div class="kpi-value">{{ $summary['total_downtime_min'] }} min</div>
            </div>

            <div class="kpi-card">
                <div class="kpi-label">Open Stops</div>
                <div class="kpi-value danger">{{ $summary['open_stops'] }}</div>
            </div>
        </div>

        <div class="erp-card">
            <form method="GET" action="{{ route('machine-status.index') }}">
                <div class="filter-grid">
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
                </div>

                <div class="filter-actions">
                    <button type="submit" class="erp-btn erp-btn-primary">Apply Filters</button>
                    <a href="{{ route('machine-status.index') }}" class="erp-btn erp-btn-secondary">Reset</a>
                </div>
            </form>
        </div>

        @if($openStops->count() > 0)
            <div class="erp-card">
                <div class="erp-result-title">Open Machine Stops</div>

                <div class="erp-responsive-table">
                    <table class="erp-table">
                        <thead>
                            <tr>
                                <th>Machine</th>
                                <th>Zone</th>
                                <th>Line</th>
                                <th>Shift</th>
                                <th>Started</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($openStops as $stop)
                                <tr>
                                    <td>
                                        <strong>{{ $stop->machine?->code }}</strong>
                                        <div class="erp-muted-small">{{ $stop->machine?->name }}</div>
                                    </td>

                                    <td>{{ $stop->productionPlan?->zone?->code ?? '-' }}</td>
                                    <td>{{ $stop->productionPlan?->productionLine?->code ?? '-' }}</td>
                                    <td>{{ $stop->productionPlan?->shift?->code ?? '-' }}</td>
                                    <td>{{ $stop->started_at?->format('Y-m-d H:i:s') }}</td>
                                    <td><span class="erp-pill erp-pill-danger">In Repair</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <div class="erp-card">
            <div class="erp-result-title">Machine Downtime Results: {{ $rows->count() }}</div>

            <div class="erp-responsive-table">
                <table class="erp-table">
                    <thead>
                        <tr>
                            <th>Machine</th>
                            <th>Zone</th>
                            <th>Line</th>
                            <th>Stops Count</th>
                            <th>Total Downtime</th>
                            <th>Last Stop Started</th>
                            <th>Last Stop Ended</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($rows as $row)
                            <tr>
                                <td>
                                    <strong>{{ $row->machine_code }}</strong>
                                    <div class="erp-muted-small">{{ $row->machine_name }}</div>
                                </td>

                                <td>
                                    <strong>{{ $row->zone_code ?? '-' }}</strong>
                                    <div class="erp-muted-small">{{ $row->zone_name }}</div>
                                </td>

                                <td>
                                    <strong>{{ $row->line_code ?? '-' }}</strong>
                                    <div class="erp-muted-small">{{ $row->line_name }}</div>
                                </td>

                                <td>{{ (int) $row->stops_count }}</td>
                                <td>{{ (int) $row->total_downtime_min }} min</td>
                                <td>{{ $row->last_stop_started_at ?? '-' }}</td>
                                <td>{{ $row->last_stop_ended_at ?? '-' }}</td>

                                <td>
                                    @if((int) $row->has_open_stop === 1)
                                        <span class="erp-pill erp-pill-danger">In Repair</span>
                                    @else
                                        <span class="erp-pill erp-pill-success">Active</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="erp-empty">
                                    No machine downtime found for selected filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @include('components.erp-page-style')

    <style>
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 14px;
            margin-bottom: 16px;
        }

        .kpi-card {
            padding: 18px;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            background: #ffffff;
        }

        .kpi-label {
            font-size: 12px;
            font-weight: 900;
            color: #64748b;
        }

        .kpi-value {
            margin-top: 8px;
            font-size: 26px;
            font-weight: 900;
            color: #0f172a;
        }

        .kpi-value.danger {
            color: #dc2626;
        }

        .filter-grid {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 12px;
        }

        .filter-grid label {
            display: block;
            margin-bottom: 5px;
            font-size: 12px;
            font-weight: 800;
            color: #334155;
        }

        .filter-grid input,
        .filter-grid select {
            width: 100%;
            height: 36px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 6px 9px;
            font-size: 13px;
            color: #0f172a;
            background: #ffffff;
        }

        .filter-actions {
            display: flex;
            gap: 8px;
            margin-top: 12px;
        }

        .erp-pill-danger {
            background: #fee2e2;
            color: #991b1b;
        }

        @media (max-width: 1200px) {
            .kpi-grid,
            .filter-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 640px) {
            .kpi-grid,
            .filter-grid {
                grid-template-columns: 1fr;
            }

            .filter-actions {
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