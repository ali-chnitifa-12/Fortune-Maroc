<x-app-layout>
    <x-slot name="header">
        <div class="erp-page-head">
            <div>
                <h2 class="erp-page-title">Line KPI Board</h2>
                <div class="erp-page-subtitle">
                    Production line performance by date and zone.
                </div>
            </div>

            <a href="{{ route('machine-status.index') }}" class="erp-btn erp-btn-secondary">
                Machine Downtime Board
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
            <form method="GET" action="{{ route('line-status.index') }}">
                <div class="line-filter-grid">
                    <div>
                        <label>Date</label>
                        <input type="date" name="date" value="{{ $selectedDate }}">
                    </div>

                    <div>
                        <label>Zone</label>
                        <select name="zone_id">
                            <option value="">All zones</option>
                            @foreach($zones as $zone)
                                <option value="{{ $zone->id }}" {{ (string) $selectedZoneId === (string) $zone->id ? 'selected' : '' }}>
                                    {{ $zone->code }} - {{ $zone->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="line-filter-actions">
                    <button type="submit" class="erp-btn erp-btn-primary">
                        Apply Filters
                    </button>

                    <a href="{{ route('line-status.index') }}" class="erp-btn erp-btn-secondary">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <div class="line-kpi-grid">
            <div class="line-kpi-card">
                <div class="line-kpi-label">Lines</div>
                <div class="line-kpi-value">{{ $stats['lines'] }}</div>
            </div>

            <div class="line-kpi-card">
                <div class="line-kpi-label">Planned</div>
                <div class="line-kpi-value">{{ number_format($stats['plannedQty'], 2) }}</div>
            </div>

            <div class="line-kpi-card">
                <div class="line-kpi-label">Actual</div>
                <div class="line-kpi-value">{{ number_format($stats['actualQty'], 2) }}</div>
            </div>

            <div class="line-kpi-card">
                <div class="line-kpi-label">Good</div>
                <div class="line-kpi-value text-success">{{ number_format($stats['goodQty'], 2) }}</div>
            </div>

            <div class="line-kpi-card">
                <div class="line-kpi-label">Rejected</div>
                <div class="line-kpi-value text-danger">{{ number_format($stats['rejectedQty'], 2) }}</div>
            </div>

            <div class="line-kpi-card">
                <div class="line-kpi-label">Average OEE</div>
                <div class="line-kpi-value">{{ number_format($stats['oee'], 2) }}%</div>
            </div>

            <div class="line-kpi-card">
                <div class="line-kpi-label">Stops</div>
                <div class="line-kpi-value">{{ $stats['stopsCount'] }}</div>
            </div>

            <div class="line-kpi-card">
                <div class="line-kpi-label">Downtime</div>
                <div class="line-kpi-value">{{ $stats['stopMin'] }} min</div>
            </div>
        </div>

        <div class="line-card-grid">
            @forelse($lineCards as $card)
                @php
                    $line = $card['line'];
                @endphp

                <div class="line-card">
                    <div class="line-card-head">
                        <div>
                            <div class="line-code">{{ $line->code }}</div>
                            <div class="line-name">{{ $line->name }}</div>
                        </div>

                        <span class="erp-pill erp-pill-success">Active</span>
                    </div>

                    <div class="line-meta-grid">
                        <div>
                            <span>Zone</span>
                            <strong>{{ $card['zone']?->code ?? '-' }}</strong>
                        </div>

                        <div>
                            <span>Machines</span>
                            <strong>{{ $line->machines->where('is_active', true)->count() }}</strong>
                        </div>

                        <div>
                            <span>Products</span>
                            <strong>{{ $line->products->count() }}</strong>
                        </div>

                        <div>
                            <span>Entries</span>
                            <strong>{{ $card['entries']->count() }}</strong>
                        </div>
                    </div>

                    <div class="line-production-grid">
                        <div>
                            <span>Planned</span>
                            <strong>{{ number_format($card['plannedQty'], 2) }}</strong>
                        </div>

                        <div>
                            <span>Actual</span>
                            <strong>{{ number_format($card['actualQty'], 2) }}</strong>
                        </div>

                        <div>
                            <span>Good</span>
                            <strong>{{ number_format($card['goodQty'], 2) }}</strong>
                        </div>

                        <div>
                            <span>Rejected</span>
                            <strong>{{ number_format($card['rejectedQty'], 2) }}</strong>
                        </div>

                        <div>
                            <span>Stops</span>
                            <strong>{{ $card['stopsCount'] }}</strong>
                        </div>

                        <div>
                            <span>Downtime</span>
                            <strong>{{ $card['stopMin'] }} min</strong>
                        </div>
                    </div>

                    <div class="line-oee-grid">
                        <div>
                            <span>Availability</span>
                            <strong>{{ number_format($card['availability'], 2) }}%</strong>
                        </div>

                        <div>
                            <span>Performance</span>
                            <strong>{{ number_format($card['performance'], 2) }}%</strong>
                        </div>

                        <div>
                            <span>Quality</span>
                            <strong>{{ number_format($card['quality'], 2) }}%</strong>
                        </div>

                        <div>
                            <span>OEE</span>
                            <strong>{{ number_format($card['oee'], 2) }}%</strong>
                        </div>
                    </div>

                    @if($card['entries']->count() > 0)
                        <div class="line-entry-list">
                            <div class="line-section-title">Entries</div>

                            @foreach($card['entries'] as $entry)
                                <div class="line-entry-row">
                                    <div>
                                        <strong>{{ substr($entry->hour_start, 0, 5) }} - {{ substr($entry->hour_end, 0, 5) }}</strong>
                                        <span>{{ $entry->shift?->code ?? '-' }}</span>
                                    </div>

                                    <div>
                                        {{ $entry->product?->code ?? '-' }}
                                    </div>

                                    <div>
                                        OEE {{ number_format((float) $entry->oee, 2) }}%
                                    </div>

                                    <a href="{{ route('production-entries.edit', $entry) }}">
                                        Open
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="line-empty">
                            No production entry found for this line on {{ $selectedDate }}.
                        </div>
                    @endif
                </div>
            @empty
                <div class="erp-card">
                    No production lines found for selected filters.
                </div>
            @endforelse
        </div>
    </div>

    @include('components.erp-page-style')

    <style>
        .line-filter-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .line-filter-grid label {
            display: block;
            margin-bottom: 5px;
            font-size: 12px;
            font-weight: 800;
            color: #334155;
        }

        .line-filter-grid input,
        .line-filter-grid select {
            width: 100%;
            height: 36px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 6px 9px;
            font-size: 13px;
            color: #0f172a;
            background: #ffffff;
        }

        .line-filter-actions {
            display: flex;
            gap: 8px;
            margin-top: 12px;
        }

        .line-kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
            margin: 16px 0;
        }

        .line-kpi-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 14px;
            box-shadow: 0 8px 22px rgba(15, 23, 42, 0.04);
        }

        .line-kpi-label {
            font-size: 12px;
            font-weight: 800;
            color: #64748b;
        }

        .line-kpi-value {
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

        .line-card-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }

        .line-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            padding: 16px;
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.05);
            border-top: 4px solid #2563eb;
        }

        .line-card-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 12px;
        }

        .line-code {
            font-size: 17px;
            font-weight: 900;
            color: #0f172a;
        }

        .line-name {
            margin-top: 3px;
            font-size: 13px;
            font-weight: 700;
            color: #334155;
        }

        .line-meta-grid,
        .line-production-grid,
        .line-oee-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 8px;
            margin-top: 10px;
        }

        .line-meta-grid div,
        .line-production-grid div,
        .line-oee-grid div {
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 9px;
        }

        .line-meta-grid span,
        .line-production-grid span,
        .line-oee-grid span {
            display: block;
            font-size: 11px;
            color: #64748b;
            font-weight: 800;
        }

        .line-meta-grid strong,
        .line-production-grid strong,
        .line-oee-grid strong {
            display: block;
            margin-top: 4px;
            font-size: 13px;
            color: #0f172a;
            font-weight: 900;
        }

        .line-entry-list {
            margin-top: 12px;
            border-top: 1px solid #e5e7eb;
            padding-top: 12px;
        }

        .line-section-title {
            font-size: 12px;
            font-weight: 900;
            color: #334155;
            margin-bottom: 8px;
        }

        .line-entry-row {
            display: grid;
            grid-template-columns: 120px 1fr 110px 60px;
            gap: 8px;
            align-items: center;
            padding: 7px 0;
            border-bottom: 1px solid #eef2f7;
            font-size: 12px;
        }

        .line-entry-row strong {
            color: #0f172a;
            font-weight: 900;
        }

        .line-entry-row span {
            color: #64748b;
            margin-left: 4px;
        }

        .line-empty {
            margin-top: 12px;
            border: 1px dashed #cbd5e1;
            border-radius: 10px;
            padding: 12px;
            background: #f8fafc;
            color: #64748b;
            font-size: 13px;
            font-weight: 800;
        }

        @media (max-width: 1200px) {
            .line-kpi-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .line-card-grid {
                grid-template-columns: 1fr;
            }

            .line-meta-grid,
            .line-production-grid,
            .line-oee-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 768px) {
            .line-filter-grid,
            .line-kpi-grid,
            .line-meta-grid,
            .line-production-grid,
            .line-oee-grid {
                grid-template-columns: 1fr;
            }

            .line-filter-actions {
                flex-direction: column;
            }

            .line-entry-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</x-app-layout>