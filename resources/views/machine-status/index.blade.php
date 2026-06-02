<x-app-layout>
    <x-slot name="header">
        <div class="erp-page-head">
            <div>
                <h2 class="erp-page-title">Machine Downtime Status</h2>
                <div class="erp-page-subtitle">
                    Machine-level downtime follow-up by date, zone and production line.
                </div>
            </div>

            <a href="{{ route('line-status.index') }}" class="erp-btn erp-btn-secondary">
                Line KPI Board
            </a>
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

        <div class="erp-card">
            <form method="GET" action="{{ route('machine-status.index') }}">
                <div class="machine-filter-grid">
                    <div>
                        <label>Date</label>
                        <input type="date" name="date" value="{{ $selectedDate }}">
                    </div>

                    <div>
                        <label>Zone</label>
                        <select name="zone_id" id="zone_filter">
                            <option value="">All zones</option>
                            @foreach($zones as $zone)
                                <option value="{{ $zone->id }}" {{ (string) $selectedZoneId === (string) $zone->id ? 'selected' : '' }}>
                                    {{ $zone->code }} - {{ $zone->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label>Production Line</label>
                        <select name="production_line_id" id="line_filter">
                            <option value="">All lines</option>
                            @foreach($productionLines as $line)
                                <option value="{{ $line->id }}"
                                        data-zone-id="{{ $line->zone_id }}"
                                    {{ (string) $selectedLineId === (string) $line->id ? 'selected' : '' }}>
                                    {{ $line->code }} - {{ $line->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="machine-filter-actions">
                    <button type="submit" class="erp-btn erp-btn-primary">
                        Apply Filters
                    </button>

                    <a href="{{ route('machine-status.index') }}" class="erp-btn erp-btn-secondary">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <div class="status-kpi-grid">
            <div class="status-kpi-card">
                <div class="status-kpi-label">Machines</div>
                <div class="status-kpi-value">{{ $stats['machines'] }}</div>
            </div>

            <div class="status-kpi-card">
                <div class="status-kpi-label">Active</div>
                <div class="status-kpi-value text-success">{{ $stats['active'] }}</div>
            </div>

            <div class="status-kpi-card">
                <div class="status-kpi-label">In Repair</div>
                <div class="status-kpi-value text-danger">{{ $stats['in_repair'] }}</div>
            </div>

            <div class="status-kpi-card">
                <div class="status-kpi-label">Stops</div>
                <div class="status-kpi-value">{{ $stats['total_stops'] }}</div>
            </div>

            <div class="status-kpi-card">
                <div class="status-kpi-label">Downtime</div>
                <div class="status-kpi-value">{{ $stats['total_stop_min'] }} min</div>
            </div>
        </div>

        <div class="machine-card-grid">
            @forelse($machineCards as $card)
                @php
                    $machine = $card['machine'];
                    $status = $card['status'];
                    $openDowntime = $card['openDowntime'];
                @endphp

                <div class="machine-card {{ $status === 'in_repair' ? 'machine-card-danger' : 'machine-card-success' }}">
                    <div class="machine-card-head">
                        <div>
                            <div class="machine-code">{{ $machine->code }}</div>
                            <div class="machine-name">{{ $machine->name }}</div>
                        </div>

                        @if($status === 'in_repair')
                            <span class="erp-pill erp-pill-danger">In Repair</span>
                        @else
                            <span class="erp-pill erp-pill-success">Active</span>
                        @endif
                    </div>

                    <div class="machine-meta-grid">
                        <div>
                            <span>Zone</span>
                            <strong>{{ $card['zone']?->code ?? '-' }}</strong>
                        </div>

                        <div>
                            <span>Line</span>
                            <strong>{{ $card['line']?->code ?? '-' }}</strong>
                        </div>

                        <div>
                            <span>Stops Today</span>
                            <strong>{{ $card['stopsCount'] }}</strong>
                        </div>

                        <div>
                            <span>Downtime</span>
                            <strong>{{ $card['totalStopMin'] }} min</strong>
                        </div>
                    </div>

                    @if($openDowntime)
                        <div class="open-stop-box">
                            <div class="open-stop-title">Current Open Stop</div>
                            <div class="open-stop-grid">
                                <div>
                                    <span>Started</span>
                                    <strong>{{ $openDowntime->started_at?->format('H:i:s') }}</strong>
                                </div>

                                <div>
                                    <span>Entry</span>
                                    @if($openDowntime->productionEntry)
                                        <a href="{{ route('production-entries.edit', ['production_entry' => $openDowntime->productionEntry->id, 'tab' => 'downtime']) }}">
                                            Open Entry
                                        </a>
                                    @else
                                        <strong>-</strong>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="machine-downtime-summary">
                        <div>
                            <div class="machine-section-title">Downtime Categories</div>

                            @forelse($card['categories']->take(3) as $category)
                                <div class="summary-line">
                                    <span>{{ $category['name'] }}</span>
                                    <strong>{{ $category['duration'] }} min / {{ $category['count'] }} stop(s)</strong>
                                </div>
                            @empty
                                <div class="empty-summary">No category.</div>
                            @endforelse
                        </div>

                        <div>
                            <div class="machine-section-title">Downtime Reasons</div>

                            @forelse($card['reasons']->take(3) as $reason)
                                <div class="summary-line">
                                    <span>{{ $reason['name'] }}</span>
                                    <strong>{{ $reason['duration'] }} min / {{ $reason['count'] }} stop(s)</strong>
                                </div>
                            @empty
                                <div class="empty-summary">No reason.</div>
                            @endforelse
                        </div>
                    </div>

                    @if($card['downtimes']->count() > 0)
                        <div class="machine-last-stops">
                            <div class="machine-section-title">Last Stops</div>

                            @foreach($card['downtimes']->take(3) as $downtime)
                                <div class="last-stop-line">
                                    <div>
                                        <strong>{{ $downtime->started_at?->format('H:i') }}</strong>
                                        <span>
                                            {{ $downtime->ended_at ? $downtime->ended_at->format('H:i') : 'Open' }}
                                        </span>
                                    </div>

                                    <div>
                                        {{ $downtime->downtimeCategory?->name ?? '-' }}
                                        /
                                        {{ $downtime->downtimeReason?->name ?? '-' }}
                                    </div>

                                    <strong>{{ $downtime->duration_min }} min</strong>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @empty
                <div class="erp-card">
                    No active machines found for selected filters.
                </div>
            @endforelse
        </div>
    </div>

    @include('components.erp-page-style')

    <style>
        .machine-filter-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
        }

        .machine-filter-grid label {
            display: block;
            margin-bottom: 5px;
            font-size: 12px;
            font-weight: 800;
            color: #334155;
        }

        .machine-filter-grid input,
        .machine-filter-grid select {
            width: 100%;
            height: 36px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 6px 9px;
            font-size: 13px;
            color: #0f172a;
            background: #ffffff;
        }

        .machine-filter-actions {
            display: flex;
            gap: 8px;
            margin-top: 12px;
        }

        .status-kpi-grid {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 12px;
            margin: 16px 0;
        }

        .status-kpi-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 14px;
            box-shadow: 0 8px 22px rgba(15, 23, 42, 0.04);
        }

        .status-kpi-label {
            font-size: 12px;
            font-weight: 800;
            color: #64748b;
        }

        .status-kpi-value {
            margin-top: 8px;
            font-size: 20px;
            font-weight: 900;
            color: #0f172a;
        }

        .text-success {
            color: #16a34a;
        }

        .text-danger {
            color: #dc2626;
        }

        .machine-card-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }

        .machine-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            padding: 16px;
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.05);
            border-top: 4px solid #94a3b8;
        }

        .machine-card-success {
            border-top-color: #16a34a;
        }

        .machine-card-danger {
            border-top-color: #dc2626;
        }

        .machine-card-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 12px;
        }

        .machine-code {
            font-size: 17px;
            font-weight: 900;
            color: #0f172a;
        }

        .machine-name {
            margin-top: 3px;
            font-size: 13px;
            font-weight: 700;
            color: #334155;
        }

        .machine-meta-grid,
        .open-stop-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 8px;
        }

        .machine-meta-grid div,
        .open-stop-grid div {
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 9px;
        }

        .machine-meta-grid span,
        .open-stop-grid span {
            display: block;
            font-size: 11px;
            color: #64748b;
            font-weight: 800;
        }

        .machine-meta-grid strong,
        .open-stop-grid strong {
            display: block;
            margin-top: 4px;
            font-size: 13px;
            color: #0f172a;
            font-weight: 900;
        }

        .open-stop-box {
            margin-top: 12px;
            border: 1px solid #fecaca;
            background: #fef2f2;
            border-radius: 10px;
            padding: 10px;
        }

        .open-stop-title {
            font-size: 12px;
            font-weight: 900;
            color: #991b1b;
            margin-bottom: 8px;
        }

        .machine-downtime-summary {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
            margin-top: 12px;
        }

        .machine-section-title {
            font-size: 12px;
            font-weight: 900;
            color: #334155;
            margin-bottom: 8px;
        }

        .summary-line {
            display: flex;
            justify-content: space-between;
            gap: 8px;
            padding: 7px 0;
            border-bottom: 1px solid #eef2f7;
            font-size: 12px;
        }

        .summary-line span {
            color: #475569;
            font-weight: 700;
        }

        .summary-line strong {
            color: #0f172a;
            font-weight: 900;
            text-align: right;
        }

        .empty-summary {
            color: #94a3b8;
            font-size: 12px;
            font-weight: 700;
        }

        .machine-last-stops {
            margin-top: 12px;
            border-top: 1px solid #e5e7eb;
            padding-top: 12px;
        }

        .last-stop-line {
            display: grid;
            grid-template-columns: 90px 1fr 70px;
            gap: 8px;
            align-items: center;
            padding: 7px 0;
            border-bottom: 1px solid #eef2f7;
            font-size: 12px;
        }

        .last-stop-line span {
            color: #64748b;
            margin-left: 4px;
        }

        .last-stop-line strong {
            color: #0f172a;
            font-weight: 900;
        }

        @media (max-width: 1200px) {
            .status-kpi-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }

            .machine-card-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .machine-filter-grid,
            .status-kpi-grid,
            .machine-meta-grid,
            .open-stop-grid,
            .machine-downtime-summary {
                grid-template-columns: 1fr;
            }

            .machine-filter-actions {
                flex-direction: column;
            }

            .last-stop-line {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <script>
        const zoneFilter = document.getElementById('zone_filter');
        const lineFilter = document.getElementById('line_filter');

        function filterLinesByZone() {
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

        zoneFilter.addEventListener('change', filterLinesByZone);
        filterLinesByZone();
    </script>
</x-app-layout>