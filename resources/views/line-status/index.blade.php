<x-app-layout>
    <x-slot name="header">
        <div class="erp-page-head">
            <div>
                <h2 class="erp-page-title">Line KPI Board</h2>
                <div class="erp-page-subtitle">
                    Shift-level line KPI calculated from generated hourly entries and shift downtime.
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
                <div class="kpi-label">Plans</div>
                <div class="kpi-value">{{ $summary['plans'] }}</div>
            </div>

            <div class="kpi-card">
                <div class="kpi-label">Entries</div>
                <div class="kpi-value">{{ $summary['entries'] }}</div>
            </div>

            <div class="kpi-card">
                <div class="kpi-label">Planned Qty</div>
                <div class="kpi-value">{{ number_format($summary['planned_qty'], 2) }}</div>
            </div>

            <div class="kpi-card">
                <div class="kpi-label">Actual Qty</div>
                <div class="kpi-value">{{ number_format($summary['actual_qty'], 2) }}</div>
            </div>

            <div class="kpi-card">
                <div class="kpi-label">Good Qty</div>
                <div class="kpi-value">{{ number_format($summary['good_qty'], 2) }}</div>
            </div>

            <div class="kpi-card">
                <div class="kpi-label">Rejected Qty</div>
                <div class="kpi-value danger">{{ number_format($summary['rejected_qty'], 2) }}</div>
            </div>

            <div class="kpi-card">
                <div class="kpi-label">Stops</div>
                <div class="kpi-value">{{ $summary['stops_count'] }}</div>
            </div>

            <div class="kpi-card">
                <div class="kpi-label">Avg OEE</div>
                <div class="kpi-value">{{ number_format($summary['average_oee'], 2) }}%</div>
            </div>
        </div>

        <div class="erp-card">
            <form method="GET" action="{{ route('line-status.index') }}">
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
                    <a href="{{ route('line-status.index') }}" class="erp-btn erp-btn-secondary">Reset</a>
                </div>
            </form>
        </div>

        <div class="line-grid">
            @forelse($rows as $row)
                @php
                    $plan = $row['plan'];
                @endphp

                <div class="line-card">
                    <div class="line-card-head">
                        <div>
                            <h3>{{ $plan->productionLine?->code ?? '-' }}</h3>
                            <div class="line-subtitle">
                                {{ $plan->productionLine?->name ?? '-' }}
                            </div>
                        </div>

                        <div>
                            @if($plan->status === 'completed')
                                <span class="erp-pill erp-pill-success">Completed</span>
                            @elseif($plan->status === 'in_progress')
                                <span class="erp-pill erp-pill-warning">In Progress</span>
                            @elseif($plan->status === 'cancelled')
                                <span class="erp-pill erp-pill-danger">Cancelled</span>
                            @else
                                <span class="erp-pill erp-pill-info">Planned</span>
                            @endif
                        </div>
                    </div>

                    <div class="line-context">
                        <div>
                            <span>Plan</span>
                            <strong>{{ $plan->plan_code }}</strong>
                        </div>

                        <div>
                            <span>Date</span>
                            <strong>{{ $plan->plan_date?->format('Y-m-d') }}</strong>
                        </div>

                        <div>
                            <span>Shift</span>
                            <strong>{{ $plan->shift?->code ?? '-' }}</strong>
                        </div>

                        <div>
                            <span>Zone</span>
                            <strong>{{ $plan->zone?->code ?? '-' }}</strong>
                        </div>

                        <div>
                            <span>Product</span>
                            <strong>{{ $plan->product?->code ?? '-' }}</strong>
                        </div>

                        <div>
                            <span>Entries</span>
                            <strong>{{ $row['entries_count'] }}</strong>
                        </div>
                    </div>

                    <div class="line-kpi-grid">
                        <div>
                            <span>Planned</span>
                            <strong>{{ number_format($row['planned_qty'], 2) }}</strong>
                        </div>

                        <div>
                            <span>Actual</span>
                            <strong>{{ number_format($row['actual_qty'], 2) }}</strong>
                        </div>

                        <div>
                            <span>Good</span>
                            <strong>{{ number_format($row['good_qty'], 2) }}</strong>
                        </div>

                        <div>
                            <span>Rejected</span>
                            <strong>{{ number_format($row['rejected_qty'], 2) }}</strong>
                        </div>

                        <div>
                            <span>Chute 1</span>
                            <strong>{{ number_format($row['chute_1_qty'], 2) }}</strong>
                        </div>

                        <div>
                            <span>Chute 2</span>
                            <strong>{{ number_format($row['chute_2_qty'], 2) }}</strong>
                        </div>

                        <div>
                            <span>Chute 3</span>
                            <strong>{{ number_format($row['chute_3_qty'], 2) }}</strong>
                        </div>

                        <div>
                            <span>Stops</span>
                            <strong>{{ $row['stops_count'] }}</strong>
                        </div>

                        <div>
                            <span>Downtime</span>
                            <strong>{{ $row['downtime_min'] }} min</strong>
                        </div>

                        <div>
                            <span>Availability</span>
                            <strong>{{ number_format($row['availability'], 2) }}%</strong>
                        </div>

                        <div>
                            <span>Performance</span>
                            <strong>{{ number_format($row['performance'], 2) }}%</strong>
                        </div>

                        <div>
                            <span>Quality</span>
                            <strong>{{ number_format($row['quality'], 2) }}%</strong>
                        </div>

                        <div class="oee-box">
                            <span>OEE</span>
                            <strong>{{ number_format($row['oee'], 2) }}%</strong>
                        </div>
                    </div>

                    <div class="entry-status-line">
                        <span>Draft: {{ $row['draft_count'] }}</span>
                        <span>Finished: {{ $row['finished_count'] }}</span>
                        <span>Sent: {{ $row['sent_count'] }}</span>
                    </div>

                    @if($row['open_entry'])
                        <div class="line-actions">
                            <a href="{{ route('production-entries.edit', $row['open_entry']) }}" class="erp-btn erp-btn-primary">
                                Open Entries
                            </a>
                        </div>
                    @endif
                </div>
            @empty
                <div class="erp-card">
                    <div class="erp-empty">
                        No line KPI data found for selected filters.
                    </div>
                </div>
            @endforelse
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
            font-size: 24px;
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

        .line-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
        }

        .line-card {
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            background: #ffffff;
            padding: 18px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.04);
        }

        .line-card-head {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            align-items: flex-start;
            margin-bottom: 14px;
        }

        .line-card-head h3 {
            margin: 0;
            font-size: 20px;
            font-weight: 900;
            color: #0f172a;
        }

        .line-subtitle {
            margin-top: 4px;
            font-size: 13px;
            font-weight: 800;
            color: #475569;
        }

        .line-context,
        .line-kpi-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
        }

        .line-context {
            margin-bottom: 12px;
        }

        .line-context div,
        .line-kpi-grid div {
            padding: 12px;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            background: #f8fafc;
        }

        .line-context span,
        .line-kpi-grid span {
            display: block;
            font-size: 11px;
            font-weight: 900;
            color: #64748b;
        }

        .line-context strong,
        .line-kpi-grid strong {
            display: block;
            margin-top: 5px;
            font-size: 14px;
            font-weight: 900;
            color: #0f172a;
        }

        .oee-box {
            border-color: #bfdbfe !important;
            background: #eff6ff !important;
        }

        .entry-status-line {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 14px;
            font-size: 12px;
            font-weight: 900;
            color: #475569;
        }

        .entry-status-line span {
            padding: 6px 10px;
            border-radius: 999px;
            background: #f1f5f9;
        }

        .line-actions {
            margin-top: 14px;
        }

        .erp-pill-info {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .erp-pill-danger {
            background: #fee2e2;
            color: #991b1b;
        }

        @media (max-width: 1400px) {
            .line-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 1200px) {
            .kpi-grid,
            .filter-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 720px) {
            .kpi-grid,
            .filter-grid,
            .line-context,
            .line-kpi-grid {
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